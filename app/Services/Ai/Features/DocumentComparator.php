<?php

namespace App\Services\Ai\Features;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class DocumentComparator extends AiFeature
{
    /**
     * Compare two documents.
     */
    public function execute(File $file1, File $file2): array
    {
        $content1 = $this->extractContent($file1);
        $content2 = $this->extractContent($file2);

        if (empty($content1) || empty($content2)) {
            throw new \RuntimeException('Could not extract content from one or both files.');
        }

        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ['role' => 'user', 'content' => $this->buildUserMessage($file1, $file2, $content1, $content2)],
        ];

        $comparison = $this->call($messages, ['temperature' => 0.3, 'max_tokens' => 2000]);

        return [
            'comparison' => $comparison,
            'files' => [
                'file1' => [
                    'id' => $file1->id,
                    'name' => $file1->original_name,
                    'version' => $file1->version,
                ],
                'file2' => [
                    'id' => $file2->id,
                    'name' => $file2->original_name,
                    'version' => $file2->version,
                ],
            ],
        ];
    }

    /**
     * Compare a file with all its previous versions.
     */
    public function compareWithVersions(File $file): array
    {
        $versions = File::where('parent_file_id', $file->parent_file_id ?? $file->id)
            ->orWhere('id', $file->parent_file_id ?? $file->id)
            ->orderBy('version')
            ->get();

        if ($versions->count() < 2) {
            return [
                'comparison' => 'No previous versions to compare.',
                'versions' => $versions,
            ];
        }

        $comparisons = [];
        $latest = $versions->last();

        foreach ($versions->where('id', '!=', $latest->id) as $version) {
            try {
                $comparisons[] = $this->execute($latest, $version);
            } catch (\Exception $e) {
                $comparisons[] = [
                    'error' => "Could not compare with version {$version->version}: {$e->getMessage()}",
                ];
            }
        }

        return [
            'comparisons' => $comparisons,
            'versions' => $versions,
        ];
    }

    /**
     * Summarize changes between versions.
     */
    public function summarizeChanges(File $file): string
    {
        $previous = File::where('parent_file_id', $file->parent_file_id ?? $file->id)
            ->where('version', '<', $file->version)
            ->orderBy('version', 'desc')
            ->first();

        if (!$previous) {
            return "No previous version found to compare.";
        }

        $content1 = $this->extractContent($previous);
        $content2 = $this->extractContent($file);

        if (empty($content1) || empty($content2)) {
            return "Could not extract content from files.";
        }

        $messages = [
            ['role' => 'system', 'content' => "You are an academic writing assistant. Summarize the changes made between two document versions, highlighting additions, deletions, and improvements."],
            ['role' => 'user', 'content' => $this->buildDiffPrompt($previous, $file, $content1, $content2)],
        ];

        return $this->call($messages);
    }

    protected function extractContent(File $file): string
    {
        $path = Storage::disk($file->disk)->path($file->path);

        if (!file_exists($path)) {
            return '';
        }

        $mimeType = $file->mime_type;

        return match (true) {
            str_starts_with($mimeType, 'text/') => file_get_contents($path),
            $mimeType === 'application/pdf' => $this->extractPdfText($path),
            default => '',
        };
    }

    protected function extractPdfText(string $path): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            return $pdf->getText();
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function buildSystemPrompt(): string
    {
        return "You are an academic document analyst. Compare two documents and provide:\n\n" .
            "1. **Key Differences**: What changed between the versions\n" .
            "2. **Additions**: New content added\n" .
            "3. **Deletions**: Content removed\n" .
            "4. **Improvements**: Quality improvements made\n" .
            "5. **Recommendations**: Suggestions for further improvement\n\n" .
            "Be specific with examples. Use academic tone.";
    }

    protected function buildUserMessage(File $file1, File $file2, string $content1, string $content2): string
    {
        $maxLen = 5000;
        $c1 = strlen($content1) > $maxLen ? substr($content1, 0, $maxLen) . '...' : $content1;
        $c2 = strlen($content2) > $maxLen ? substr($content2, 0, $maxLen) . '...' : $content2;

        return "Compare the following documents:\n\n" .
            "### Document A\n" .
            "File: {$file1->original_name} (v{$file1->version})\n" .
            "```\n{$c1}\n```\n\n" .
            "### Document B\n" .
            "File: {$file2->original_name} (v{$file2->version})\n" .
            "```\n{$c2}\n```\n\n" .
            "Provide a detailed comparison focusing on content, structure, and quality differences.";
    }

    protected function buildDiffPrompt(File $previous, File $current, string $content1, string $content2): string
    {
        $maxLen = 8000;
        $c1 = strlen($content1) > $maxLen ? substr($content1, 0, $maxLen) . '...' : $content1;
        $c2 = strlen($content2) > $maxLen ? substr($content2, 0, $maxLen) . '...' : $content2;

        return "Summarize the changes between these document versions:\n\n" .
            "### Previous Version (v{$previous->version})\n" .
            "```\n{$c1}\n```\n\n" .
            "### Current Version (v{$current->version})\n" .
            "```\n{$c2}\n```\n\n" .
            "List the key changes in bullet points. Be specific about what was added, removed, or modified.";
    }
}
