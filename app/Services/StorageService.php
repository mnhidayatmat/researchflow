<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    private string $disk;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local');
        $this->allowedMimeTypes = config('storage.allowed_mime_types', [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/zip',
            'application/x-rar-compressed',
        ]);
        $this->maxFileSize = config('storage.max_file_size', 51200); // 50MB in KB
    }

    public function setDisk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }

    public function getDisk(): string
    {
        return $this->disk;
    }

    public function upload(
        UploadedFile $uploadedFile,
        Student $student,
        int $userId,
        ?int $folderId = null,
        ?string $description = null,
        ?string $category = null
    ): File {
        $this->validateFile($uploadedFile);

        $folder = $folderId ? Folder::find($folderId) : null;
        $path = $this->generateStoragePath($student, $folder, $uploadedFile);

        $storedPath = $uploadedFile->storeAs(
            dirname($path),
            basename($path),
            $this->disk
        );

        $file = File::create([
            'student_id' => $student->id,
            'folder_id' => $folderId,
            'uploaded_by' => $userId,
            'name' => basename($path),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'disk' => $this->disk,
            'path' => $storedPath,
            'description' => $description,
            'category' => $category ?? $this->detectCategory($uploadedFile),
            'is_latest' => true,
        ]);

        // Update folder size if applicable
        if ($folder) {
            $folder->increment('size', $uploadedFile->getSize());
        }

        return $file;
    }

    public function uploadNewVersion(
        UploadedFile $uploadedFile,
        File $parentFile,
        int $userId,
        ?string $description = null
    ): File {
        $this->validateFile($uploadedFile);

        // Mark parent and all previous versions as not latest
        File::where('id', $parentFile->id)
            ->orWhere('parent_file_id', $parentFile->parent_file_id ?? $parentFile->id)
            ->update(['is_latest' => false]);

        $basePath = dirname($parentFile->path);
        $filename = $this->generateUniqueFilename($uploadedFile);
        $path = $basePath . '/' . $filename;

        $storedPath = $uploadedFile->storeAs(
            $basePath,
            $filename,
            $parentFile->disk
        );

        $newVersion = File::create([
            'student_id' => $parentFile->student_id,
            'folder_id' => $parentFile->folder_id,
            'uploaded_by' => $userId,
            'name' => $filename,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'disk' => $parentFile->disk,
            'path' => $storedPath,
            'version' => $parentFile->version + 1,
            'parent_file_id' => $parentFile->parent_file_id ?? $parentFile->id,
            'description' => $description ?? $parentFile->description,
            'category' => $parentFile->category,
            'is_latest' => true,
        ]);

        // Update folder size
        if ($parentFile->folder) {
            $parentFile->folder->increment('size', $uploadedFile->getSize());
        }

        return $newVersion;
    }

    public function delete(File $file, bool $permanent = false): bool
    {
        if ($permanent) {
            // Delete all versions
            $allVersions = File::where('id', $file->id)
                ->orWhere('parent_file_id', $file->id)
                ->orWhere('parent_file_id', $file->parent_file_id)
                ->get();

            foreach ($allVersions as $version) {
                Storage::disk($version->disk)->delete($version->path);
            }

            // Update folder size
            if ($file->folder) {
                $totalSize = $allVersions->sum('size');
                $file->folder->decrement('size', $totalSize);
            }

            return $file->forceDelete();
        }

        // Soft delete
        $file->delete();
        return true;
    }

    public function restore(File $file): File
    {
        $file->restore();
        return $file;
    }

    public function download(File $file): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function getStream(File $file)
    {
        return Storage::disk($file->disk)->readStream($file->path);
    }

    public function getUrl(File $file): string
    {
        return Storage::disk($file->disk)->url($file->path);
    }

    public function getTemporaryUrl(File $file, \DateTimeInterface $expiration): string
    {
        return Storage::disk($file->disk)->temporaryUrl($file->path, $expiration);
    }

    public function exists(File $file): bool
    {
        return Storage::disk($file->disk)->exists($file->path);
    }

    public function getFileSize(File $file): int
    {
        return Storage::disk($file->disk)->size($file->path);
    }

    public function getLastModified(File $file): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::createFromTimestamp(
            Storage::disk($file->disk)->lastModified($file->path)
        );
    }

    public function createFolder(
        Student $student,
        string $name,
        ?int $parentId = null,
        ?string $category = null
    ): Folder {
        $parent = $parentId ? Folder::find($parentId) : null;

        $path = $parent
            ? $parent->path . '/' . $name
            : "files/{$student->id}/{$name}";

        // Create physical directory
        Storage::disk($this->disk)->makeDirectory($path);

        return Folder::create([
            'student_id' => $student->id,
            'parent_id' => $parentId,
            'name' => $name,
            'path' => $path,
            'category' => $category ?? 'other',
            'size' => 0,
        ]);
    }

    public function deleteFolder(Folder $folder, bool $recursive = true): bool
    {
        if ($recursive) {
            // Delete all files in folder
            foreach ($folder->files as $file) {
                $this->delete($file, true);
            }

            // Delete all subfolders
            foreach ($folder->children as $child) {
                $this->deleteFolder($child, true);
            }
        }

        // Delete physical directory
        Storage::disk($this->disk)->deleteDirectory($folder->path);

        return $folder->delete();
    }

    public function moveFile(File $file, ?Folder $newFolder): File
    {
        $oldFolder = $file->folder;
        $oldPath = $file->path;

        $newPath = $newFolder
            ? $newFolder->path . '/' . $file->name
            : "files/{$file->student_id}/{$file->name}";

        Storage::disk($file->disk)->move($oldPath, $newPath);

        $file->update([
            'folder_id' => $newFolder?->id,
            'path' => $newPath,
        ]);

        // Update folder sizes
        if ($oldFolder) {
            $oldFolder->decrement('size', $file->size);
        }
        if ($newFolder) {
            $newFolder->increment('size', $file->size);
        }

        return $file->fresh();
    }

    public function copyFile(File $file, ?Folder $targetFolder, int $userId): File
    {
        $newFilename = $this->generateUniqueFilenameFromFile($file);
        $newPath = $targetFolder
            ? $targetFolder->path . '/' . $newFilename
            : "files/{$file->student_id}/{$newFilename}";

        Storage::disk($file->disk)->copy($file->path, $newPath);

        $newFile = File::create([
            'student_id' => $file->student_id,
            'folder_id' => $targetFolder?->id,
            'uploaded_by' => $userId,
            'name' => $newFilename,
            'original_name' => $file->original_name,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'disk' => $file->disk,
            'path' => $newPath,
            'description' => $file->description,
            'category' => $file->category,
            'version' => 1,
            'is_latest' => true,
        ]);

        if ($targetFolder) {
            $targetFolder->increment('size', $file->size);
        }

        return $newFile;
    }

    public function getStorageUsage(Student $student): array
    {
        $files = $student->files()->where('is_latest', true)->get();

        return [
            'total_files' => $files->count(),
            'total_size' => $files->sum('size'),
            'by_category' => $files->groupBy('category')
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'size' => $group->sum('size'),
                ]),
            'by_type' => $files->groupBy(fn($f) => Str::before($f->mime_type, '/'))
                ->map(fn($group) => [
                    'count' => $group->count(),
                    'size' => $group->sum('size'),
                ]),
        ];
    }

    public function createDefaultFolders(Student $student): void
    {
        $categories = [
            ['name' => 'Proposals', 'slug' => 'proposal'],
            ['name' => 'Progress Reports', 'slug' => 'reports'],
            ['name' => 'Thesis Drafts', 'slug' => 'thesis'],
            ['name' => 'Simulations', 'slug' => 'simulation'],
            ['name' => 'Data & Results', 'slug' => 'data'],
            ['name' => 'Images & Figures', 'slug' => 'images'],
            ['name' => 'References', 'slug' => 'references'],
            ['name' => 'Meeting Notes', 'slug' => 'meetings'],
            ['name' => 'Presentations', 'slug' => 'presentations'],
        ];

        foreach ($categories as $category) {
            $this->createFolder(
                $student,
                $category['name'],
                null,
                $category['slug']
            );
        }
    }

    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxFileSize * 1024) {
            throw new \InvalidArgumentException(
                "File size exceeds maximum allowed of {$this->maxFileSize} KB"
            );
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException(
                "File type {$file->getMimeType()} is not allowed"
            );
        }
    }

    private function generateStoragePath(
        Student $student,
        ?Folder $folder,
        UploadedFile $file
    ): string {
        $basePath = $folder ? $folder->path : "files/{$student->id}";
        $filename = $this->generateUniqueFilename($file);

        return $basePath . '/' . $filename;
    }

    private function generateUniqueFilename(UploadedFile $file): string
    {
        return Str::uuid() . '.' . $file->getClientOriginalExtension();
    }

    private function generateUniqueFilenameFromFile(File $file): string
    {
        $extension = pathinfo($file->name, PATHINFO_EXTENSION);
        return Str::uuid() . '.' . $extension;
    }

    private function detectCategory(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        return match (true) {
            str_starts_with($mimeType, 'image/') => 'images',
            str_starts_with($mimeType, 'video/') => 'media',
            $mimeType === 'application/pdf' => 'documents',
            str_contains($mimeType, 'word') || str_contains($mimeType, 'document') => 'documents',
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'data',
            str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation') => 'presentations',
            str_contains($mimeType, 'zip') || str_contains($mimeType, 'rar') => 'archives',
            default => 'other',
        };
    }

    public function sanitizeFilename(string $filename): string
    {
        // Remove any characters that aren't alphanumeric, hyphens, underscores, or periods
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }

    public function getAllowedExtensions(): array
    {
        return [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp',
            'zip', 'rar', '7z', 'tar', 'gz'
        ];
    }
}
