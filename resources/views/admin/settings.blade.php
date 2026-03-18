{{-- Admin Settings Template --}}
<x-layouts.app title="Admin Settings" :header="'Settings'">

    {{-- Settings tabs --}}
    <x-tabs :tabs="[
        'general' => 'General',
        'storage' => ['label' => 'Storage', 'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4'],
        'ai' => ['label' => 'AI Providers', 'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z'],
        'users' => ['label' => 'Users', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
    ]" :active="$activeTab ?? 'general'" variant="underline" />

    <div class="mt-6 grid lg:grid-cols-3 gap-6">
        {{-- Settings form --}}
        <div class="lg:col-span-2">
            <x-card>
                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- Storage settings --}}
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-sm font-semibold text-primary mb-4">Storage Configuration</h3>
                            <div class="space-y-4">
                                <x-select
                                    label="Default Storage Driver"
                                    name="storage_default"
                                    :options="[
                                        'local' => 'Local Storage',
                                        's3' => 'Amazon S3',
                                        'dropbox' => 'Dropbox',
                                    ]"
                                    :value="config('settings.storage.default', 'local')"
                                />

                                <x-input-group
                                    label="Max File Size (MB)"
                                    name="storage_max_size"
                                    type="number"
                                    :value="config('settings.storage.max_size', 100)"
                                    hint="Maximum allowed file upload size"
                                />

                                <div class="flex items-center justify-between p-4 bg-surface rounded-lg">
                                    <div>
                                        <p class="text-sm font-medium text-primary">Auto-backup enabled</p>
                                        <p class="text-xs text-secondary">Automatically backup files to secondary storage</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="storage_backup" class="sr-only peer" {{ config('settings.storage.backup', true) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-secondary peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="border-border">

                        {{-- AI settings --}}
                        <div>
                            <h3 class="text-sm font-semibold text-primary mb-4">AI Configuration</h3>
                            <div class="space-y-4">
                                <x-select
                                    label="AI Provider"
                                    name="ai_provider"
                                    :options="[
                                        'openai' => 'OpenAI',
                                        'anthropic' => 'Anthropic',
                                        'cohere' => 'Cohere',
                                    ]"
                                    :value="config('settings.ai.provider', 'openai')"
                                />

                                <x-input-group
                                    label="API Key"
                                    name="ai_api_key"
                                    type="password"
                                    :value="config('settings.ai.api_key', '')"
                                    placeholder="sk-..."
                                    hint="Your AI provider API key"
                                />

                                <x-input-group
                                    label="Model"
                                    name="ai_model"
                                    :value="config('settings.ai.model', 'gpt-4')"
                                    placeholder="gpt-4"
                                    hint="The AI model to use for chat completions"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-border">
                        <button type="button" class="px-4 py-2 text-sm font-medium text-secondary hover:text-primary">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-accent hover:bg-amber-700 rounded-lg transition-colors">Save Changes</button>
                    </div>
                </form>
            </x-card>
        </div>

        {{-- Sidebar info --}}
        <div class="space-y-6">
            <x-alert variant="info" :dismissible="false">
                <strong>Heads up!</strong> Changes to storage settings may affect existing files.
            </x-alert>

            <x-card title="Storage Usage">
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-secondary">Local Storage</span>
                            <span class="text-xs font-medium text-primary">2.4 GB / 10 GB</span>
                        </div>
                        <x-progress :value="24" size="sm" variant="default" />
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-secondary">S3 Backup</span>
                            <span class="text-xs font-medium text-primary">1.8 GB / Unlimited</span>
                        </div>
                        <x-progress :value="10" size="sm" variant="success" />
                    </div>
                </div>
            </x-card>

            <x-card title="Quick Links">
                <div class="space-y-1">
                    <a href="{{ route('admin.settings.users') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Manage Users
                    </a>
                    <a href="{{ route('admin.programmes.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Programmes
                    </a>
                    <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-2 px-3 py-2 text-sm text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        Journey Templates
                    </a>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.app>
