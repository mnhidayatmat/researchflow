<x-layouts.app title="Storage Authentication">
    <x-slot:header>Storage Authentication</x-slot:header>

    @php
        $updateRoute = $role === 'admin' ? route('admin.settings.storage.update') : route('supervisor.storage.update');
        $testRoute = $role === 'admin' ? route('admin.settings.storage.test') : route('supervisor.storage.test');
    @endphp

    <div class="max-w-4xl space-y-6" x-data="storageAuth()">
        @if ($errors->has('google_drive'))
            <div class="rounded-xl border border-danger/20 bg-danger-light px-4 py-3 text-sm text-danger">
                {{ $errors->first('google_drive') }}
            </div>
        @endif

        <div class="space-y-2">
            <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Personal Storage Profile</h2>
            <p class="text-xs text-secondary dark:text-dark-secondary">
                Choose where report attachments are stored. Google Drive now uses a normal sign-in and consent flow instead of manual OAuth token setup.
            </p>
        </div>

        <x-card class="space-y-6">
            <div class="rounded-2xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Google Drive Connection</h3>
                            @if ($isGoogleConnected)
                                <span class="rounded-full bg-success-light px-2.5 py-1 text-[11px] font-medium text-success">Connected</span>
                            @else
                                <span class="rounded-full bg-warning-light px-2.5 py-1 text-[11px] font-medium text-warning">Not Connected</span>
                            @endif
                        </div>

                        @if ($hasGoogleOAuthConfig)
                            <p class="text-xs text-secondary dark:text-dark-secondary">
                                Sign in with your Google account and authorize access. The app stores only your refresh token and optional folder ID.
                            </p>
                        @else
                            <p class="text-xs text-danger">
                                Google Drive OAuth is not configured yet. Add `GOOGLE_DRIVE_CLIENT_ID` and `GOOGLE_DRIVE_CLIENT_SECRET` in the application environment first.
                            </p>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        @if ($hasGoogleOAuthConfig)
                            @if ($isGoogleConnected)
                                <form method="POST" action="{{ route('storage.google.disconnect') }}">
                                    @csrf
                                    <button type="submit" class="rounded-xl border border-border dark:border-dark-border px-4 py-2.5 text-sm text-secondary dark:text-dark-secondary transition-all hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface hover:text-primary dark:hover:text-dark-primary dark:text-dark-primary">
                                        Disconnect Google
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('storage.google.connect') }}" class="inline-flex items-center rounded-xl bg-accent px-4 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-90">
                                    Connect Google Drive
                                </a>
                            @endif
                        @endif

                        <button type="button"
                                @click="testConnection('{{ $testRoute }}')"
                                class="rounded-xl border border-border dark:border-dark-border px-4 py-2.5 text-sm text-secondary dark:text-dark-secondary transition-all hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface hover:text-primary dark:hover:text-dark-primary dark:text-dark-primary">
                            Test Connection
                        </button>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ $updateRoute }}" class="space-y-5">
                @csrf

                <div>
                    <label class="mb-2 block text-xs font-medium text-secondary dark:text-dark-secondary">Storage Destination</label>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-border dark:border-dark-border p-4">
                            <input type="radio" name="storage_disk" value="local" class="mt-1" {{ old('storage_disk', $profile->storage_disk) === 'local' ? 'checked' : '' }}>
                            <div>
                                <p class="text-sm font-medium text-primary dark:text-dark-primary">Local</p>
                                <p class="mt-1 text-xs text-secondary dark:text-dark-secondary">Store report attachments on the application server.</p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-border dark:border-dark-border p-4">
                            <input type="radio" name="storage_disk" value="google_drive" class="mt-1" {{ old('storage_disk', $profile->storage_disk) === 'google_drive' ? 'checked' : '' }} {{ !$isGoogleConnected ? 'disabled' : '' }}>
                            <div>
                                <p class="text-sm font-medium text-primary dark:text-dark-primary">Google Drive</p>
                                <p class="mt-1 text-xs text-secondary dark:text-dark-secondary">Store report attachments in your own Google Drive space after account authorization.</p>
                            </div>
                        </label>
                    </div>
                    @error('storage_disk')
                        <p class="mt-2 text-xs text-danger">{{ $message }}</p>
                    @enderror
                    @if (!$isGoogleConnected)
                        <p class="mt-2 text-xs text-secondary dark:text-dark-secondary">Google Drive becomes selectable after you connect your account.</p>
                    @endif
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium text-secondary dark:text-dark-secondary">Root Folder ID</label>
                    <input type="text"
                           name="google_drive_folder_id"
                           value="{{ old('google_drive_folder_id', $profile->google_drive_folder_id) }}"
                           placeholder="Optional: defaults to Drive root"
                           class="w-full rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-sm text-primary dark:text-dark-primary">
                    <p class="mt-2 text-xs text-secondary dark:text-dark-secondary">
                        Optional. Leave blank to store files under your Google Drive root. If provided, ResearchFlow creates its own subfolders inside that folder.
                    </p>
                    <div class="mt-3 rounded-2xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface px-4 py-3 text-xs text-secondary dark:text-dark-secondary space-y-2">
                        <p class="font-medium text-primary dark:text-dark-primary">How to find your Root Folder ID</p>
                        <p>1. Open Google Drive and create or open the folder you want to use.</p>
                        <p>2. Look at the browser URL. Example:</p>
                        <p class="rounded-lg bg-white dark:bg-dark-card px-3 py-2 font-mono text-[11px] text-primary dark:text-dark-primary break-all">
                            https://drive.google.com/drive/folders/1AbCdEfGhIJkLmNoPqRsTuVwXyZ123456
                        </p>
                        <p>3. Copy only the last part after `/folders/`:</p>
                        <p class="rounded-lg bg-white dark:bg-dark-card px-3 py-2 font-mono text-[11px] text-primary dark:text-dark-primary break-all">
                            1AbCdEfGhIJkLmNoPqRsTuVwXyZ123456
                        </p>
                        <p>4. Paste that value into this field. Do not paste the full Google Drive URL.</p>
                        <p>If left blank, uploads will be stored directly under your Google Drive root.</p>
                    </div>
                    @error('google_drive_folder_id')
                        <p class="mt-2 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-2xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface p-4 text-xs text-secondary dark:text-dark-secondary space-y-2">
                    <p class="font-medium text-primary dark:text-dark-primary">How it works</p>
                    <p>1. Click `Connect Google Drive` and sign in with your Google account.</p>
                    <p>2. Grant Drive access to the app.</p>
                    <p>3. Choose `Google Drive` as your storage destination.</p>
                    <p>4. Uploads will be organized automatically by supervisor and student.</p>
                </div>

                <div class="flex justify-end">
                    <x-button type="submit" variant="accent">Save Storage Preferences</x-button>
                </div>
            </form>

            <div x-show="message" x-cloak class="rounded-xl px-4 py-3 text-sm" :class="success ? 'bg-success-light text-success' : 'bg-danger-light text-danger'">
                <span x-text="message"></span>
            </div>
        </x-card>
    </div>

    @push('scripts')
    <script>
        function storageAuth() {
            return {
                message: '',
                success: false,
                async testConnection(url) {
                    this.message = '';
                    try {
                        const response = await axios.post(url);
                        this.success = true;
                        this.message = response.data.message;
                    } catch (error) {
                        this.success = false;
                        this.message = error.response?.data?.message || 'Connection test failed.';
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
