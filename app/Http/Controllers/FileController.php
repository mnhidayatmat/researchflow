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

        return view('files.index', compact('student', 'folders', 'files', 'currentFolder', 'breadcrumbs'));
    }

    public function upload(Request $request, Student $student)
    {
        $this->authorize('view', $student);

        $request->validate([
            'file' => 'required|file|max:51200',
            'folder_id' => 'nullable|exists:folders,id',
            'description' => 'nullable|string|max:500',
        ]);

        $file = $this->storage->upload(
            $request->file('file'),
            $student,
            Auth::id(),
            $request->folder_id,
            $request->description
        );

        return back()->with('success', "File '{$file->original_name}' uploaded.");
    }

    public function uploadVersion(Request $request, Student $student, File $file)
    {
        $this->authorize('view', $student);

        $request->validate(['file' => 'required|file|max:51200']);

        $this->storage->uploadNewVersion($request->file('file'), $file, Auth::id());

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
            'category' => 'nullable|in:proposal,reports,thesis,simulation,data,images,references,other',
        ]);

        $this->storage->createFolder(
            $student,
            $request->name,
            $request->parent_id,
            $request->category
        );

        return back()->with('success', 'Folder created.');
    }

    public function destroy(Student $student, File $file)
    {
        $this->authorize('view', $student);
        $this->storage->delete($file);
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
}
