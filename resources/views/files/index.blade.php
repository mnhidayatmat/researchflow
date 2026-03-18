<x-layouts.app title="Research Vault">
    <x-slot:header>Research Vault</x-slot:header>

    <div class="flex items-center justify-between mb-6">
        {{-- Breadcrumbs --}}
        <nav class="flex items-center gap-1 text-sm">
            <a href="{{ route('files.index', $student) }}" class="text-secondary hover:text-accent">Root</a>
            @foreach($breadcrumbs as $crumb)
                <span class="text-secondary">/</span>
                <a href="{{ route('files.index', [$student, 'folder' => $crumb->id]) }}" class="text-secondary hover:text-accent">{{ $crumb->name }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <button @click="$dispatch('open-modal-new-folder')" class="text-xs text-secondary hover:text-accent px-3 py-1.5 border border-border rounded-lg hover:bg-gray-50">New Folder</button>
            <button @click="$dispatch('open-modal-upload')" class="text-xs text-white bg-accent hover:bg-amber-600 px-3 py-1.5 rounded-lg">Upload File</button>
        </div>
    </div>

    {{-- Folders --}}
    @if($folders->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
            @foreach($folders as $folder)
                <a href="{{ route('files.index', [$student, 'folder' => $folder->id]) }}" class="bg-white border border-border rounded-lg p-3 hover:shadow-sm hover:border-accent/30 transition-all group">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent/70 group-hover:text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        <span class="text-sm font-medium truncate">{{ $folder->name }}</span>
                    </div>
                    @if($folder->category)
                        <p class="text-[10px] text-secondary mt-1">{{ ucfirst($folder->category) }}</p>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    {{-- Files --}}
    <x-card :padding='false'>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-medium text-secondary uppercase tracking-wider border-b border-border">
                    <th class="px-5 py-3">Name</th>
                    <th class="px-5 py-3">Size</th>
                    <th class="px-5 py-3">Version</th>
                    <th class="px-5 py-3">Uploaded</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($files as $file)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span class="font-medium">{{ $file->original_name }}</span>
                            </div>
                            @if($file->description)
                                <p class="text-xs text-secondary ml-6">{{ $file->description }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-secondary">{{ $file->sizeForHumans() }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('files.versions', [$student, $file]) }}" class="text-xs text-accent hover:underline">v{{ $file->version }}</a>
                        </td>
                        <td class="px-5 py-3 text-secondary text-xs">{{ $file->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('files.download', [$student, $file]) }}" class="text-xs text-accent hover:underline">Download</a>
                                <form method="POST" action="{{ route('files.destroy', [$student, $file]) }}" onsubmit="return confirm('Delete this file?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-600">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-secondary text-sm">No files in this folder.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $files->links() }}</div>

    {{-- Upload Modal --}}
    <x-modal name="upload" title="Upload File">
        <form method="POST" action="{{ route('files.upload', $student) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
            <div>
                <label class="block text-sm font-medium text-primary mb-1">File</label>
                <input type="file" name="file" required class="w-full text-sm text-secondary file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-accent/10 file:text-accent hover:file:bg-accent/20">
            </div>
            <x-input name="description" label="Description (optional)" placeholder="Brief description of this file" />
            <x-button type="submit" variant="accent" class="w-full">Upload</x-button>
        </form>
    </x-modal>

    {{-- New Folder Modal --}}
    <x-modal name="new-folder" title="Create Folder">
        <form method="POST" action="{{ route('files.create-folder', $student) }}" class="space-y-4">
            @csrf
            <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
            <x-input name="name" label="Folder Name" required placeholder="e.g. Chapter 1" />
            <x-select name="category" label="Category" :options="['proposal' => 'Proposal', 'reports' => 'Reports', 'thesis' => 'Thesis', 'simulation' => 'Simulation', 'data' => 'Data', 'images' => 'Images', 'references' => 'References', 'other' => 'Other']" />
            <x-button type="submit" variant="accent" class="w-full">Create Folder</x-button>
        </form>
    </x-modal>
</x-layouts.app>
