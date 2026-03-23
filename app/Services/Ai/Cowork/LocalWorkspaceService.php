<?php

namespace App\Services\Ai\Cowork;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class LocalWorkspaceService
{
    protected array $blockedPaths;

    public function __construct(protected Filesystem $files)
    {
        $this->blockedPaths = [
            $this->normalizePath(base_path('.env')),
            $this->normalizePath(base_path('.git')),
            $this->normalizePath(base_path('vendor')),
            $this->normalizePath(base_path('node_modules')),
            $this->normalizePath(storage_path('framework')),
            $this->normalizePath(base_path('bootstrap/cache')),
        ];
    }

    public function inspect(string $inputPath): array
    {
        $path = $this->resolvePath($inputPath);
        $exists = $this->files->exists($path);

        if (!$exists) {
            return [
                'path' => $path,
                'exists' => false,
                'type' => 'missing',
                'directory_entries' => [],
                'content' => null,
                'is_text' => $this->isTextPath($path),
            ];
        }

        if ($this->files->isDirectory($path)) {
            return [
                'path' => $path,
                'exists' => true,
                'type' => 'directory',
                'directory_entries' => $this->listDirectory($path),
                'content' => null,
                'is_text' => false,
            ];
        }

        return [
            'path' => $path,
            'exists' => true,
            'type' => 'file',
            'directory_entries' => [],
            'content' => $this->isTextPath($path) ? $this->readFile($path) : null,
            'is_text' => $this->isTextPath($path),
        ];
    }

    public function execute(array $plan, string $inputPath): array
    {
        $path = $this->resolvePath($inputPath);
        $operation = $plan['operation'] ?? 'error';
        $targetType = $plan['target_type'] ?? ($this->files->isDirectory($path) ? 'directory' : 'file');

        return match ($operation) {
            'list' => $this->listResult($path),
            'read' => $this->readResult($path),
            'create' => $targetType === 'directory'
                ? $this->createDirectory($path)
                : $this->createFile($path, (string) ($plan['content'] ?? '')),
            'update' => $this->updateFile($path, (string) ($plan['content'] ?? '')),
            'delete' => $targetType === 'directory'
                ? $this->deleteDirectory($path)
                : $this->deleteFile($path),
            default => throw new RuntimeException($plan['clarification'] ?? 'Cowork could not determine a safe file operation.'),
        };
    }

    public function browse(?string $inputPath = null): array
    {
        $path = $inputPath ? $this->resolvePath($inputPath) : $this->normalizePath(base_path());

        if (!$this->files->isDirectory($path)) {
            throw new RuntimeException('Cowork directory browser only works with directories.');
        }

        $entries = collect($this->listDirectory($path))
            ->values()
            ->all();

        $parent = dirname($path);
        if ($parent === '.' || $parent === $path) {
            $parent = null;
        } else {
            try {
                $this->assertAllowed($this->normalizePath($parent));
                $parent = $this->normalizePath($parent);
            } catch (RuntimeException) {
                $parent = null;
            }
        }

        return [
            'path' => $path,
            'parent' => $parent,
            'entries' => $entries,
        ];
    }

    public function resolvePath(string $inputPath): string
    {
        $path = trim($inputPath);
        if ($path === '') {
            throw new RuntimeException('Workspace path is required.');
        }

        $candidate = $this->isAbsolutePath($path)
            ? $path
            : base_path($path);

        $normalized = $this->normalizePath($candidate);
        $this->assertAllowed($normalized);

        return $normalized;
    }

    protected function listResult(string $path): array
    {
        if (!$this->files->isDirectory($path)) {
            throw new RuntimeException('Cowork can only list directories.');
        }

        $entries = $this->listDirectory($path);

        return [
            'operation' => 'list',
            'path' => $path,
            'summary' => 'Listed directory contents.',
            'preview' => collect($entries)
                ->take(20)
                ->map(fn (array $entry) => sprintf('- %s (%s)', $entry['name'], $entry['type']))
                ->implode("\n"),
            'details' => [
                'entries' => $entries,
            ],
        ];
    }

    protected function readResult(string $path): array
    {
        if (!$this->files->exists($path) || $this->files->isDirectory($path)) {
            throw new RuntimeException('Cowork can only read existing files.');
        }

        if (!$this->isTextPath($path)) {
            throw new RuntimeException('Cowork only reads text-like files.');
        }

        $content = $this->readFile($path);

        return [
            'operation' => 'read',
            'path' => $path,
            'summary' => 'Read file contents.',
            'preview' => $this->truncate($content),
            'details' => [
                'size' => $this->files->size($path),
            ],
        ];
    }

    protected function createFile(string $path, string $content): array
    {
        if ($this->files->exists($path)) {
            throw new RuntimeException('Target file already exists. Ask Cowork to update it instead.');
        }

        if (!$this->isTextPath($path)) {
            throw new RuntimeException('Cowork only creates text-like files.');
        }

        $this->ensureParentDirectory($path);
        $this->files->put($path, $content);

        return [
            'operation' => 'create',
            'path' => $path,
            'summary' => 'Created file.',
            'preview' => $this->truncate($content),
            'details' => [
                'bytes_written' => strlen($content),
            ],
        ];
    }

    protected function updateFile(string $path, string $content): array
    {
        if (!$this->files->exists($path) || $this->files->isDirectory($path)) {
            throw new RuntimeException('Cowork can only update existing files.');
        }

        if (!$this->isTextPath($path)) {
            throw new RuntimeException('Cowork only updates text-like files.');
        }

        $this->files->put($path, $content);

        return [
            'operation' => 'update',
            'path' => $path,
            'summary' => 'Updated file.',
            'preview' => $this->truncate($content),
            'details' => [
                'bytes_written' => strlen($content),
            ],
        ];
    }

    protected function deleteFile(string $path): array
    {
        if (!$this->files->exists($path) || $this->files->isDirectory($path)) {
            throw new RuntimeException('Cowork can only delete existing files.');
        }

        $this->files->delete($path);

        return [
            'operation' => 'delete',
            'path' => $path,
            'summary' => 'Deleted file.',
            'preview' => null,
            'details' => [],
        ];
    }

    protected function createDirectory(string $path): array
    {
        if ($this->files->exists($path)) {
            throw new RuntimeException('Target directory already exists.');
        }

        $this->files->ensureDirectoryExists($path);

        return [
            'operation' => 'create',
            'path' => $path,
            'summary' => 'Created directory.',
            'preview' => null,
            'details' => [],
        ];
    }

    protected function deleteDirectory(string $path): array
    {
        if (!$this->files->isDirectory($path)) {
            throw new RuntimeException('Cowork can only delete existing directories.');
        }

        $this->files->deleteDirectory($path);

        return [
            'operation' => 'delete',
            'path' => $path,
            'summary' => 'Deleted directory.',
            'preview' => null,
            'details' => [],
        ];
    }

    protected function readFile(string $path): string
    {
        $content = $this->files->get($path);

        if (strlen($content) > 120000) {
            throw new RuntimeException('File is too large for Cowork. Keep files under 120 KB.');
        }

        return $content;
    }

    protected function listDirectory(string $path): array
    {
        return collect($this->files->glob($path . DIRECTORY_SEPARATOR . '*') ?: [])
            ->sort()
            ->take(50)
            ->map(fn (string $entry) => [
                'name' => basename($entry),
                'path' => $entry,
                'type' => is_dir($entry) ? 'directory' : 'file',
            ])
            ->values()
            ->all();
    }

    protected function ensureParentDirectory(string $path): void
    {
        $parent = dirname($path);
        $this->assertAllowed($parent);
        $this->files->ensureDirectoryExists($parent);
    }

    protected function assertAllowed(string $path): void
    {
        $base = $this->normalizePath(base_path());
        $storageApp = $this->normalizePath(storage_path('app'));

        if (!$this->startsWithPath($path, $base) && !$this->startsWithPath($path, $storageApp)) {
            throw new RuntimeException('Cowork can only work inside this Laravel project or storage/app.');
        }

        foreach ($this->blockedPaths as $blockedPath) {
            if ($path === $blockedPath || $this->startsWithPath($path, $blockedPath)) {
                throw new RuntimeException('Cowork is blocked from touching protected project paths.');
            }
        }
    }

    protected function isTextPath(string $path): bool
    {
        $filename = strtolower(basename($path));
        if ($filename === '.env.example') {
            return true;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, [
            'php', 'js', 'ts', 'tsx', 'jsx', 'json', 'md', 'txt', 'css', 'scss',
            'html', 'xml', 'yml', 'yaml', 'sql', 'csv', 'blade', 'env',
        ], true) || str_ends_with($filename, '.blade.php');
    }

    protected function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1 || str_starts_with($path, DIRECTORY_SEPARATOR);
    }

    protected function normalizePath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $prefix = preg_match('/^[A-Za-z]:/', $path, $matches) === 1 ? strtoupper($matches[0]) : '';
        $pathWithoutPrefix = $prefix !== '' ? substr($path, strlen($prefix)) : $path;
        $segments = preg_split('#[\\\\/]+#', $pathWithoutPrefix) ?: [];
        $normalized = [];

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($normalized);
                continue;
            }

            $normalized[] = $segment;
        }

        $assembled = implode(DIRECTORY_SEPARATOR, $normalized);

        if ($prefix !== '') {
            return rtrim($prefix . DIRECTORY_SEPARATOR . $assembled, DIRECTORY_SEPARATOR);
        }

        return str_starts_with($path, DIRECTORY_SEPARATOR)
            ? DIRECTORY_SEPARATOR . $assembled
            : $assembled;
    }

    protected function startsWithPath(string $path, string $root): bool
    {
        return $path === $root || str_starts_with($path, $root . DIRECTORY_SEPARATOR);
    }

    protected function truncate(?string $content, int $limit = 4000): ?string
    {
        if ($content === null || strlen($content) <= $limit) {
            return $content;
        }

        return substr($content, 0, $limit) . "\n\n[preview truncated]";
    }
}
