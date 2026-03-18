<?php

namespace App\Services\Storage;

use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Str;
use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToDeleteFile;

class GoogleDriveAdapter implements FilesystemAdapter
{
    protected Drive $service;
    protected ?string $rootFolderId;
    protected array $pathCache = [];

    public function __construct(Drive $service, ?string $rootFolderId = null)
    {
        $this->service = $service;
        $this->rootFolderId = $rootFolderId;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->upload($path, $contents);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream(string $path, $resource, Config $config): void
    {
        $this->upload($path, stream_get_contents($resource));
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): string
    {
        $fileId = $this->getFileId($path);

        if ($fileId === null) {
            throw UnableToReadFile::fromLocation($path);
        }

        $response = $this->service->files->get($fileId, ['alt' => 'media']);

        return (string) $response->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function readStream(string $path)
    {
        $fileId = $this->getFileId($path);

        if ($fileId === null) {
            throw UnableToReadFile::fromLocation($path);
        }

        $response = $this->service->files->get($fileId, ['alt' => 'media']);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, (string) $response->getBody());
        rewind($stream);

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): void
    {
        $fileId = $this->getFileId($path);

        if ($fileId !== null) {
            $this->service->files->delete($fileId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $path): void
    {
        $folderId = $this->getFolderId($path);

        if ($folderId !== null) {
            $this->service->files->delete($folderId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->ensureFolder($path);
    }

    /**
     * {@inheritdoc}
     */
    public function directoryExists(string $path): bool
    {
        return $this->getFolderId($path) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists(string $path): bool
    {
        return $this->getFileId($path) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $path): bool
    {
        return $this->fileExists($path) || $this->directoryExists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents(string $path = '', bool $deep = false): iterable
    {
        $folderId = empty($path) ? $this->rootFolderId : $this->getFolderId($path);

        if ($folderId === null) {
            return [];
        }

        $results = $this->service->files->listFiles([
            'q' => "'{$folderId}' in parents and trashed = false",
            'fields' => 'files(id,name,mimeType,size,createdTime,modifiedTime)',
        ]);

        $contents = [];
        foreach ($results->getFiles() as $file) {
            $contents[] = [
                'type' => $file->getMimeType() === 'application/vnd.google-apps.folder' ? 'dir' : 'file',
                'path' => $file->getName(),
                'filename' => $file->getName(),
                'size' => (int) ($file->getSize() ?? 0),
                'timestamp' => strtotime($file->getModifiedTime()),
            ];
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path): int
    {
        $fileId = $this->getFileId($path);

        if ($fileId === null) {
            throw UnableToReadFile::fromLocation($path);
        }

        $file = $this->service->files->get($fileId, ['fields' => 'modifiedTime']);

        return strtotime($file->getModifiedTime());
    }

    /**
     * {@inheritdoc}
     */
    public function fileSize(string $path): int
    {
        $fileId = $this->getFileId($path);

        if ($fileId === null) {
            throw UnableToReadFile::fromLocation($path);
        }

        $file = $this->service->files->get($fileId, ['fields' => 'size']);

        return (int) ($file->getSize() ?? 0);
    }

    /**
     * {@inheritdoc}
     */
    public function mimeType(string $path): string
    {
        $fileId = $this->getFileId($path);

        if ($fileId === null) {
            throw UnableToReadFile::fromLocation($path);
        }

        $file = $this->service->files->get($fileId, ['fields' => 'mimeType']);

        // Convert Google Docs formats to standard MIME types
        $googleMimeTypes = [
            'application/vnd.google-apps.document' => 'application/pdf',
            'application/vnd.google-apps.spreadsheet' => 'application/vnd.ms-excel',
            'application/vnd.google-apps.presentation' => 'application/vnd.ms-powerpoint',
        ];

        return $googleMimeTypes[$file->getMimeType()] ?? $file->getMimeType();
    }

    /**
     * {@inheritdoc}
     */
    public function visibility(string $path): string
    {
        return 'private';
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility(string $path, string $visibility): void
    {
        // Google Drive files are always private to the account
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $sourceFileId = $this->getFileId($source);
        $destFolderId = $this->ensureFolder(dirname($destination));

        if ($sourceFileId === null) {
            throw UnableToReadFile::fromLocation($source);
        }

        $file = $this->service->files->get($sourceFileId, ['fields' => 'parents']);

        $this->service->files->update($sourceFileId, [
            'parents' => [$destFolderId],
            'name' => basename($destination),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $sourceFileId = $this->getFileId($source);

        if ($sourceFileId === null) {
            throw UnableToReadFile::fromLocation($source);
        }

        $destFolderId = $this->ensureFolder(dirname($destination));

        $file = new DriveFile();
        $file->setParents([$destFolderId]);
        $file->setName(basename($destination));

        $this->service->files->copy($sourceFileId, $file);
    }

    /**
     * Upload a file to Google Drive.
     */
    protected function upload(string $path, string $contents): void
    {
        $folderId = $this->ensureFolder(dirname($path));
        $filename = basename($path);

        // Check if file exists
        $existingFile = $this->findFileInFolder($folderId, $filename);

        if ($existingFile) {
            // Update existing file
            $file = new DriveFile();
            $this->service->files->update($existingFile->id, $file, [
                'data' => $contents,
                'uploadType' => 'media',
            ]);
        } else {
            // Create new file
            $file = new DriveFile();
            $file->setName($filename);
            $file->setParents([$folderId]);

            $this->service->files->create($file, [
                'data' => $contents,
                'uploadType' => 'media',
                'fields' => 'id',
            ]);
        }
    }

    /**
     * Get file ID by path.
     */
    protected function getFileId(string $path): ?string
    {
        $parts = array_filter(explode('/', trim($path, '/')));
        $filename = array_pop($parts);
        $folderPath = implode('/', $parts);

        $folderId = empty($parts) ? $this->rootFolderId : $this->getFolderId($folderPath);

        if ($folderId === null) {
            return null;
        }

        $file = $this->findFileInFolder($folderId, $filename);

        return $file ? $file->id : null;
    }

    /**
     * Find a file in a folder by name.
     */
    protected function findFileInFolder(string $folderId, string $filename): ?DriveFile
    {
        $cacheKey = "{$folderId}:{$filename}";

        if (isset($this->pathCache[$cacheKey])) {
            return $this->pathCache[$cacheKey];
        }

        $results = $this->service->files->listFiles([
            'q' => "'{$folderId}' in parents and name = '{$filename}' and trashed = false",
            'fields' => 'files(id,name)',
        ]);

        if ($results->count() > 0) {
            $this->pathCache[$cacheKey] = $results[0];
            return $results[0];
        }

        $this->pathCache[$cacheKey] = null;
        return null;
    }

    /**
     * Get folder ID by path.
     */
    protected function getFolderId(string $path): ?string
    {
        $parts = array_filter(explode('/', trim($path, '/')));
        $currentFolderId = $this->rootFolderId;

        foreach ($parts as $folderName) {
            $folder = $this->findFolderInFolder($currentFolderId, $folderName);

            if ($folder === null) {
                return null;
            }

            $currentFolderId = $folder->id;
        }

        return $currentFolderId;
    }

    /**
     * Find a folder in a folder by name.
     */
    protected function findFolderInFolder(?string $parentFolderId, string $folderName): ?DriveFile
    {
        if ($parentFolderId === null) {
            return null;
        }

        $cacheKey = "{$parentFolderId}:{$folderName}:dir";

        if (isset($this->pathCache[$cacheKey])) {
            return $this->pathCache[$cacheKey];
        }

        $results = $this->service->files->listFiles([
            'q' => "'{$parentFolderId}' in parents and name = '{$folderName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false",
            'fields' => 'files(id,name)',
        ]);

        if ($results->count() > 0) {
            $this->pathCache[$cacheKey] = $results[0];
            return $results[0];
        }

        $this->pathCache[$cacheKey] = null;
        return null;
    }

    /**
     * Ensure a folder exists, creating if necessary.
     */
    protected function ensureFolder(string $path): string
    {
        if (empty($path) || $path === '.') {
            return $this->rootFolderId ?: 'root';
        }

        $folderId = $this->getFolderId($path);

        if ($folderId !== null) {
            return $folderId;
        }

        return $this->createFolder($path);
    }

    /**
     * Create a folder and return its ID.
     */
    protected function createFolder(string $path): string
    {
        $parts = array_filter(explode('/', trim($path, '/')));
        $folderName = array_pop($parts);
        $parentPath = implode('/', $parts);
        $parentId = empty($parts) ? ($this->rootFolderId ?: 'root') : $this->ensureFolder($parentPath);

        $file = new DriveFile();
        $file->setName($folderName);
        $file->setMimeType('application/vnd.google-apps.folder');
        $file->setParents([$parentId]);

        $createdFile = $this->service->files->create($file, ['fields' => 'id']);

        return $createdFile->getId();
    }

    /**
     * Get the underlying Google Drive service.
     */
    public function getService(): Drive
    {
        return $this->service;
    }
}
