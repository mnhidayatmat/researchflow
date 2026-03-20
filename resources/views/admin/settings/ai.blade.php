<x-layouts.app title="AI Settings">
    <x-slot:header>Settings</x-slot:header>

    <div class="max-w-3xl" x-data="aiSettingsData" x-init="init()">
        {{-- Page header --}}
        <div class="mb-6">
            <h2 class="text-base font-semibold text-primary">AI Provider Configuration</h2>
            <p class="text-xs text-secondary mt-0.5">Configure AI providers for automated feedback and suggestions</p>
        </div>

        {{-- Info card --}}
        <div class="mb-6 p-4 bg-info-light/30 border border-info-light rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-info shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-xs text-primary">
                <p class="font-medium mb-1">Get your API keys:</p>
                <ul class="space-y-0.5 text-secondary">
                    <li>• <strong>OpenAI:</strong> <a href="https://platform.openai.com/api-keys" target="_blank" class="text-info hover:underline">platform.openai.com/api-keys</a></li>
                    <li>• <strong>Google:</strong> <a href="https://console.cloud.google.com/" target="_blank" class="text-info hover:underline">console.cloud.google.com</a></li>
                    <li>• <strong>Anthropic:</strong> <a href="https://console.anthropic.com/" target="_blank" class="text-info hover:underline">console.anthropic.com</a></li>
                </ul>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.ai.update') }}">
            @csrf

            {{-- Providers list --}}
            <div class="space-y-4 mb-6">
                <template x-for="(provider, index) in providers" :key="provider.id">
                    <div class="bg-card rounded-2xl border border-border overflow-hidden" :class="!provider.is_active && 'opacity-60'">
                        {{-- Provider header --}}
                        <div class="flex items-center justify-between px-6 py-4 border-b border-border bg-surface/50">
                            <div class="flex items-center gap-3">
                                {{-- Active toggle --}}
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :name="`providers[${index}][is_active]`"
                                        value="1"
                                        x-model="provider.is_active"
                                        class="sr-only peer"
                                    >
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-accent/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                </label>
                                <div>
                                    <span class="text-sm font-medium text-primary" x-text="provider.name || provider.slug || 'New Provider'"></span>
                                    <span class="ml-2 text-xs text-secondary" x-show="provider.is_default">(Default)</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="setDefault(index)"
                                    x-show="!provider.is_default"
                                    class="px-3 py-1.5 text-xs text-secondary border border-border rounded-lg hover:bg-surface transition-colors"
                                >
                                    Set Default
                                </button>
                                <button
                                    type="button"
                                    @click="toggleExpand(index)"
                                    class="p-2 text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4 transition-transform" :class="provider.expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button
                                    type="button"
                                    @click="removeProvider(index)"
                                    x-show="providers.length > 1"
                                    class="p-2 text-secondary hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Provider config (collapsible) --}}
                        <div x-show="provider.expanded" x-cloak class="p-6">
                            <input type="hidden" :name="`providers[${index}][id]`" :value="provider.id">
                            <input type="hidden" :name="`providers[${index}][is_default]`" :value="provider.is_default ? 1 : 0">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                {{-- Provider type/slug --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Provider Type</label>
                                    <select
                                        :name="`providers[${index}][slug]`"
                                        x-model="provider.slug"
                                        @change="setProviderDefaults(provider)"
                                        class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm text-primary focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                        <option value="openai">OpenAI</option>
                                        <option value="gemini">Google Gemini</option>
                                        <option value="anthropic">Anthropic Claude</option>
                                        <option value="zai">Z.Ai</option>
                                        <option value="custom">Custom / Self-hosted</option>
                                    </select>
                                </div>

                                {{-- Name --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Display Name</label>
                                    <input
                                        type="text"
                                        :name="`providers[${index}][name]`"
                                        x-model="provider.name"
                                        placeholder="e.g. GPT-4o Mini"
                                        class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- API Key --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-secondary mb-1.5">
                                        API Key <span class="text-red-400">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        :name="`providers[${index}][api_key]`"
                                        x-model="provider.api_key"
                                        :placeholder="provider.has_key ? '••••••••••••  (saved)' : 'Enter API key'"
                                        class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Model --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Model</label>
                                    <input
                                        type="text"
                                        :name="`providers[${index}][model]`"
                                        x-model="provider.model"
                                        :placeholder="modelPlaceholder(provider.slug)"
                                        class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Temperature --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">
                                        Temperature <span class="text-secondary font-normal ml-1" x-text="`(${provider.temperature ?? 0.7})`"></span>
                                    </label>
                                    <input
                                        type="range"
                                        :name="`providers[${index}][temperature]`"
                                        x-model="provider.temperature"
                                        min="0" max="1" step="0.1"
                                        class="w-full accent-amber-500"
                                    >
                                    <div class="flex justify-between text-[10px] text-secondary mt-1">
                                        <span>Precise</span>
                                        <span>Creative</span>
                                    </div>
                                </div>

                                {{-- Custom endpoint (for custom providers) --}}
                                <div class="sm:col-span-2" x-show="provider.slug === 'custom'">
                                    <label class="block text-xs font-medium text-secondary mb-1.5">API Endpoint URL</label>
                                    <input
                                        type="url"
                                        :name="`providers[${index}][base_url]`"
                                        x-model="provider.base_url"
                                        placeholder="https://api.yourprovider.com/v1/chat/completions"
                                        class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Max tokens --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Max Tokens</label>
                                    <input
                                        type="number"
                                        :name="`providers[${index}][max_tokens]`"
                                        x-model="provider.max_tokens"
                                        min="100"
                                        max="128000"
                                        placeholder="e.g. 4096"
                                        class="w-full rounded-xl border border-border bg-white px-4 py-2.5 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Features --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-secondary mb-2">Enable For</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach(['feedback' => 'AI Feedback', 'summary' => 'Report Summary', 'suggestions' => 'Task Suggestions'] as $feat => $featlabel)
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-border hover:bg-surface cursor-pointer transition-colors">
                                                <input
                                                    type="checkbox"
                                                    :name="`providers[${index}][features][{{ $feat }}]`"
                                                    value="1"
                                                    :checked="provider.features?.{{ $feat }}"
                                                    class="rounded border-border text-accent focus:ring-accent/30"
                                                >
                                                <span class="text-xs text-primary">{{ $featlabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Add provider button --}}
            <button
                type="button"
                @click.prevent="addProvider()"
                class="w-full flex items-center justify-center gap-2 py-3 text-sm text-secondary hover:text-accent hover:bg-surface/50 border border-dashed border-border hover:border-accent rounded-xl transition-all cursor-pointer mb-6"
                style="position: relative; z-index: 10;"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Another Provider
            </button>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 text-sm font-medium text-secondary hover:text-primary border border-border rounded-xl transition-all">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-accent hover:bg-amber-700 rounded-xl transition-all shadow-sm hover:shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save AI Settings
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('aiSettingsData', () => ({
                providers: @js($providers ?: []),
                addProvider() {
                    const defaults = {
                        openai: { model: 'gpt-4o-mini', name: 'GPT-4o Mini' },
                        gemini: { model: 'gemini-1.5-pro', name: 'Gemini 1.5 Pro' },
                        anthropic: { model: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
                        zai: { model: 'glm-4.6', name: 'Z.Ai' },
                        custom: { model: '', name: 'Custom Provider' },
                    };

                    const newProvider = {
                        id: Date.now(),
                        slug: 'openai',
                        name: defaults.openai.name,
                        api_key: '',
                        model: defaults.openai.model,
                        temperature: 0.7,
                        max_tokens: 4096,
                        is_active: true,
                        is_default: false,
                        has_key: false,
                        expanded: true,
                        features: {}
                    };
                    this.providers.push(newProvider);
                    console.log('Provider added. Total providers:', this.providers.length);
                },
                removeProvider(index) {
                    const wasDefault = this.providers[index].is_default;
                    this.providers.splice(index, 1);
                    if (wasDefault && this.providers.length > 0) {
                        this.providers[0].is_default = true;
                    }
                },
                setDefault(index) {
                    this.providers.forEach((p, i) => p.is_default = i === index);
                },
                toggleExpand(index) {
                    this.providers[index].expanded = !this.providers[index].expanded;
                },
                setProviderDefaults(provider) {
                    const defaults = {
                        openai: { model: 'gpt-4o-mini', name: 'GPT-4o Mini' },
                        gemini: { model: 'gemini-1.5-pro', name: 'Gemini 1.5 Pro' },
                        anthropic: { model: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
                        zai: { model: 'glm-4.6', name: 'Z.Ai' },
                        custom: { model: '', name: 'Custom Provider' },
                    };
                    if (defaults[provider.slug] && !provider.name) {
                        provider.name = defaults[provider.slug].name;
                        provider.model = defaults[provider.slug].model;
                    }
                },
                modelPlaceholder(slug) {
                    const map = {
                        openai: 'e.g. gpt-4o-mini',
                        gemini: 'e.g. gemini-1.5-pro',
                        anthropic: 'e.g. claude-3-5-sonnet-20241022',
                        zai: 'e.g. glm-4.6',
                        custom: 'Model name or ID',
                    };
                    return map[slug] || 'Model identifier';
                },
                init() {
                    console.log('AI Settings initialized with', this.providers.length, 'providers');
                    // Initialize providers with proper structure
                    if (!Array.isArray(this.providers) || this.providers.length === 0) {
                        this.providers = [{
                            id: Date.now(),
                            slug: 'openai',
                            name: 'GPT-4',
                            api_key: '',
                            model: 'gpt-4o-mini',
                            temperature: 0.7,
                            max_tokens: 4096,
                            is_active: true,
                            is_default: true,
                            has_key: false,
                            expanded: true,
                            features: {}
                        }];
                    } else {
                        // Add features to each provider
                        this.providers = this.providers.map((p, i) => ({
                            ...p,
                            features: p.settings?.features || {},
                            expanded: p.expanded || i === 0
                        }));
                    }
                }
            }));
        });
    </script>
    @endpush
</x-layouts.app>
