<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Grant;
use App\Models\GrantDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GrantDocumentController extends Controller
{
    public function store(Request $request, Grant $grant): RedirectResponse
    {
        abort_unless($grant->user_id === Auth::id(), 403);

        $request->validate([
            'document' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip'],
        ]);

        $file = $request->file('document');
        $path = $file->store("grants/{$grant->id}", 'local');

        $grant->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'size'          => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function destroy(Grant $grant, GrantDocument $document): RedirectResponse
    {
        abort_unless($grant->user_id === Auth::id(), 403);
        abort_unless($document->grant_id === $grant->id, 404);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document deleted.');
    }

    public function download(Grant $grant, GrantDocument $document): StreamedResponse
    {
        abort_unless($grant->user_id === Auth::id(), 403);
        abort_unless($document->grant_id === $grant->id, 404);

        return Storage::disk('local')->download($document->path, $document->original_name);
    }
}
