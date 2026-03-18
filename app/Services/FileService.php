<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Services\Storage\StorageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    protected StorageManager $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    public function createDefaultFolders(Student $student): void
    {
        $categories = ['proposal', 'reports', 'thesis', 'simulation', 'data', 'images', 'references'];
        $programme = $student->programme->slug ?? 'general';

        foreach ($categories as $category) {
            Folder::create([
                'student_id' => $student->id,
                'name' => ucfirst($category),
                'path' => "{$programme}/{$student->id}/{$category}",
                'category' => $category,
            ]);
        }

        // Ensure folders exist in cloud storage if using DO Spaces or Google Drive
        $disk = $this->storageManager->getCurrentDisk();
        if ($disk === 'google-drive') {
            $this->storageManager->ensureStudentFolders($student->id, $programme, $categories);
        }
    }

    public function upload(UploadedFile $uploadedFile, Student $student, int $userId, ?int $folderId = null, ?string $description = null): File
    {
        $folder = $folderId ? Folder::find($folderId) : null;
        $disk = $this->storageManager->getCurrentDisk();

        // Build path based on storage type and category
        if ($folder) {
            $basePath = $folder->path;
            $category = $folder->category ?? 'other';
        } else {
            $programme = $student->programme->slug ?? 'general';
            $category = 'other';
            $basePath = $this->storageManager->buildCategoryPath($student->id, $programme, $category);
        }

        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $fullPath = $basePath . '/' . $filename;

        // For Google Drive, ensure the folder structure exists
        if ($disk === 'google-drive') {
            $this->storageManager->ensureGoogleDriveFolder($basePath);
        }

        $path = $uploadedFile->storeAs($basePath, $filename, $disk);

        return File::create([
            'student_id' => $student->id,
            'folder_id' => $folderId,
            'uploaded_by' => $userId,
            'name' => $filename,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'disk' => $disk,
            'path' => $path,
            'description' => $description,
        ]);
    }

    public function uploadNewVersion(UploadedFile $uploadedFile, File $parentFile, int $userId): File
    {
        // Mark old as not latest
        $parentFile->update(['is_latest' => false]);

        $basePath = dirname($parentFile->path);
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $disk = $parentFile->disk;

        // For Google Drive, ensure the folder structure exists
        if ($disk === 'google-drive') {
            $this->storageManager->ensureGoogleDriveFolder($basePath);
        }

        $path = $uploadedFile->storeAs($basePath, $filename, $disk);

        return File::create([
            'student_id' => $parentFile->student_id,
            'folder_id' => $parentFile->folder_id,
            'uploaded_by' => $userId,
            'name' => $filename,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'disk' => $disk,
            'path' => $path,
            'version' => $parentFile->version + 1,
            'parent_file_id' => $parentFile->parent_file_id ?? $parentFile->id,
            'is_latest' => true,
        ]);
    }

    public function delete(File $file): void
    {
        Storage::disk($file->disk)->delete($file->path);
        $file->delete();
    }

    /**
     * Get the storage URL for a file.
     */
    public function getFileUrl(File $file): ?string
    {
        $disk = $file->disk;

        if ($disk === 'local') {
            return null; // Local files are served through download route
        }

        if ($disk === 'do_spaces') {
            $endpoint = SystemSetting::get('do_spaces_endpoint');
            if ($endpoint) {
                return rtrim($endpoint, '/') . '/' . $file->path;
            }
            $bucket = SystemSetting::get('do_spaces_bucket');
            $region = SystemSetting::get('do_spaces_region', 'sgp1');
            return "https://{$bucket}.{$region}.digitaloceanspaces.com/{$file->path}";
        }

        // Google Drive files are served through download route
        return null;
    }
}
