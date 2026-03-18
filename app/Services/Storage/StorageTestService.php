<?php

namespace App\Services\Storage;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;

class StorageTestService
{
    protected StorageManager $manager;

    public function __construct(StorageManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Test connection to a storage disk.
     */
    public function testConnection(string $disk): array
    {
        return match ($disk) {
            'local' => $this->testLocalDisk(),
            'do_spaces' => $this->testDoSpaces(),
            'google_drive' => $this->testGoogleDrive(),
            default => ['success' => false, 'message' => 'Unknown disk type.'],
        };
    }

    /**
     * Test local disk connection.
     */
    protected function testLocalDisk(): array
    {
        try {
            Storage::disk('local')->put('_test_connection.txt', 'test');
            Storage::disk('local')->delete('_test_connection.txt');

            return [
                'success' => true,
                'message' => 'Local disk is writable and functioning correctly.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Local disk error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test DigitalOcean Spaces connection.
     */
    protected function testDoSpaces(): array
    {
        if (!$this->manager->isDoSpacesConfigured()) {
            return [
                'success' => false,
                'message' => 'DO Spaces is not configured. Please provide your access key, secret, and bucket name.',
            ];
        }

        try {
            $testPath = '_connection_test_' . time() . '.txt';
            $testContent = 'Storage connection test - ' . date('Y-m-d H:i:s');

            // Test write
            Storage::disk('do_spaces')->put($testPath, $testContent);

            // Test read
            $readContent = Storage::disk('do_spaces')->get($testPath);

            // Test delete
            Storage::disk('do_spaces')->delete($testPath);

            if ($readContent === $testContent) {
                return [
                    'success' => true,
                    'message' => 'Successfully connected to DO Spaces! Bucket: ' . SystemSetting::get('do_spaces_bucket'),
                ];
            }

            return [
                'success' => false,
                'message' => 'Connected to DO Spaces but file content mismatch detected.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'DO Spaces connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Test Google Drive connection.
     */
    protected function testGoogleDrive(): array
    {
        if (!$this->manager->isGoogleDriveConfigured()) {
            return [
                'success' => false,
                'message' => 'Google Drive is not configured. Please provide your OAuth credentials.',
            ];
        }

        try {
            $service = $this->manager->createGoogleDriveService();

            if ($service === null) {
                return [
                    'success' => false,
                    'message' => 'Failed to create Google Drive service. Check your credentials.',
                ];
            }

            // Test by listing files (validates access token)
            $folderId = SystemSetting::get('google_drive_folder_id') ?: 'root';
            $results = $service->files->listFiles([
                'q' => "'{$folderId}' in parents and trashed = false",
                'pageSize' => 1,
                'fields' => 'files(id,name)',
            ]);

            $fileCount = $results->count();

            // Test write
            $testFilename = '_connection_test_' . time() . '.txt';
            $file = new \Google\Service\Drive\DriveFile();
            $file->setName($testFilename);
            $file->setParents([$folderId]);
            $file->setDescription('Storage connection test file');

            $createdFile = $service->files->create($file, [
                'data' => 'Storage connection test - ' . date('Y-m-d H:i:s'),
                'uploadType' => 'media',
                'fields' => 'id',
            ]);

            // Test delete
            $service->files->delete($createdFile->getId());

            return [
                'success' => true,
                'message' => "Successfully connected to Google Drive! Folder contains {$fileCount} item(s).",
            ];
        } catch (\Google\Service\Exception $e) {
            $errorMessage = 'Google Drive API error: ';

            if (str_contains($e->getMessage(), 'invalid_grant')) {
                $errorMessage .= 'Access token expired. Please generate a new refresh token from OAuth Playground.';
            } elseif (str_contains($e->getMessage(), 'unauthorized')) {
                $errorMessage .= 'Unauthorized. Check your client ID and secret.';
            } elseif (str_contains($e->getMessage(), 'notFound')) {
                $errorMessage .= 'Folder not found. Check your folder ID.';
            } else {
                $errorMessage .= $e->getMessage();
            }

            return [
                'success' => false,
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Google Drive connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get storage statistics for a disk.
     */
    public function getStorageStats(string $disk): array
    {
        if ($disk === 'google_drive') {
            return $this->manager->getGoogleDriveUsage();
        }

        if ($disk === 'do_spaces') {
            return $this->manager->getDoSpacesUsage();
        }

        if ($disk === 'local') {
            $storagePath = storage_path('app/private');

            if (!is_dir($storagePath)) {
                return ['error' => 'Storage directory not found'];
            }

            $fileCount = 0;
            $totalSize = 0;

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($storagePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $fileCount++;
                    $totalSize += $file->getSize();
                }
            }

            return [
                'file_count' => $fileCount,
                'total_size' => $totalSize,
                'total_size_human' => $this->formatBytes($totalSize),
                'disk_usage' => disk_free_space($storage_path) ? [
                    'free' => $this->formatBytes(disk_free_space($storagePath)),
                    'total' => $this->formatBytes(disk_total_space($storagePath)),
                ] : null,
            ];
        }

        return ['error' => 'Unknown disk type'];
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
}
