<x-layouts.app title="Research Vault">
    <x-slot:header>Research Vault</x-slot:header>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        {{-- Breadcrumbs --}}
        <nav class="flex items-center gap-1 text-sm">
            <a href="{{ route('files.index', $student) }}" class="text-secondary hover:text-accent">Root</a>
            @foreach($breadcrumbs as $crumb)
                <span class="text-secondary">/</span>
                <a href="{{ route('files.index', [$student, 'folder' => $crumb->id]) }}" class="text-secondary hover:text-accent">{{ $crumb->name }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <button @click="$dispatch('open-modal-create-default-folders')" class="text-xs text-secondary hover:text-accent px-3 py-1.5 border border-border rounded-lg hover:bg-surface transition-all">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Default Folders
                </span>
            </button>
            <button @click="$dispatch('open-modal-new-folder')" class="text-xs text-secondary hover:text-accent px-3 py-1.5 border border-border rounded-lg hover:bg-surface transition-all">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    New Folder
                </span>
            </button>
            <button @click="$dispatch('open-modal-upload')" class="text-xs text-white bg-accent hover:bg-amber-700 px-3 py-1.5 rounded-lg transition-all shadow-sm hover:shadow">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Upload File
                </span>
            </button>
        </div>
    </div>

    {{-- Folders --}}
    @if($folders->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3 mb-6">
            @foreach($folders as $folder)
                <div class="group relative bg-card border border-border rounded-xl p-4 hover:border-accent/30 hover:shadow-soft transition-all">
                    <a href="{{ route('files.index', [$student, 'folder' => $folder->id]) }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-primary truncate">{{ $folder->name }}</p>
                            @if($folder->category)
                                <p class="text-[10px] text-secondary mt-0.5">{{ ucfirst($folder->category) }}</p>
                            @endif
                        </div>
                    </a>
                    {{-- Delete folder button --}}
                    <form method="POST" action="{{ route('folders.delete', [$student, $folder]) }}" onsubmit="return confirm('Delete this folder and all its contents?')" class="absolute top-2 right-2">
                        @csrf @method('DELETE')
                        <button type="submit" class="opacity-0 group-hover:opacity-100 p-1.5 text-secondary hover:text-danger rounded-lg hover:bg-danger/10 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
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
                    <tr class="hover:bg-surface/50 transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-primary">{{ $file->original_name }}</p>
                                    @if($file->description)
                                        <p class="text-xs text-secondary">{{ $file->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-secondary">{{ $file->sizeForHumans() }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('files.versions', [$student, $file]) }}" class="text-xs text-accent hover:underline">v{{ $file->version }}</a>
                        </td>
                        <td class="px-5 py-3 text-secondary text-xs">{{ $file->created_at->diffForHumans() }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('files.download', [$student, $file]) }}" class="text-xs text-accent hover:text-amber-700 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download
                                </a>
                                <form method="POST" action="{{ route('files.destroy', [$student, $file]) }}" onsubmit="return confirm('Delete this file?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-danger hover:text-red-700 font-medium flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-tertiary/10 to-tertiary/5 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <p class="text-sm text-secondary">No files in this folder</p>
                                <button @click="$dispatch('open-modal-upload')" class="mt-4 text-xs text-accent hover:text-amber-700 font-medium">Upload your first file</button>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>

    <div class="mt-4">{{ $files->links() }}</div>

    {{-- Upload Modal --}}
    <x-modal name="upload" title="Upload File" :show="$errors->has('file') || $errors->has('folder_id') || $errors->has('description') || $errors->has('category')">
        <form method="POST" action="{{ route('files.upload', $student) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-select
                name="folder_id"
                label="Save To Folder"
                :options="$folderOptions"
                :value="old('folder_id', $currentFolder?->id)"
                placeholder="Root"
                hint="Choose a folder, or leave as Root to store at the top level."
            />
            <div>
                <label class="block text-sm font-medium text-primary mb-1.5">File</label>
                <input type="file" name="file" required class="w-full text-sm text-secondary file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-accent/10 file:text-accent hover:file:bg-accent/20">
                @error('file')
                    <p class="mt-2 text-xs text-danger">{{ $message }}</p>
                @enderror
            </div>
            <x-select
                name="category"
                label="Category"
                :options="['thesis' => 'Thesis', 'manuscript' => 'Manuscript', 'proposal' => 'Proposal', 'report' => 'Report', 'simulation' => 'Simulation', 'data' => 'Data', 'images' => 'Images', 'references' => 'References', 'presentation' => 'Presentation', 'other' => 'Other']"
                :value="old('category')"
                placeholder="Select category"
            />
            <x-input name="description" label="Description (optional)" placeholder="Brief description of this file" />
            <x-button type="submit" variant="accent" class="w-full">Upload File</x-button>
        </form>
    </x-modal>

    {{-- New Folder Modal --}}
    <x-modal name="new-folder" title="Create Folder" :show="$errors->has('name') || $errors->has('parent_id')">
        <form method="POST" action="{{ route('files.create-folder', $student) }}" class="space-y-4">
            @csrf
            <x-select
                name="parent_id"
                label="Parent Folder"
                :options="$folderOptions"
                :value="old('parent_id', $currentFolder?->id)"
                placeholder="Root"
                hint="Choose where this new folder should be created."
            />
            <x-input name="name" label="Folder Name" required placeholder="e.g. Chapter 1" />
            <x-select name="category" label="Category" :options="['proposal' => 'Proposal', 'reports' => 'Reports', 'thesis' => 'Thesis', 'simulation' => 'Simulation', 'data' => 'Data', 'images' => 'Images', 'references' => 'References', 'presentations' => 'Presentations', 'other' => 'Other']" :value="old('category')" />
            <x-button type="submit" variant="accent" class="w-full">Create Folder</x-button>
        </form>
    </x-modal>

    {{-- Create Default Folders Modal --}}
    <x-modal name="create-default-folders" title="Create Default Folders" subtitle="This will create standard folders for organizing your research files">
        <form method="POST" action="{{ route('files.create-default-folders', $student) }}" class="space-y-4">
            @csrf
            <div class="bg-surface rounded-xl p-4">
                <p class="text-sm text-secondary mb-3">The following folders will be created:</p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Proposals
                    </div>
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Progress Reports
                    </div>
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Thesis Drafts
                    </div>
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Simulations
                    </div>
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Data & Results
                    </div>
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Images & Figures
                    </div>
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        References
                    </div>
                    <div class="flex items-center gap-2 text-primary">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Meeting Notes
                    </div>
                    <div class="flex items-center gap-2 text-primary col-span-2">
                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                        Presentations
                    </div>
                </div>
            </div>
            <x-button type="submit" variant="accent" class="w-full">Create Default Folders</x-button>
        </form>
    </x-modal>
</x-layouts.app>
