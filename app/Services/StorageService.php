<?php

namespace App\Services;

use App\Models\File;
use App\Models\Folder;
use App\Models\Student;
use App\Models\User;
use Google\Service\Drive\DriveFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    private string $disk;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct(private UserStorageService $userStorageService)
    {
        $this->disk = config('filesystems.default', 'local');
        $this->allowedMimeTypes = config('settings.storage.allowed_mime_types', [
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
        $this->maxFileSize = config('settings.storage.max_file_size', 51200);
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
        $storageOwner = $this->resolveStorageOwner($student);

        if (!$storageOwner) {
            throw new \RuntimeException('No supervisor is assigned to this student. Please contact your administrator to assign one before uploading files.');
        }

        if (!$this->userStorageService->canUseGoogleDrive($storageOwner)) {
            throw new \RuntimeException('Your assigned supervisor has not connected their Google Drive yet. Please ask them to connect Google Drive in their storage settings before uploading files.');
        }

        return $this->uploadToGoogleDrive($uploadedFile, $student, $userId, $folder, $description, $category, $storageOwner);
    }

    public function uploadNewVersion(
        UploadedFile $uploadedFile,
        File $parentFile,
        int $userId,
        ?string $description = null
    ): File {
        $this->validateFile($uploadedFile);

        File::where('id', $parentFile->id)
            ->orWhere('parent_file_id', $parentFile->parent_file_id ?? $parentFile->id)
            ->update(['is_latest' => false]);

        if ($parentFile->disk !== 'google_drive' || !$parentFile->storageOwner) {
            throw new \RuntimeException('This file is not on Google Drive. New versions can only be uploaded for files stored on the supervisor\'s Google Drive.');
        }

        return $this->uploadGoogleDriveVersion($uploadedFile, $parentFile, $userId, $description, $parentFile->storageOwner);
    }

    public function delete(File $file, bool $permanent = false): bool
    {
        if ($permanent) {
            $allVersions = File::where('id', $file->id)
                ->orWhere('parent_file_id', $file->id)
                ->orWhere('parent_file_id', $file->parent_file_id)
                ->get();

            foreach ($allVersions as $version) {
                $this->deleteStoredFile($version);
            }

            return $file->forceDelete();
        }

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
        if ($file->disk !== 'google_drive') {
            return Storage::disk($file->disk)->download($file->path, $file->original_name);
        }

        abort_unless($file->storageOwner, 422, 'Google Drive storage owner not found.');

        $service = $this->userStorageService->googleDriveServiceFor($file->storageOwner);
        abort_unless($service, 422, 'Google Drive is not configured for the assigned supervisor.');

        $response = $service->files->get($file->path, ['alt' => 'media']);
        $stream = $response->getBody();

        return response()->streamDownload(function () use ($stream) {
            while (!$stream->eof()) {
                echo $stream->read(1024 * 8);
            }
        }, $file->original_name, [
            'Content-Type' => $file->mime_type ?: 'application/octet-stream',
        ]);
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
        if ($file->disk === 'google_drive') {
            return true;
        }

        return Storage::disk($file->disk)->exists($file->path);
    }

    public function getFileSize(File $file): int
    {
        if ($file->disk === 'google_drive') {
            return $file->size;
        }

        return Storage::disk($file->disk)->size($file->path);
    }

    public function getLastModified(File $file): \Illuminate\Support\Carbon
    {
        if ($file->disk === 'google_drive') {
            return $file->updated_at;
        }

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
        $path = $parent ? $parent->path . '/' . $name : "files/{$student->id}/{$name}";

        Storage::disk($this->disk)->makeDirectory($path);

        return Folder::create([
            'student_id' => $student->id,
            'parent_id' => $parentId,
            'name' => $name,
            'path' => $path,
            'category' => $category ?? 'other',
        ]);
    }

    public function deleteFolder(Folder $folder, bool $recursive = true): bool
    {
        if ($recursive) {
            foreach ($folder->files as $file) {
                $this->delete($file, true);
            }

            foreach ($folder->children as $child) {
                $this->deleteFolder($child, true);
            }
        }

        Storage::disk($this->disk)->deleteDirectory($folder->path);

        return $folder->delete();
    }

    public function moveFile(File $file, ?Folder $newFolder): File
    {
        $oldPath = $file->path;

        $newPath = $newFolder
            ? $newFolder->path . '/' . $file->name
            : "files/{$file->student_id}/{$file->name}";

        Storage::disk($file->disk)->move($oldPath, $newPath);

        $file->update([
            'folder_id' => $newFolder?->id,
            'path' => $newPath,
        ]);

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
            'storage_owner_id' => $file->storage_owner_id,
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
        ];

        foreach ($categories as $category) {
            $this->createFolder($student, $category['name'], null, $category['slug']);
        }
    }

    private function uploadToLocal(
        UploadedFile $uploadedFile,
        Student $student,
        int $userId,
        ?Folder $folder,
        ?string $description,
        ?string $category
    ): File {
        $path = $this->generateStoragePath($student, $folder, $uploadedFile);

        $storedPath = $uploadedFile->storeAs(dirname($path), basename($path), $this->disk);

        $file = File::create([
            'student_id' => $student->id,
            'folder_id' => $folder?->id,
            'uploaded_by' => $userId,
            'storage_owner_id' => null,
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

        return $file;
    }

    private function uploadToGoogleDrive(
        UploadedFile $uploadedFile,
        Student $student,
        int $userId,
        ?Folder $folder,
        ?string $description,
        ?string $category,
        User $storageOwner
    ): File {
        $service = $this->userStorageService->googleDriveServiceFor($storageOwner);
        if (!$service) {
            throw new \RuntimeException('Assigned supervisor has not configured Google Drive yet.');
        }

        $profile = $this->userStorageService->profileFor($storageOwner);
        $folderId = $this->userStorageService->ensureDriveFolder(
            $service,
            $this->googleDriveFolderSegments($student, $storageOwner, $folder),
            $profile->google_drive_folder_id ?: 'root'
        );

        $driveFile = new DriveFile([
            'name' => $uploadedFile->getClientOriginalName(),
            'parents' => [$folderId],
        ]);

        $createdFile = $service->files->create($driveFile, [
            'data' => file_get_contents($uploadedFile->getRealPath()),
            'mimeType' => $uploadedFile->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        $file = File::create([
            'student_id' => $student->id,
            'folder_id' => $folder?->id,
            'uploaded_by' => $userId,
            'storage_owner_id' => $storageOwner->id,
            'name' => $uploadedFile->getClientOriginalName(),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'disk' => 'google_drive',
            'path' => $createdFile->id,
            'description' => $description,
            'category' => $category ?? $this->detectCategory($uploadedFile),
            'is_latest' => true,
        ]);

        return $file;
    }

    private function uploadGoogleDriveVersion(
        UploadedFile $uploadedFile,
        File $parentFile,
        int $userId,
        ?string $description,
        User $storageOwner
    ): File {
        $service = $this->userStorageService->googleDriveServiceFor($storageOwner);
        if (!$service) {
            throw new \RuntimeException('Assigned supervisor has not configured Google Drive yet.');
        }

        $folder = $parentFile->folder;
        $student = $parentFile->student;
        $profile = $this->userStorageService->profileFor($storageOwner);
        $folderId = $this->userStorageService->ensureDriveFolder(
            $service,
            $this->googleDriveFolderSegments($student, $storageOwner, $folder),
            $profile->google_drive_folder_id ?: 'root'
        );

        $driveFile = new DriveFile([
            'name' => $uploadedFile->getClientOriginalName(),
            'parents' => [$folderId],
        ]);

        $createdFile = $service->files->create($driveFile, [
            'data' => file_get_contents($uploadedFile->getRealPath()),
            'mimeType' => $uploadedFile->getMimeType(),
            'uploadType' => 'multipart',
            'fields' => 'id',
        ]);

        $newVersion = File::create([
            'student_id' => $parentFile->student_id,
            'folder_id' => $parentFile->folder_id,
            'uploaded_by' => $userId,
            'storage_owner_id' => $storageOwner->id,
            'name' => $uploadedFile->getClientOriginalName(),
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'disk' => 'google_drive',
            'path' => $createdFile->id,
            'version' => $parentFile->version + 1,
            'parent_file_id' => $parentFile->parent_file_id ?? $parentFile->id,
            'description' => $description ?? $parentFile->description,
            'category' => $parentFile->category,
            'is_latest' => true,
        ]);

        return $newVersion;
    }

    private function deleteStoredFile(File $file): void
    {
        if ($file->disk === 'google_drive') {
            if (!$file->storageOwner) {
                return;
            }

            $service = $this->userStorageService->googleDriveServiceFor($file->storageOwner);
            if ($service) {
                $service->files->delete($file->path);
            }

            return;
        }

        Storage::disk($file->disk)->delete($file->path);
    }

    private function googleDriveFolderSegments(Student $student, User $storageOwner, ?Folder $folder): array
    {
        $segments = [
            'ResearchFlow',
            'supervisor-' . $storageOwner->id,
            $this->studentFolderName($student),
            'files',
        ];

        if (!$folder) {
            return $segments;
        }

        $relativeSegments = array_values(array_filter(explode('/', $folder->path)));

        if (count($relativeSegments) >= 2) {
            $relativeSegments = array_slice($relativeSegments, 2);
        }

        return [...$segments, ...$relativeSegments];
    }

    private function studentFolderName(Student $student): string
    {
        $name = trim((string) ($student->user?->name ?? ''));
        $name = str_replace(['/', '\\'], '-', $name);

        return $name !== '' ? $name : 'student-' . $student->id;
    }

    private function resolveStorageOwner(Student $student): ?User
    {
        return $student->supervisor ?: $student->cosupervisor;
    }

    private function validateFile(UploadedFile $file): void
    {
        if ($file->getSize() > $this->maxFileSize * 1024) {
            throw new \InvalidArgumentException("File size exceeds maximum allowed of {$this->maxFileSize} KB");
        }

        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \InvalidArgumentException("File type {$file->getMimeType()} is not allowed");
        }
    }

    private function generateStoragePath(Student $student, ?Folder $folder, UploadedFile $file): string
    {
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
            str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet') => 'data',
            str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation') => 'presentation',
            default => 'other',
        };
    }

    public function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }

    public function getAllowedExtensions(): array
    {
        return [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'csv', 'jpg', 'jpeg', 'png', 'gif', 'webp',
            'zip', 'rar', '7z', 'tar', 'gz',
        ];
    }
}
