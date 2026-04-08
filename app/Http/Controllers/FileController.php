<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Student;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    public function __construct(private StorageService $storage) {}

    public function index(Student $student, Request $request)
    {
        $this->authorize('view', $student);

        $folderId = $request->get('folder');
        $currentFolder = $folderId ? Folder::findOrFail($folderId) : null;

        $folders = $student->folders()
            ->where('parent_id', $folderId)
            ->orderBy('name')
            ->get();

        $files = $student->files()
            ->where('folder_id', $folderId)
            ->where('is_latest', true)
            ->latest()
            ->paginate(20);

        $breadcrumbs = $this->buildBreadcrumbs($currentFolder);
        $folderOptions = $this->buildFolderOptions($student);

        return view('files.index', compact('student', 'folders', 'files', 'currentFolder', 'breadcrumbs', 'folderOptions'));
    }

    public function upload(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        // Detect when PHP silently discards the upload due to post_max_size being exceeded
        if (empty($_FILES) && empty($_POST) && $request->server('CONTENT_LENGTH') > 0) {
            $maxSize = ini_get('post_max_size');
            return back()->withErrors(['file' => "The uploaded file is too large. The server allows a maximum of {$maxSize}. Please contact the administrator to increase PHP upload limits."])->withInput();
        }

        $request->validate([
            'file' => 'required|file|max:51200',
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|in:thesis,manuscript,proposal,report,simulation,data,images,references,presentation,other',
        ]);

        if ($request->filled('folder_id') && !Folder::where('id', $request->folder_id)->where('student_id', $student->id)->exists()) {
            abort(422, 'Selected folder does not belong to this student.');
        }

        try {
            $file = $this->storage->upload(
                $request->file('file'),
                $student,
                Auth::id(),
                $request->folder_id,
                $request->description,
                $request->category
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()])->withInput();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()])->withInput();
        }

        return back()->with('success', "File '{$file->original_name}' uploaded.");
    }

    public function uploadVersion(Request $request, Student $student, File $file)
    {
        $this->authorize('view', $student);

        $request->validate(['file' => 'required|file|max:51200']);

        try {
            $this->storage->uploadNewVersion($request->file('file'), $file, Auth::id());
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()])->withInput();
        } catch (\RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'New version uploaded.');
    }

    public function download(Student $student, File $file)
    {
        $this->authorize('view', $student);
        return $this->storage->download($file);
    }

    public function versions(Student $student, File $file)
    {
        $this->authorize('view', $student);
        $rootFile = $file->parent_file_id ? File::find($file->parent_file_id) : $file;
        $versions = File::where('parent_file_id', $rootFile->id)
            ->orWhere('id', $rootFile->id)
            ->orderBy('version', 'desc')
            ->get();

        return view('files.versions', compact('student', 'file', 'versions'));
    }

    public function createFolder(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
            'category' => 'nullable|in:proposal,reports,thesis,simulation,data,images,references,presentations,other',
        ]);

        if ($request->filled('parent_id') && !Folder::where('id', $request->parent_id)->where('student_id', $student->id)->exists()) {
            abort(422, 'Selected parent folder does not belong to this student.');
        }

        $this->storage->createFolder(
            $student,
            $request->name,
            $request->parent_id,
            $request->category
        );

        return back()->with('success', 'Folder created.');
    }

    public function createDefaultFolders(Student $student)
    {
        $this->authorize('view', $student);

        $this->storage->createDefaultFolders($student);

        return back()->with('success', 'Default folders created successfully.');
    }

    public function deleteFolder(Request $request, Student $student, Folder $folder)
    {
        $this->authorize('view', $student);

        // Verify folder belongs to student
        if ($folder->student_id !== $student->id) {
            abort(403, 'You do not own this folder.');
        }

        $recursive = $request->boolean('recursive', true);

        // Check if folder has contents and recursive is not set
        if (($folder->files()->count() > 0 || $folder->children()->count() > 0) && !$recursive) {
            return back()->with('error', 'Folder is not empty. Please confirm to delete with contents.');
        }

        $this->storage->deleteFolder($folder, $recursive);

        return back()->with('success', 'Folder deleted.');
    }

    public function destroy(Student $student, File $file)
    {
        $this->authorize('view', $student);

        // Verify file belongs to student
        if ($file->student_id !== $student->id) {
            abort(403, 'You do not own this file.');
        }

        $this->storage->delete($file);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'File deleted.']);
        }

        return back()->with('success', 'File deleted.');
    }

    private function buildBreadcrumbs(?Folder $folder): array
    {
        $breadcrumbs = [];
        while ($folder) {
            array_unshift($breadcrumbs, $folder);
            $folder = $folder->parent;
        }
        return $breadcrumbs;
    }

    private function buildFolderOptions(Student $student): array
    {
        $folders = $student->folders()->with('parent')->orderBy('path')->get();

        return $folders->mapWithKeys(function (Folder $folder) {
            $depth = max(substr_count($folder->path, '/') - 1, 0);
            $prefix = $depth > 0 ? str_repeat('  ', $depth) . '- ' : '';

            return [$folder->id => $prefix . $folder->name];
        })->toArray();
    }
}
