<x-layouts.app title="Storage Settings">
    <x-slot:header>Settings</x-slot:header>

    <div class="max-w-4xl" x-data="storageSettings('{{ $currentDisk }}')" x-init="init()">
        {{-- Page Header --}}
        <div class="mb-6">
            <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Storage Configuration</h2>
            <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Configure where uploaded files, documents, and research data are stored</p>
        </div>

        {{-- Storage Stats Section --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Storage Overview</h2>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Current usage and storage statistics</p>
                </div>
            </div>

            <div x-show="loadingStats" x-cloak class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-8 text-center">
                <div class="flex items-center justify-center gap-3">
                    <svg class="w-5 h-5 animate-spin text-accent" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span class="text-sm text-secondary dark:text-dark-secondary">Loading storage statistics...</span>
                </div>
            </div>

            <div x-show="!loadingStats" x-cloak class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Active Storage --}}
                <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-6 border border-border dark:border-dark-border hover:border-accent/30 hover:shadow-soft transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    </div>
                    <div class="mt-4">
                        <p class="text-lg font-bold text-primary dark:text-dark-primary capitalize">{{ str_replace('_', ' ', $currentDisk) }}</p>
                        <p class="text-xs text-secondary dark:text-dark-secondary">Active Storage</p>
                    </div>
                </div>

                {{-- Total Files --}}
                <template x-if="stats.file_count !== undefined">
                    <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-6 border border-border dark:border-dark-border hover:border-info/30 hover:shadow-soft transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="mt-4">
                            <p class="text-lg font-bold text-primary dark:text-dark-primary" x-text="stats.file_count?.toLocaleString() || '0'"></p>
                            <p class="text-xs text-secondary dark:text-dark-secondary">Total Files</p>
                        </div>
                    </div>
                </template>

                {{-- Total Size --}}
                <template x-if="stats.total_size_human !== undefined">
                    <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-6 border border-border dark:border-dark-border hover:border-success/30 hover:shadow-soft transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-success/20 to-success/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div class="mt-4">
                            <p class="text-lg font-bold text-primary dark:text-dark-primary" x-text="stats.total_size_human || '0 B'"></p>
                            <p class="text-xs text-secondary dark:text-dark-secondary">Total Size</p>
                        </div>
                    </div>
                </template>

                {{-- Drive Usage --}}
                <template x-if="stats.usage !== undefined">
                    <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-6 border border-border dark:border-dark-border hover:border-warning/30 hover:shadow-soft transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div class="mt-4">
                            <p class="text-lg font-bold text-primary dark:text-dark-primary" x-text="formatBytes(stats.usage)"></p>
                            <p class="text-xs text-secondary dark:text-dark-secondary">Drive Usage</p>
                        </div>
                    </div>
                </template>

                {{-- Drive Limit --}}
                <template x-if="stats.limit !== undefined">
                    <div class="group relative bg-card dark:bg-dark-card rounded-2xl p-6 border border-border dark:border-dark-border hover:border-accent/30 hover:shadow-soft transition-all duration-300">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <div class="mt-4">
                            <p class="text-lg font-bold text-primary dark:text-dark-primary" x-text="formatBytes(stats.limit)"></p>
                            <p class="text-xs text-secondary dark:text-dark-secondary">Drive Limit</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Storage Driver Selection --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Select Storage Driver</h2>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Choose where your files will be stored</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                {{-- Local Disk --}}
                <label
                    class="group relative flex flex-col p-6 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                    :class="activeDisk === 'local' ? 'border-accent bg-accent/5 shadow-soft' : 'border-border dark:border-dark-border hover:border-accent/30 bg-card dark:bg-dark-card hover:shadow-soft'"
                >
                    <input type="radio" name="disk_selector" value="local" x-model="activeDisk" class="sr-only" @change="onDiskChange()">
                    <div class="w-14 h-14 rounded-2xl mb-4 flex items-center justify-center transition-transform duration-300"
                         :class="activeDisk === 'local' ? 'bg-gradient-to-br from-accent/20 to-accent/10 scale-110' : 'bg-surface dark:bg-dark-surface group-hover:bg-accent/10'">
                        <svg class="w-7 h-7" :class="activeDisk === 'local' ? 'text-accent' : 'text-secondary'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold" :class="activeDisk === 'local' ? 'text-accent' : 'text-primary'">Local Disk</h3>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Store files on server filesystem</p>
                    <div x-show="activeDisk === 'local'" class="absolute top-4 right-4">
                        <div class="w-6 h-6 rounded-full bg-accent flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                </label>

                {{-- DO Spaces --}}
                <label
                    class="group relative flex flex-col p-6 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                    :class="activeDisk === 'do_spaces' ? 'border-accent bg-accent/5 shadow-soft' : 'border-border dark:border-dark-border hover:border-accent/30 bg-card dark:bg-dark-card hover:shadow-soft'"
                >
                    <input type="radio" name="disk_selector" value="do_spaces" x-model="activeDisk" class="sr-only" @change="onDiskChange()">
                    <div class="w-14 h-14 rounded-2xl mb-4 flex items-center justify-center transition-transform duration-300"
                         :class="activeDisk === 'do_spaces' ? 'bg-gradient-to-br from-accent/20 to-accent/10 scale-110' : 'bg-surface dark:bg-dark-surface group-hover:bg-accent/10'">
                        <svg class="w-7 h-7" :class="activeDisk === 'do_spaces' ? 'text-accent' : 'text-secondary'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold" :class="activeDisk === 'do_spaces' ? 'text-accent' : 'text-primary'">DO Spaces</h3>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Cloud storage by DigitalOcean</p>
                    <div x-show="activeDisk === 'do_spaces'" class="absolute top-4 right-4">
                        <div class="w-6 h-6 rounded-full bg-accent flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                </label>

                {{-- Google Drive --}}
                <label
                    class="group relative flex flex-col p-6 rounded-2xl border-2 cursor-pointer transition-all duration-300"
                    :class="activeDisk === 'google_drive' ? 'border-accent bg-accent/5 shadow-soft' : 'border-border dark:border-dark-border hover:border-accent/30 bg-card dark:bg-dark-card hover:shadow-soft'"
                >
                    <input type="radio" name="disk_selector" value="google_drive" x-model="activeDisk" class="sr-only" @change="onDiskChange()">
                    <div class="w-14 h-14 rounded-2xl mb-4 flex items-center justify-center transition-transform duration-300"
                         :class="activeDisk === 'google_drive' ? 'bg-gradient-to-br from-accent/20 to-accent/10 scale-110' : 'bg-surface dark:bg-dark-surface group-hover:bg-accent/10'">
                        <svg class="w-7 h-7" :class="activeDisk === 'google_drive' ? 'text-accent' : 'text-secondary'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </div>
                    <h3 class="text-sm font-semibold" :class="activeDisk === 'google_drive' ? 'text-accent' : 'text-primary'">Google Drive</h3>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Store files in Google Drive</p>
                    <div x-show="activeDisk === 'google_drive'" class="absolute top-4 right-4">
                        <div class="w-6 h-6 rounded-full bg-accent flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Configuration Forms --}}
        <form method="POST" action="{{ route('admin.settings.storage.update') }}" class="mb-6">
            @csrf
            <input type="hidden" name="storage_disk" :value="activeDisk">

            {{-- Local disk config --}}
            <div x-show="activeDisk === 'local'" x-cloak>
                <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-8">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-primary dark:text-dark-primary">Local Disk Storage</h3>
                            <p class="text-sm text-secondary dark:text-dark-secondary mt-1">Files are stored on the server's local filesystem at <code class="bg-surface dark:bg-dark-surface px-2 py-0.5 rounded text-xs font-mono">storage/app/private</code></p>
                            <div class="mt-4 p-4 bg-warning-light/30 border border-warning-light rounded-xl">
                                <p class="text-xs text-warning">
                                    <strong>Note:</strong> Local storage is not recommended for production deployments. Consider using cloud storage for better scalability and reliability.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DO Spaces config --}}
            <div x-show="activeDisk === 'do_spaces'" x-cloak>
                <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
                    <div class="border-b border-border dark:border-dark-border px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-primary dark:text-dark-primary">DigitalOcean Spaces Configuration</h3>
                                <p class="text-xs text-secondary dark:text-dark-secondary">Enter your DigitalOcean Spaces credentials</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Access Key ID</label>
                                <input type="text" name="do_spaces_key"
                                       value="{{ old('do_spaces_key', $settings['do_spaces_key'] ?? '') }}"
                                       placeholder="Your DO Spaces access key"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Secret Access Key</label>
                                <input type="password" name="do_spaces_secret"
                                       value="{{ old('do_spaces_secret', !empty($settings['do_spaces_secret']) ? '••••••••' : '') }}"
                                       placeholder="Your DO Spaces secret"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Bucket / Space Name</label>
                                <input type="text" name="do_spaces_bucket"
                                       value="{{ old('do_spaces_bucket', $settings['do_spaces_bucket'] ?? '') }}"
                                       placeholder="my-research-files"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Region</label>
                                <select name="do_spaces_region" class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                                    @foreach(['nyc3' => 'New York 3', 'sfo3' => 'San Francisco 3', 'ams3' => 'Amsterdam 3', 'sgp1' => 'Singapore 1', 'fra1' => 'Frankfurt 1', 'blr1' => 'Bangalore 1', 'syd1' => 'Sydney 1'] as $val => $label)
                                        <option value="{{ $val }}" {{ old('do_spaces_region', $settings['do_spaces_region'] ?? 'sgp1') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">CDN Endpoint (Optional)</label>
                                <input type="text" name="do_spaces_endpoint"
                                       value="{{ old('do_spaces_endpoint', $settings['do_spaces_endpoint'] ?? '') }}"
                                       placeholder="https://myspace.sgp1.cdn.digitaloceanspaces.com"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                                <p class="text-xs text-secondary dark:text-dark-secondary mt-1.5">Leave blank to use the default Spaces endpoint</p>
                            </div>
                        </div>
                        <div class="mt-6 p-4 bg-info-light/30 border border-info-light rounded-xl flex items-start gap-3">
                            <svg class="w-5 h-5 text-info shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-primary dark:text-dark-primary">
                                <strong>Tip:</strong> Create a new Space in your DigitalOcean control panel with "Restrict File Listing" enabled for better security.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Google Drive config --}}
            <div x-show="activeDisk === 'google_drive'" x-cloak>
                <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
                    <div class="border-b border-border dark:border-dark-border px-8 py-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-primary dark:text-dark-primary">Google Drive Integration</h3>
                                <p class="text-xs text-secondary dark:text-dark-secondary">Configure OAuth 2.0 credentials for Google Drive API</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Client ID</label>
                                <input type="text" name="google_drive_client_id"
                                       value="{{ old('google_drive_client_id', $settings['google_drive_client_id'] ?? '') }}"
                                       placeholder="Your Google OAuth client ID"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Client Secret</label>
                                <input type="password" name="google_drive_client_secret"
                                       value="{{ old('google_drive_client_secret', !empty($settings['google_drive_client_secret']) ? '••••••••' : '') }}"
                                       placeholder="Your Google OAuth client secret"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Refresh Token</label>
                                <input type="text" name="google_drive_refresh_token"
                                       value="{{ old('google_drive_refresh_token', $settings['google_drive_refresh_token'] ?? '') }}"
                                       placeholder="OAuth refresh token from playground"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Folder ID (Optional)</label>
                                <input type="text" name="google_drive_folder_id"
                                       value="{{ old('google_drive_folder_id', $settings['google_drive_folder_id'] ?? '') }}"
                                       placeholder="Root folder ID for uploads"
                                       class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all">
                                <p class="text-xs text-secondary dark:text-dark-secondary mt-1.5">Specify a folder ID to organize uploads in a specific folder</p>
                            </div>
                        </div>
                        <div class="mt-6 p-4 bg-info-light/30 border border-info-light rounded-xl">
                            <p class="text-xs text-primary dark:text-dark-primary font-medium mb-3">How to get credentials:</p>
                            <ol class="text-xs text-secondary dark:text-dark-secondary space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="w-5 h-5 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold shrink-0 mt-0.5">1</span>
                                    <span>Go to <a href="https://console.cloud.google.com" target="_blank" class="text-accent hover:underline">Google Cloud Console</a> and create a project</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="w-5 h-5 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold shrink-0 mt-0.5">2</span>
                                    <span>Enable the Google Drive API</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="w-5 h-5 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold shrink-0 mt-0.5">3</span>
                                    <span>Create OAuth 2.0 credentials (Desktop app)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="w-5 h-5 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold shrink-0 mt-0.5">4</span>
                                    <span>Use <a href="https://developers.google.com/oauthplayground" target="_blank" class="text-accent hover:underline">OAuth Playground</a> to get a refresh token</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="w-5 h-5 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold shrink-0 mt-0.5">5</span>
                                    <span>Use scope: <code class="bg-surface dark:bg-dark-surface px-2 py-0.5 rounded text-xs font-mono">https://www.googleapis.com/auth/drive</code></span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border">
                {{-- Test connection button --}}
                <div x-data="testConnection()" x-show="activeDisk !== 'local'" class="flex-1">
                    <button
                        type="button"
                        @click="test()"
                        :disabled="testing"
                        class="inline-flex items-center gap-2.5 px-5 py-3 text-sm font-medium text-secondary dark:text-dark-secondary border border-border dark:border-dark-border rounded-xl hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg x-show="!testing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg x-show="testing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="testing ? 'Testing...' : 'Test Connection'"></span>
                    </button>
                    <div x-show="result" x-cloak class="mt-3">
                        <div class="flex items-center gap-2 p-3 rounded-xl" :class="success ? 'bg-success-light/30 text-success' : 'bg-danger-light/30 text-danger'">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <path x-show="!success" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs" x-text="result"></p>
                        </div>
                        <p x-show="success" class="text-xs text-secondary dark:text-dark-secondary mt-2 ml-1">
                            Tested disk: <span class="font-mono text-primary dark:text-dark-primary" x-text="testedDisk"></span>
                        </p>
                    </div>
                </div>
                <div x-show="activeDisk === 'local'" class="flex-1">
                    <p class="text-xs text-secondary dark:text-dark-secondary">Local storage does not require connection testing.</p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center gap-2.5 px-6 py-3 text-sm font-medium text-white bg-accent hover:bg-accent/90 rounded-xl transition-all shadow-soft hover:shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function storageSettings(currentDisk) {
            return {
                activeDisk: currentDisk || 'local',
                stats: {{ json_encode($storageStats ?? []) }},
                loadingStats: false,

                init() {
                    this.loadStats(currentDisk);
                },

                onDiskChange() {
                    // Clear stats when changing disk
                    this.stats = {};
                },

                async loadStats(disk) {
                    if (disk === 'local') return;

                    this.loadingStats = true;
                    try {
                        const res = await axios.get('{{ route('admin.settings.storage.stats') }}', {
                            params: { disk: disk }
                        });
                        this.stats = res.data.stats || {};
                    } catch (e) {
                        this.stats = { error: e.response?.data?.message || 'Failed to load stats' };
                    } finally {
                        this.loadingStats = false;
                    }
                },

                formatBytes(bytes) {
                    if (!bytes) return '0 B';
                    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
                    let i = 0;
                    while (bytes >= 1024 && i < units.length - 1) {
                        bytes /= 1024;
                        i++;
                    }
                    return bytes.toFixed(2) + ' ' + units[i];
                }
            }
        }

        function testConnection() {
            return {
                testing: false,
                result: '',
                success: false,
                testedDisk: '',
                async test() {
                    this.testing = true;
                    this.result = '';
                    this.testedDisk = document.querySelector('input[name="storage_disk"]').value;
                    try {
                        const res = await axios.post('{{ route('admin.settings.storage.test') }}', {
                            disk: this.testedDisk
                        });
                        this.success = res.data.success;
                        this.result = res.data.message || 'Connection successful';
                    } catch (e) {
                        this.success = false;
                        this.result = e.response?.data?.message || 'Connection failed. Check your credentials.';
                    } finally {
                        this.testing = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
