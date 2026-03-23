<?php

namespace App\Services\Ai;

use App\Models\File;
use App\Models\AiEmbedding;
use App\Services\UserStorageService;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class AiRagService
{
    protected ?AiProviderInterface $provider;
    protected int $chunkSize = 500;
    protected int $chunkOverlap = 50;
    protected int $maxResults = 5;
    protected ?UserStorageService $userStorageService;

    public function __construct(?AiProviderInterface $provider = null, ?UserStorageService $userStorageService = null)
    {
        $this->provider = $provider ?? AiServiceFactory::getProvider();
        $this->userStorageService = $userStorageService ?? (app()->bound(UserStorageService::class) ? app(UserStorageService::class) : null);
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
     * Retrieve relevant context for a query, indexing files on demand if needed.
     */
    public function retrieveContext(array $fileIds, string $query): string
    {
        $this->ensureIndexed($fileIds);

        $context = $this->getContext($query, $fileIds);

        if (!empty($context)) {
            return $context;
        }

        return $this->extractAttachedFileContext($fileIds);
    }

    /**
     * Extract plain text previews from attached files when vector retrieval is unavailable.
     */
    public function extractAttachedFileContext(array $fileIds, int $maxCharsPerFile = 3000): string
    {
        $files = File::whereIn('id', $fileIds)
            ->where('is_latest', true)
            ->get();

        if ($files->isEmpty()) {
            return '';
        }

        $context = "Attached document content:\n\n";

        foreach ($files as $file) {
            $content = trim($this->extractText($file));
            if ($content === '') {
                continue;
            }

            $snippet = mb_substr(preg_replace('/\s+/', ' ', $content), 0, $maxCharsPerFile);
            $context .= "[From: {$file->original_name}]\n{$snippet}\n\n";
        }

        return trim($context);
    }

    /**
     * Build Gemini inline parts for supported attachment types.
     */
    public function buildGeminiInlineParts(array $fileIds, int $maxBytesPerFile = 18_000_000): array
    {
        $parts = [];

        $files = File::whereIn('id', $fileIds)
            ->where('is_latest', true)
            ->get();

        foreach ($files as $file) {
            if (!$this->supportsGeminiInlineMime($file->mime_type ?? '')) {
                continue;
            }

            $path = $this->materializePath($file);
            if (!$path || !file_exists($path)) {
                continue;
            }

            $size = filesize($path);
            if ($size === false || $size <= 0 || $size > $maxBytesPerFile) {
                if ($this->isTemporaryPath($path)) {
                    @unlink($path);
                }
                continue;
            }

            $data = file_get_contents($path);

            if ($this->isTemporaryPath($path)) {
                @unlink($path);
            }

            if ($data === false || $data === '') {
                continue;
            }

            $parts[] = [
                'inline_data' => [
                    'mime_type' => $file->mime_type,
                    'data' => base64_encode($data),
                ],
            ];
        }

        return $parts;
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
        $path = $this->materializePath($file);

        if (!$path || !file_exists($path)) {
            return '';
        }

        $mimeType = $file->mime_type ?? '';

        $text = match (true) {
            str_starts_with($mimeType, 'text/') => file_get_contents($path),
            $mimeType === 'application/pdf' => $this->extractPdfText($path),
            in_array($mimeType, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']) => $this->extractDocText($path),
            $mimeType === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => $this->extractXlsxText($path),
            default => '',
        };

        if ($this->isTemporaryPath($path)) {
            @unlink($path);
        }

        return is_string($text) ? $text : '';
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
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'docx') {
            return $this->extractDocxText($path);
        }

        return '';
    }

    protected function extractDocxText(string $path): string
    {
        if (!class_exists(ZipArchive::class)) {
            return '';
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return '';
        }

        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml));

        return html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected function extractXlsxText(string $path): string
    {
        if (!class_exists(ZipArchive::class)) {
            return '';
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) {
            return '';
        }

        $sharedStrings = [];
        if ($sharedStringsXml) {
            $shared = @simplexml_load_string($sharedStringsXml);
            if ($shared !== false && isset($shared->si)) {
                foreach ($shared->si as $item) {
                    $parts = [];
                    if (isset($item->t)) {
                        $parts[] = (string) $item->t;
                    }
                    if (isset($item->r)) {
                        foreach ($item->r as $run) {
                            $parts[] = (string) $run->t;
                        }
                    }
                    $sharedStrings[] = implode('', $parts);
                }
            }
        }

        $sheet = @simplexml_load_string($sheetXml);
        if ($sheet === false || !isset($sheet->sheetData->row)) {
            return '';
        }

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $cell) {
                $type = (string) ($cell['t'] ?? '');
                $value = (string) ($cell->v ?? '');
                if ($type === 's' && is_numeric($value)) {
                    $value = $sharedStrings[(int) $value] ?? $value;
                }
                if ($value !== '') {
                    $cells[] = $value;
                }
            }
            if (!empty($cells)) {
                $rows[] = implode(' | ', $cells);
            }
        }

        return implode("\n", $rows);
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

    protected function ensureIndexed(array $fileIds): void
    {
        if (!$this->provider || !$this->provider->supports('embeddings')) {
            return;
        }

        File::whereIn('id', $fileIds)
            ->where('is_latest', true)
            ->get()
            ->each(function (File $file) {
                $hasEmbeddings = AiEmbedding::where('file_id', $file->id)->exists();

                if (!$hasEmbeddings) {
                    $this->indexFile($file);
                }
            });
    }

    protected function materializePath(File $file): ?string
    {
        if ($file->disk !== 'google_drive') {
            try {
                return Storage::disk($file->disk)->path($file->path);
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            $service = $file->storageOwner && $this->userStorageService
                ? $this->userStorageService->googleDriveServiceFor($file->storageOwner)
                : null;

            if ($service) {
                $response = $service->files->get($file->path, ['alt' => 'media']);
                $body = $response->getBody();
                $tempPath = $this->createTemporaryPath($file);
                if (!$tempPath) {
                    return null;
                }

                $target = fopen($tempPath, 'wb');
                if (!is_resource($target)) {
                    @unlink($tempPath);
                    return null;
                }

                while (!$body->eof()) {
                    fwrite($target, $body->read(1024 * 8));
                }

                fclose($target);

                return $tempPath;
            }

            $stream = Storage::disk($file->disk)->readStream($file->path);
        } catch (\Throwable) {
            return null;
        }

        if (!is_resource($stream)) {
            return null;
        }

        $tempPath = $this->createTemporaryPath($file);
        if (!$tempPath) {
            fclose($stream);
            return null;
        }

        $target = fopen($tempPath, 'wb');
        if (!is_resource($target)) {
            fclose($stream);
            @unlink($tempPath);
            return null;
        }

        stream_copy_to_stream($stream, $target);
        fclose($stream);
        fclose($target);

        return $tempPath;
    }

    protected function isTemporaryPath(string $path): bool
    {
        return str_starts_with($path, sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rf-ai-');
    }

    protected function supportsGeminiInlineMime(string $mimeType): bool
    {
        return in_array($mimeType, [
            'application/pdf',
            'image/png',
            'image/jpeg',
            'image/webp',
            'image/gif',
            'text/plain',
            'text/csv',
        ], true);
    }

    protected function createTemporaryPath(File $file): ?string
    {
        $extension = pathinfo($file->original_name ?? $file->name ?? 'file.tmp', PATHINFO_EXTENSION);
        $tempPath = tempnam(sys_get_temp_dir(), 'rf-ai-');

        if ($tempPath === false) {
            return null;
        }

        if (!$extension) {
            return $tempPath;
        }

        $targetPath = $tempPath . '.' . $extension;
        if (@rename($tempPath, $targetPath)) {
            return $targetPath;
        }

        @unlink($tempPath);

        return null;
    }
}
