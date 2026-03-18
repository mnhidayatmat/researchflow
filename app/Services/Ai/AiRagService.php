<?php

namespace App\Services\Ai;

use App\Models\File;
use App\Models\AiEmbedding;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class AiRagService
{
    protected ?AiProviderInterface $provider;
    protected int $chunkSize = 500;
    protected int $chunkOverlap = 50;
    protected int $maxResults = 5;

    public function __construct(?AiProviderInterface $provider = null)
    {
        $this->provider = $provider ?? AiServiceFactory::getProvider();
    }

    /**
     * Index a file for retrieval.
     */
    public function indexFile(File $file): void
    {
        if (!$this->provider || !$this->provider->supports('embeddings')) {
            return;
        }

        // Delete existing embeddings for this file
        AiEmbedding::where('file_id', $file->id)->delete();

        // Extract text content
        $content = $this->extractText($file);
        if (empty($content)) {
            return;
        }

        // Chunk the content
        $chunks = $this->chunkText($content);

        // Generate embeddings for each chunk
        $texts = array_column($chunks, 'text');
        $embeddings = $this->provider->embed($texts);

        // Store embeddings
        foreach ($chunks as $index => $chunk) {
            AiEmbedding::create([
                'file_id' => $file->id,
                'chunk_index' => $index,
                'content' => $chunk['text'],
                'metadata' => [
                    'start_char' => $chunk['start'],
                    'end_char' => $chunk['end'],
                    'token_estimate' => $chunk['tokens'],
                ],
                'vector' => $embeddings[$index],
            ]);
        }
    }

    /**
     * Search for relevant content across indexed files.
     */
    public function search(string $query, ?array $fileIds = null, int $topK = null): array
    {
        if (!$this->provider) {
            return [];
        }

        $topK = $topK ?? $this->maxResults;

        // Generate query embedding
        $queryVector = $this->provider->embed($query);

        // Build search query
        $searchQuery = AiEmbedding::query()
            ->when($fileIds, fn($q) => $q->whereIn('file_id', $fileIds))
            ->with('file');

        // For PostgreSQL with pgvector, we'd use cosine distance
        // For now, we'll fetch all and compute similarity in PHP
        // TODO: Implement proper vector similarity search with pgvector

        $results = $searchQuery->get();

        // Compute cosine similarity
        $scoredResults = [];
        foreach ($results as $embedding) {
            $similarity = $this->cosineSimilarity($queryVector, $embedding->vector);
            $scoredResults[] = [
                'embedding' => $embedding,
                'similarity' => $similarity,
            ];
        }

        // Sort by similarity and take top K
        usort($scoredResults, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
        $scoredResults = array_slice($scoredResults, 0, $topK);

        return array_map(fn($r) => [
            'file_id' => $r['embedding']->file_id,
            'file_name' => $r['embedding']->file->original_name ?? 'Unknown',
            'chunk_index' => $r['embedding']->chunk_index,
            'content' => $r['embedding']->content,
            'similarity' => $r['similarity'],
        ], $scoredResults);
    }

    /**
     * Get relevant context for a query.
     */
    public function getContext(string $query, ?array $fileIds = null): string
    {
        $results = $this->search($query, $fileIds);

        if (empty($results)) {
            return '';
        }

        $context = "Relevant information from documents:\n\n";
        foreach ($results as $result) {
            $context .= "[From: {$result['file_name']}]\n";
            $context .= $result['content'] . "\n\n";
        }

        return $context;
    }

    /**
     * Chat with RAG - includes relevant document context.
     */
    public function chatWithRag(array $messages, ?array $fileIds = null): string
    {
        if (!$this->provider) {
            throw new \RuntimeException('AI provider not configured.');
        }

        // Get the last user message for retrieval
        $lastUserMessage = null;
        foreach (array_reverse($messages) as $message) {
            if ($message['role'] === 'user') {
                $lastUserMessage = $message['content'];
                break;
            }
        }

        if (!$lastUserMessage) {
            return $this->provider->chat($messages);
        }

        // Retrieve relevant context
        $context = $this->getContext($lastUserMessage, $fileIds);

        // Inject context into the last user message
        if (!empty($context)) {
            foreach ($messages as &$message) {
                if ($message['role'] === 'user' && $message['content'] === $lastUserMessage) {
                    $message['content'] = "Context from documents:\n\n{$context}\n\nQuestion: {$message['content']}";
                    break;
                }
            }
        }

        return $this->provider->chat($messages);
    }

    /**
     * Extract text from a file.
     */
    protected function extractText(File $file): string
    {
        $path = Storage::disk($file->disk)->path($file->path);

        if (!file_exists($path)) {
            return '';
        }

        $mimeType = $file->mime_type;

        return match (true) {
            str_starts_with($mimeType, 'text/') => file_get_contents($path),
            $mimeType === 'application/pdf' => $this->extractPdfText($path),
            in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']) => $this->extractDocText($path),
            default => '',
        };
    }

    /**
     * Extract text from PDF.
     */
    protected function extractPdfText(string $path): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($path);
            return $pdf->getText();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Extract text from Word document (placeholder - requires phpword).
     */
    protected function extractDocText(string $path): string
    {
        // TODO: Implement with phpoffice/phpword
        return '';
    }

    /**
     * Split text into chunks.
     */
    protected function chunkText(string $text): array
    {
        $chunks = [];
        $length = strlen($text);
        $start = 0;

        while ($start < $length) {
            $end = $start + $this->chunkSize;

            // Try to break at word boundary
            if ($end < $length) {
                $lastSpace = strpos($text, ' ', $end - 50);
                if ($lastSpace !== false && $lastSpace < $end + 50) {
                    $end = $lastSpace;
                }
            }

            $chunkText = substr($text, $start, $end - $start);
            $chunks[] = [
                'text' => $chunkText,
                'start' => $start,
                'end' => $end,
                'tokens' => (int) (strlen($chunkText) / 4), // Rough estimate
            ];

            $start = $end - $this->chunkOverlap;
        }

        return $chunks;
    }

    /**
     * Compute cosine similarity between two vectors.
     */
    protected function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] ** 2;
            $magnitudeB += $b[$i] ** 2;
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Batch index multiple files.
     */
    public function batchIndex(array $fileIds): void
    {
        File::whereIn('id', $fileIds)
            ->where('is_latest', true)
            ->get()
            ->each(fn($file) => $this->indexFile($file));
    }
}
