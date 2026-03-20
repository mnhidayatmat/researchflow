<x-layouts.app title="Storage Settings" :header="'Settings'">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-primary">Storage Settings</h2>
                <p class="text-xs text-secondary mt-0.5">Configure file storage and backup preferences</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.settings.users') }}" class="text-xs text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a3.375 3.375 0 013 2.122V8.75h.375c.621 0 1.125.504 1.125 1.125v5.625h-5.25V8.75c0-.621.504-1.125-1.125-1.125-1.125H6.75V12.75h5.25v1.875c0 .621.504 1.125 1.125 1.125v5.625h-5.25v-5.625c0-.621.504-1.125-1.125-1.125-1.125h-3.375M4.75 12.75h13.5m-13.5 0v7.5m0-7.5h13.5"/>
                    </svg>
                    View Storage Usage
                </a>
            </div>
        </div>

        {{-- Main Grid --}}
        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Left: Configuration Form (2 columns) --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Default Storage --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8 1.79 8-4m0 0V7M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 0v10"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-semibold text-primary">Default Storage</h2>
                                <p class="text-xs text-secondary">Choose where files are stored by default</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                            @csrf
                            @method('PUT')

                            <div class="grid sm:grid-cols-3 gap-4">
                                <label class="flex items-center gap-3 p-4 rounded-xl border border-border hover:border-accent/30 hover:bg-surface transition-all cursor-pointer group">
                                    <input type="radio" name="storage_default" value="local" class="sr-only" {{ config('settings.storage.default', 'local') === 'local' ? 'checked' : '' }}>
                                    <div class="w-5 h-5 rounded-full border-2 border-secondary flex items-center justify-center group-hover:border-accent transition-colors">
                                        <div class="w-2.5 h-2.5 rounded-full {{ config('settings.storage.default', 'local') === 'local' ? 'bg-accent' : 'hidden' }}"></div>
                                    </div>
                                    <div class="w-10 h-10 rounded-lg bg-surface flex items-center justify-center">
                                        <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-primary">Local Storage</p>
                                        <p class="text-xs text-tertiary">Server filesystem</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 rounded-xl border border-border hover:border-accent/30 hover:bg-surface transition-all cursor-pointer group">
                                    <input type="radio" name="storage_default" value="do_spaces" class="sr-only" {{ config('settings.storage.default', 'local') === 'do_spaces' ? 'checked' : '' }}>
                                    <div class="w-5 h-5 rounded-full border-2 border-secondary flex items-center justify-center group-hover:border-accent transition-colors">
                                        <div class="w-2.5 h-2.5 rounded-full {{ config('settings.storage.default', 'local') === 'do_spaces' ? 'bg-accent' : 'hidden' }}"></div>
                                    </div>
                                    <div class="w-10 h-10 rounded-lg bg-surface flex items-center justify-center">
                                        <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1 0H7a5 5 0 00-.1 0H4a3 3 0 01-3-3zM3 10a4 4 0 014 4h1a5 5 0 10-.1 0H7a5 5 0 00-.1 0H4a3 3 0 01-3-3z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-primary">DigitalOcean</p>
                                        <p class="text-xs text-tertiary">Spaces S3-compatible</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-3 p-4 rounded-xl border border-border hover:border-accent/30 hover:bg-surface transition-all cursor-pointer group">
                                    <input type="radio" name="storage_default" value="google_drive" class="sr-only" {{ config('settings.storage.default', 'local') === 'google_drive' ? 'checked' : '' }}>
                                    <div class="w-5 h-5 rounded-full border-2 border-secondary flex items-center justify-center group-hover:border-accent transition-colors">
                                        <div class="w-2.5 h-2.5 rounded-full {{ config('settings.storage.default', 'local') === 'google_drive' ? 'bg-accent' : 'hidden' }}"></div>
                                    </div>
                                    <div class="w-10 h-10 rounded-lg bg-surface flex items-center justify-center">
                                        <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.364a1 1 0 01-1.445.894L15 14M5 10l4.553 2.276A1 1 0 013 8.618v6.364a1 1 0 01-1.445.894L5 14m5 5V5a5 5 0 0110 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-primary">Google Drive</p>
                                        <p class="text-xs text-tertiary">Cloud storage</p>
                                    </div>
                                </label>
                            </div>

                            <div class="mt-5 pt-5 border-t border-border flex items-center justify-end gap-3">
                                <button type="button" class="px-5 py-2.5 text-sm font-medium text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all">
                                    Cancel
                                </button>
                                <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-accent text-white hover:bg-amber-700 rounded-xl transition-all shadow-sm hover:shadow">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- File Upload Settings --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center">
                                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m0 0l4-4m0 0V8m0 0v6"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-primary">Upload Settings</h2>
                                <p class="text-sm text-secondary">Configure file upload restrictions</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-primary mb-2">Max File Size</label>
                                <div class="relative">
                                    <input type="number" name="storage_max_size" value="{{ config('settings.storage.max_size', 100) }}"
                                           class="w-full px-4 py-2.5 pr-16 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all"
                                           placeholder="100">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-tertiary">MB</span>
                                </div>
                                <p class="text-xs text-tertiary mt-1">Maximum file size per upload</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-primary mb-2">Allowed File Types</label>
                                <input type="text" value="pdf,doc,docx,zip,images"
                                       class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all bg-surface"
                                       readonly>
                                <p class="text-xs text-tertiary mt-1">Comma-separated extensions</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-surface rounded-xl">
                            <div>
                                <p class="text-sm font-medium text-primary">Enable Virus Scanning</p>
                                <p class="text-xs text-secondary">Scan uploaded files for malware</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="storage_scan" class="sr-only peer" {{ config('settings.storage.scan', false) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-secondary peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Storage Stats & Info (1 column) --}}
            <div class="space-y-6">
                {{-- Storage Usage Card --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <h2 class="text-lg font-semibold text-primary">Storage Usage</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Local Storage --}}
                        <div class="p-4 bg-surface rounded-xl">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-primary">Local Storage</p>
                                        <p class="text-xs text-tertiary">Server filesystem</p>
                                    </div>
                                </div>
                                <span class="text-xs font-medium text-accent">2.4 GB</span>
                            </div>
                            <div class="w-full h-2 bg-border-light rounded-full overflow-hidden">
                                <div class="h-full bg-info rounded-full transition-all duration-500" style="width: 24%"></div>
                            </div>
                            <p class="text-xs text-tertiary mt-1">2.4 GB of 10 GB used</p>
                        </div>

                        {{-- DO Spaces (conditional) --}}
                        @if(config('settings.storage.default') !== 'local')
                        <div class="p-4 bg-surface rounded-xl">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1 0H7a5 5 0 00-.1 0H4a3 3 0 01-3-3zM3 10a4 4 0 014 4h1a5 5 0 10-.1 0H7a5 5 0 00-.1 0H4a3 3 0 01-3-3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-primary">DigitalOcean</p>
                                        <p class="text-xs text-tertiary">Spaces bucket</p>
                                    </div>
                                </div>
                                <span class="text-xs font-medium text-accent">1.8 GB</span>
                            </div>
                            <div class="w-full h-2 bg-border-light rounded-full overflow-hidden">
                                <div class="h-full bg-accent rounded-full transition-all duration-500" style="width: 9%"></div>
                            </div>
                            <p class="text-xs text-tertiary mt-1">1.8 GB of unlimited</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Quick Actions --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-5 border-b border-border">
                        <h2 class="text-lg font-semibold text-primary">Quick Actions</h2>
                    </div>
                    <div class="p-3">
                        <button type="button" onclick="testStorageConnection()" class="w-full flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-success/10 flex items-center justify-center group-hover:bg-success/20 transition-colors">
                                <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 text-left">
                                <p class="text-sm font-medium">Test Connection</p>
                                <p class="text-xs text-tertiary">Verify storage access</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <a href="{{ route('admin.settings.ai') }}" class="w-full flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-info/10 flex items-center justify-center group-hover:bg-info/20 transition-colors">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 text-left">
                                <p class="text-sm font-medium">AI Settings</p>
                                <p class="text-xs text-tertiary">Configure AI provider</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ route('admin.settings.users') }}" class="w-full flex items-center gap-3 p-3 rounded-xl text-secondary hover:bg-surface hover:text-primary transition-all group">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center group-hover:bg-accent/20 transition-colors">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 text-left">
                                <p class="text-sm font-medium">Manage Users</p>
                                <p class="text-xs text-tertiary">User permissions</p>
                            </div>
                            <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Info Alert --}}
                <div class="bg-info/10 border border-info/20 rounded-2xl p-5">
                    <div class="flex gap-3">
                        <div class="w-10 h-10 rounded-xl bg-info/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-primary">Storage Tips</p>
                            <p class="text-sm text-secondary mt-1">For production, consider using DigitalOcean Spaces or Google Drive for reliable cloud storage with automatic backups.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Storage Providers Configuration (conditional) --}}
        @if(config('settings.storage.default') !== 'local')
        <div class="bg-card rounded-2xl border border-border overflow-hidden">
            <div class="px-6 py-5 border-b border-border">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a3.375 3.375 0 013 2.122V8.75h.375c.621 0 1.125.504 1.125 1.125v5.625h-5.25V8.75c0-.621.504-1.125-1.125-1.125-1.125H6.75V12.75h5.25v1.875c0 .621.504 1.125 1.125 1.125v5.625h-5.25v-5.625c0-.621.504-1.125-1.125-1.125-1.125h-3.375M4.75 12.75h13.5m-13.5 0v7.5m0-7.5h13.5"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-primary">Cloud Storage Configuration</h2>
                        <p class="text-sm text-secondary">Configure your cloud storage provider credentials</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- DO Spaces Form -->
                @if(config('settings.storage.default') === 'do_spaces')
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">Spaces Key</label>
                            <input type="text" name="do_spaces_key"
                                   value="{{ config('settings.storage.do_spaces.key', '') }}"
                                   class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all"
                                   placeholder="SPACES_KEY">
                            <p class="text-xs text-tertiary mt-1">Your DigitalOcean Spaces access key</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">Spaces Secret</label>
                            <input type="password" name="do_spaces_secret"
                                   value="{{ config('settings.storage.do_spaces.secret', '') }}"
                                   class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all"
                                   placeholder="••••••••••">
                            <p class="text-xs text-tertiary mt-1">Your DigitalOcean Spaces secret key</p>
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">Bucket Name</label>
                            <input type="text" name="do_spaces_bucket"
                                   value="{{ config('settings.storage.do_spaces.bucket', '') }}"
                                   class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all"
                                   placeholder="researchflow-files">
                            <p class="text-xs text-tertiary mt-1">Name of your Spaces bucket</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-primary mb-2">Region</label>
                            <select name="do_spaces_region" class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                <option value="nyc3">New York 3</option>
                                <option value="sfo3">San Francisco 3</option>
                                <option value="ams3">Amsterdam 3</option>
                                <option value="sgp1">Singapore 1</option>
                                <option value="fra1">Frankfurt 1</option>
                                <option value="blr1">Bangalore 1</option>
                            </select>
                            <p class="text-xs text-tertiary mt-1">Choose the region closest to your users</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" class="px-5 py-2.5 text-sm font-medium text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-accent text-white hover:bg-amber-700 rounded-xl transition-all shadow-sm hover:shadow">
                            Save Configuration
                        </button>
                    </div>
                </form>
                @endif

                <!-- Google Drive Form -->
                @if(config('settings.storage.default') === 'google_drive')
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-primary mb-2">Client ID</label>
                        <input type="text" name="google_drive_client_id"
                               value="{{ config('settings.storage.google_drive.client_id', '') }}"
                               class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all"
                               placeholder="Your OAuth Client ID">
                        <p class="text-xs text-tertiary mt-1">From your Google Cloud Console project</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-primary mb-2">Client Secret</label>
                        <input type="password" name="google_drive_client_secret"
                               value="{{ config('settings.storage.google_drive.client_secret', '') }}"
                               class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all"
                               placeholder="Your OAuth Client Secret">
                        <p class="text-xs text-tertiary mt-1">From your Google Cloud Console project</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-primary mb-2">Refresh Token</label>
                        <input type="text" name="google_drive_refresh_token"
                               value="{{ config('settings.storage.google_drive.refresh_token', '') }}"
                               class="w-full px-4 py-2.5 text-sm border border-border rounded-xl focus:outline-none focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all"
                               placeholder="Your OAuth refresh token">
                        <p class="text-xs text-tertiary mt-1">Obtained from OAuth Playground</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" class="px-5 py-2.5 text-sm font-medium text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all">
                            Cancel
                        </button>
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold bg-accent text-white hover:bg-amber-700 rounded-xl transition-all shadow-sm hover:shadow">
                            Save Configuration
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        function testStorageConnection() {
            fetch('{{ route('admin.settings.storage.test') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Storage connection successful!');
                } else {
                    alert('Storage connection failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error testing connection: ' + error.message);
            });
        }
    </script>
</x-layouts.app>