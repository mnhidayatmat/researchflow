<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Folder;
use App\Models\Student;
use App\Services\Ai\AiRagService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileApiController extends Controller
{
    public function __construct(
        private StorageService $storage,
        private AiRagService $ragService
    ) {}

    public function index(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $query = $student->files()->where('is_latest', true);

        // Filter by folder
        if ($request->has('folder_id')) {
            $query->where('folder_id', $request->folder_id);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('original_name', 'like', "%{$request->search}%");
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $files = $query->paginate($request->per_page ?? 20);

        return response()->json($files);
    }

    public function folders(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $query = $student->folders();

        // Filter by parent
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->whereNull('parent_id');
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $folders = $query->orderBy('name')->get();

        return response()->json($folders);
    }

    public function folderTree(Student $student)
    {
        $this->authorize('view', $student);

        $folders = $student->folders()
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->with(['children']);
            }])
            ->orderBy('name')
            ->get();

        return response()->json($folders);
    }

    public function show(Student $student, File $file)
    {
        $this->authorize('view', $student);

        $file->load(['uploadedBy', 'folder']);

        // Get all versions
        $rootFile = $file->parent_file_id ? File::find($file->parent_file_id) : $file;
        $versions = File::where('parent_file_id', $rootFile->id)
            ->orWhere('id', $rootFile->id)
            ->orderBy('version', 'desc')
            ->get();

        return response()->json([
            'file' => $file,
            'versions' => $versions,
        ]);
    }

    public function upload(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'file' => 'required|file|max:51200',
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:50',
        ]);

        $file = $this->storage->upload(
            $request->file('file'),
            $student,
            Auth::id(),
            $validated['folder_id'] ?? null,
            $validated['description'] ?? null,
            $validated['category'] ?? null
        );

        $this->indexFileForAi($file);

        return response()->json($file->load(['uploadedBy', 'folder']), 201);
    }

    public function uploadMultiple(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'required|file|max:51200',
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:50',
        ]);

        $files = [];
        foreach ($validated['files'] as $uploadedFile) {
            $file = $this->storage->upload(
                $uploadedFile,
                $student,
                Auth::id(),
                $validated['folder_id'] ?? null,
                $validated['description'] ?? null,
                $validated['category'] ?? null
            );
            $this->indexFileForAi($file);
            $files[] = $file;
        }

        return response()->json([
            'message' => count($files) . ' file(s) uploaded successfully.',
            'files' => $files,
        ], 201);
    }

    public function uploadVersion(Request $request, Student $student, File $file)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'file' => 'required|file|max:51200',
            'description' => 'nullable|string|max:500',
        ]);

        $newVersion = $this->storage->uploadNewVersion(
            $request->file('file'),
            $file,
            Auth::id(),
            $validated['description'] ?? null
        );

        $this->indexFileForAi($newVersion);

        return response()->json($newVersion->load(['uploadedBy', 'folder']), 201);
    }

    public function update(Request $request, Student $student, File $file)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:50',
        ]);

        $file->update($validated);

        return response()->json($file->load(['uploadedBy', 'folder']));
    }

    public function move(Request $request, Student $student, File $file)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $folder = $validated['folder_id'] ? Folder::find($validated['folder_id']) : null;

        $updatedFile = $this->storage->moveFile($file, $folder);

        return response()->json($updatedFile->load(['uploadedBy', 'folder']));
    }

    public function copy(Request $request, Student $student, File $file)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'folder_id' => 'nullable|exists:folders,id',
        ]);

        $folder = $validated['folder_id'] ? Folder::find($validated['folder_id']) : null;

        $newFile = $this->storage->copyFile($file, $folder, Auth::id());

        return response()->json($newFile->load(['uploadedBy', 'folder']), 201);
    }

    public function destroy(Request $request, Student $student, File $file)
    {
        $this->authorize('update', $student);

        $permanent = $request->boolean('permanent', false);

        $this->storage->delete($file, $permanent);

        return response()->json(['message' => 'File deleted.']);
    }

    public function restore(Student $student, File $file)
    {
        $this->authorize('update', $student);

        $restoredFile = $this->storage->restore($file);

        return response()->json($restoredFile->load(['uploadedBy', 'folder']));
    }

    public function download(Student $student, File $file)
    {
        $this->authorize('view', $student);

        return $this->storage->download($file);
    }

    public function getUrl(Student $student, File $file)
    {
        $this->authorize('view', $student);

        $url = $this->storage->getUrl($file);

        return response()->json(['url' => $url]);
    }

    public function getTemporaryUrl(Request $request, Student $student, File $file)
    {
        $this->authorize('view', $student);

        $minutes = $request->get('minutes', 15);
        $expiration = now()->addMinutes($minutes);

        $url = $this->storage->getTemporaryUrl($file, $expiration);

        return response()->json([
            'url' => $url,
            'expires_at' => $expiration->toIso8601String(),
        ]);
    }

    public function createFolder(Request $request, Student $student)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'category' => 'nullable|string|max:50',
        ]);

        $folder = $this->storage->createFolder(
            $student,
            $validated['name'],
            $validated['parent_id'] ?? null,
            $validated['category'] ?? null
        );

        return response()->json($folder, 201);
    }

    public function updateFolder(Request $request, Student $student, Folder $folder)
    {
        $this->authorize('update', $student);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category' => 'nullable|string|max:50',
        ]);

        $folder->update($validated);

        return response()->json($folder->load('children'));
    }

    public function deleteFolder(Request $request, Student $student, Folder $folder)
    {
        $this->authorize('update', $student);

        $recursive = $request->boolean('recursive', true);

        // Check if folder has contents
        if ($folder->files()->count() > 0 || $folder->children()->count() > 0) {
            if (!$recursive) {
                return response()->json([
                    'message' => 'Folder is not empty. Use recursive=true to delete with contents.'
                ], 400);
            }
        }

        $this->storage->deleteFolder($folder, $recursive);

        return response()->json(['message' => 'Folder deleted.']);
    }

    public function versions(Student $student, File $file)
    {
        $this->authorize('view', $student);

        $rootFile = $file->parent_file_id ? File::find($file->parent_file_id) : $file;
        $versions = File::where('parent_file_id', $rootFile->id)
            ->orWhere('id', $rootFile->id)
            ->orderBy('version', 'desc')
            ->with(['uploadedBy', 'folder'])
            ->get();

        return response()->json($versions);
    }

    public function usage(Student $student)
    {
        $this->authorize('view', $student);

        $usage = $this->storage->getStorageUsage($student);

        return response()->json($usage);
    }

    public function search(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $validated = $request->validate([
            'query' => 'required|string|min:2',
            'category' => 'nullable|string|max:50',
        ]);

        $query = $student->files()
            ->where('is_latest', true)
            ->where(function ($q) use ($validated) {
                $q->where('original_name', 'like', "%{$validated['query']}%")
                    ->orWhere('description', 'like', "%{$validated['query']}%");
            });

        if (isset($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        $files = $query->with(['uploadedBy', 'folder'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($files);
    }

    protected function indexFileForAi(File $file): void
    {
        try {
            $this->ragService->indexFile($file);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
