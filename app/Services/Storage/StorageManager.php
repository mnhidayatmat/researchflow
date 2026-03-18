<?php

namespace App\Services\Storage;

use App\Models\SystemSetting;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\Filesystem;
use League\Flysystem\GoogleDrive\GoogleDriveAdapter;

class StorageManager
{
    /**
     * Get DO Spaces configuration array.
     */
    public function getDoSpacesConfig(): array
    {
        return [
            'driver' => 's3',
            'key' => SystemSetting::get('do_spaces_key', ''),
            'secret' => SystemSetting::get('do_spaces_secret', ''),
            'region' => SystemSetting::get('do_spaces_region', 'sgp1'),
            'bucket' => SystemSetting::get('do_spaces_bucket', ''),
            'url' => SystemSetting::get('do_spaces_endpoint', ''),
            'endpoint' => $this->buildDoSpacesEndpoint(),
            'use_path_style_endpoint' => true,
            'visibility' => 'private',
        ];
    }

    /**
     * Build the DO Spaces endpoint URL.
     */
    protected function buildDoSpacesEndpoint(): string
    {
        $region = SystemSetting::get('do_spaces_region', 'sgp1');
        $bucket = SystemSetting::get('do_spaces_bucket', '');

        if (empty($bucket)) {
            return "https://{$region}.digitaloceanspaces.com";
        }

        return "https://{$bucket}.{$region}.digitaloceanspaces.com";
    }

    /**
     * Get Google Drive configuration array.
     */
    public function getGoogleDriveConfig(): array
    {
        return [
            'driver' => 'google-drive',
            'client_id' => SystemSetting::get('google_drive_client_id', ''),
            'client_secret' => SystemSetting::get('google_drive_client_secret', ''),
            'refresh_token' => SystemSetting::get('google_drive_refresh_token', ''),
            'folder_id' => SystemSetting::get('google_drive_folder_id', ''),
        ];
    }

    /**
     * Check if DO Spaces is properly configured.
     */
    public function isDoSpacesConfigured(): bool
    {
        return !empty(SystemSetting::get('do_spaces_key'))
            && !empty(SystemSetting::get('do_spaces_secret'))
            && !empty(SystemSetting::get('do_spaces_bucket'));
    }

    /**
     * Check if Google Drive is properly configured.
     */
    public function isGoogleDriveConfigured(): bool
    {
        return !empty(SystemSetting::get('google_drive_client_id'))
            && !empty(SystemSetting::get('google_drive_client_secret'))
            && !empty(SystemSetting::get('google_drive_refresh_token'));
    }

    /**
     * Get the current active storage disk.
     */
    public function getCurrentDisk(): string
    {
        return SystemSetting::get('storage_disk', 'local');
    }

    /**
     * Build Google Drive client for authentication.
     */
    public function createGoogleDriveClient(): ?GoogleClient
    {
        if (!$this->isGoogleDriveConfigured()) {
            return null;
        }

        $client = new GoogleClient();
        $client->setClientId(SystemSetting::get('google_drive_client_id'));
        $client->setClientSecret(SystemSetting::get('google_drive_client_secret'));
        $client->setAccessType('offline');
        $client->setScopes([\Google\Service\Drive::DRIVE]);

        $refreshToken = SystemSetting::get('google_drive_refresh_token');
        if (!empty($refreshToken)) {
            $client->fetchAccessTokenWithRefreshToken($refreshToken);
        }

        return $client;
    }

    /**
     * Build Google Drive service instance.
     */
    public function createGoogleDriveService(): ?\Google\Service\Drive
    {
        $client = $this->createGoogleDriveClient();

        if ($client === null) {
            return null;
        }

        return new \Google\Service\Drive($client);
    }

    /**
     * Ensure a folder exists in Google Drive, creating if necessary.
     */
    public function ensureGoogleDriveFolder(string $folderPath, ?string $rootFolderId = null): ?string
    {
        $service = $this->createGoogleDriveService();
        if ($service === null) {
            return null;
        }

        $parts = explode('/', trim($folderPath, '/'));
        $parentId = $rootFolderId ?? SystemSetting::get('google_drive_folder_id');
        $currentFolderId = $parentId;

        foreach ($parts as $folderName) {
            $folderId = $this->findGoogleDriveFolder($service, $folderName, $currentFolderId);

            if ($folderId === null) {
                $folderId = $this->createGoogleDriveFolder($service, $folderName, $currentFolderId);
            }

            $currentFolderId = $folderId;
        }

        return $currentFolderId;
    }

    /**
     * Find a folder in Google Drive by name within a parent.
     */
    protected function findGoogleDriveFolder(\Google\Service\Drive $service, string $name, ?string $parentId = null): ?string
    {
        $query = "name = '{$name}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false";

        if ($parentId) {
            $query .= " and '{$parentId}' in parents";
        }

        $results = $service->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'pageSize' => 1,
        ]);

        if ($results->count() > 0) {
            return $results[0]->id;
        }

        return null;
    }

    /**
     * Create a new folder in Google Drive.
     */
    protected function createGoogleDriveFolder(\Google\Service\Drive $service, string $name, ?string $parentId = null): ?string
    {
        $file = new \Google\Service\Drive\DriveFile();
        $file->setName($name);
        $file->setMimeType('application/vnd.google-apps.folder');

        if ($parentId) {
            $file->setParents([$parentId]);
        }

        $createdFile = $service->files->create($file, [
            'fields' => 'id',
        ]);

        return $createdFile->id;
    }

    /**
     * Build category-based path for file storage.
     */
    public function buildCategoryPath(int $studentId, string $programmeSlug, string $category): string
    {
        $disk = $this->getCurrentDisk();

        return match ($disk) {
            'do_spaces' => "students/{$programmeSlug}/{$studentId}/{$category}",
            'google_drive' => "{$programmeSlug}/{$studentId}/{$category}",
            default => "{$programmeSlug}/{$studentId}/{$category}",
        };
    }

    /**
     * Ensure all default folders exist for a student in the current storage.
     */
    public function ensureStudentFolders(int $studentId, string $programmeSlug, array $categories): void
    {
        $disk = $this->getCurrentDisk();

        if ($disk === 'google_drive') {
            foreach ($categories as $category) {
                $path = $this->buildCategoryPath($studentId, $programmeSlug, $category);
                $this->ensureGoogleDriveFolder($path);
            }
        }
    }

    /**
     * Get storage usage statistics for a disk.
     */
    public function getStorageUsage(string $disk): array
    {
        try {
            if ($disk === 'google_drive') {
                return $this->getGoogleDriveUsage();
            }

            if ($disk === 'do_spaces') {
                return $this->getDoSpacesUsage();
            }

            return ['file_count' => 0, 'total_size' => 0];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get Google Drive storage usage.
     */
    protected function getGoogleDriveUsage(): array
    {
        $service = $this->createGoogleDriveService();
        if ($service === null) {
            return ['error' => 'Google Drive not configured'];
        }

        $about = $service->about->get(['fields' => 'storageQuota']);

        return [
            'limit' => (int) ($about->getStorageQuota()->getLimit() ?? 0),
            'usage' => (int) ($about->getStorageQuota()->getUsage() ?? 0),
            'usage_in_drive' => (int) ($about->getStorageQuota()->getUsageInDrive() ?? 0),
        ];
    }

    /**
     * Get DO Spaces file count (approximate).
     */
    protected function getDoSpacesUsage(): array
    {
        try {
            $adapter = Storage::disk('do_spaces')->getAdapter();
            $client = $adapter->getClient();
            $bucket = SystemSetting::get('do_spaces_bucket');

            $objects = $client->getIterator('ListObjects', [
                'Bucket' => $bucket,
            ]);

            $fileCount = 0;
            $totalSize = 0;

            foreach ($objects as $object) {
                $fileCount++;
                $totalSize += $object['Size'] ?? 0;
            }

            return [
                'file_count' => $fileCount,
                'total_size' => $totalSize,
                'total_size_human' => $this->formatBytes($totalSize),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Format bytes to human-readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Create a Google Drive filesystem adapter.
     */
    public function createGoogleDriveAdapter(): \Illuminate\Filesystem\FilesystemAdapter
    {
        $service = $this->createGoogleDriveService();
        $rootFolderId = SystemSetting::get('google_drive_folder_id', null);

        $adapter = new GoogleDriveAdapter($service, $rootFolderId);

        return new \Illuminate\Filesystem\FilesystemAdapter(
            new Filesystem($adapter),
            $adapter,
            ['visibility' => 'private']
        );
    }
}
