<x-layouts.app title="AI Settings">
    <x-slot:header>Settings</x-slot:header>

    <div class="max-w-2xl" x-data="aiSettings(@json($providers))">
        {{-- Page header --}}
        <div class="mb-6">
            <h2 class="text-base font-semibold text-primary">AI Provider Configuration</h2>
            <p class="text-xs text-secondary mt-0.5">Configure AI providers for automated feedback and suggestions</p>
        </div>

        <form method="POST" action="{{ route('admin.settings.ai.update') }}">
            @csrf

            {{-- Providers list --}}
            <div class="space-y-4 mb-6">
                <template x-for="(provider, index) in providers" :key="provider.id">
                    <x-card :padding='false'>
                        {{-- Provider header --}}
                        <div class="flex items-center justify-between px-5 py-3 border-b border-border bg-surface/50">
                            <div class="flex items-center gap-3">
                                {{-- Active toggle --}}
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :name="`providers[${index}][active]`"
                                        value="1"
                                        x-model="provider.active"
                                        class="sr-only peer"
                                    >
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:ring-2 peer-focus:ring-accent/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-accent"></div>
                                </label>
                                <div>
                                    <span class="text-sm font-medium text-primary" x-text="provider.label || provider.type || 'New Provider'"></span>
                                    <span class="ml-2 text-xs text-secondary" x-show="provider.is_default">(Default)</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="setDefault(index)"
                                    x-show="!provider.is_default"
                                    class="px-2.5 py-1 text-xs text-secondary border border-border rounded hover:bg-gray-50 transition-colors"
                                >
                                    Set Default
                                </button>
                                <button
                                    type="button"
                                    @click="toggleExpand(index)"
                                    class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 rounded transition-colors"
                                >
                                    <svg class="w-4 h-4 transition-transform" :class="provider.expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button
                                    type="button"
                                    @click="removeProvider(index)"
                                    x-show="providers.length > 1"
                                    class="p-1.5 text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Provider config (collapsible) --}}
                        <div x-show="provider.expanded" x-cloak class="p-5">
                            <input type="hidden" :name="`providers[${index}][id]`" :value="provider.id">
                            <input type="hidden" :name="`providers[${index}][is_default]`" :value="provider.is_default ? 1 : 0">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Provider type --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1">Provider Type</label>
                                    <select
                                        :name="`providers[${index}][type]`"
                                        x-model="provider.type"
                                        @change="setProviderDefaults(provider)"
                                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                    >
                                        <option value="openai">OpenAI</option>
                                        <option value="gemini">Google Gemini</option>
                                        <option value="anthropic">Anthropic Claude</option>
                                        <option value="custom">Custom / Self-hosted</option>
                                    </select>
                                </div>

                                {{-- Label --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1">Display Label</label>
                                    <input
                                        type="text"
                                        :name="`providers[${index}][label]`"
                                        x-model="provider.label"
                                        placeholder="e.g. GPT-4 Turbo"
                                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                    >
                                </div>

                                {{-- API Key --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-secondary mb-1">
                                        API Key <span class="text-red-400">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        :name="`providers[${index}][api_key]`"
                                        x-model="provider.api_key"
                                        :placeholder="provider.has_key ? '••••••••••••  (saved)' : 'Enter API key'"
                                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                    >
                                </div>

                                {{-- Model --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1">Model</label>
                                    <input
                                        type="text"
                                        :name="`providers[${index}][model]`"
                                        x-model="provider.model"
                                        :placeholder="modelPlaceholder(provider.type)"
                                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                    >
                                </div>

                                {{-- Temperature --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1">
                                        Temperature <span class="text-secondary font-normal ml-1" x-text="`(${provider.temperature ?? 0.7})`"></span>
                                    </label>
                                    <input
                                        type="range"
                                        :name="`providers[${index}][temperature]`"
                                        x-model="provider.temperature"
                                        min="0" max="1" step="0.1"
                                        class="w-full accent-amber-500"
                                    >
                                    <div class="flex justify-between text-[10px] text-secondary mt-0.5">
                                        <span>Precise</span>
                                        <span>Creative</span>
                                    </div>
                                </div>

                                {{-- Custom endpoint (for custom type) --}}
                                <div class="sm:col-span-2" x-show="provider.type === 'custom'">
                                    <label class="block text-xs font-medium text-secondary mb-1">API Endpoint URL</label>
                                    <input
                                        type="url"
                                        :name="`providers[${index}][endpoint]`"
                                        x-model="provider.endpoint"
                                        placeholder="https://api.yourprovider.com/v1/chat/completions"
                                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                    >
                                </div>

                                {{-- Max tokens --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1">Max Tokens</label>
                                    <input
                                        type="number"
                                        :name="`providers[${index}][max_tokens]`"
                                        x-model="provider.max_tokens"
                                        min="100"
                                        max="128000"
                                        placeholder="e.g. 4096"
                                        class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                    >
                                </div>

                                {{-- Features --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-2">Enable For</label>
                                    <div class="space-y-1.5">
                                        @foreach(['feedback' => 'AI Feedback', 'summary' => 'Report Summary', 'suggestions' => 'Task Suggestions'] as $feat => $featlabel)
                                            <label class="flex items-center gap-2 cursor-pointer">
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
                    </x-card>
                </template>
            </div>

            {{-- Add provider button --}}
            <button
                type="button"
                @click="addProvider()"
                class="w-full flex items-center justify-center gap-2 py-3 text-sm text-secondary hover:text-accent border border-dashed border-border hover:border-accent rounded-lg transition-colors mb-6"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Another Provider
            </button>

            <div class="flex justify-end">
                <x-button type="submit" variant="primary">Save AI Settings</x-button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function aiSettings(initialProviders) {
            return {
                providers: initialProviders.length ? initialProviders.map((p, i) => ({ ...p, expanded: i === 0 })) : [{
                    id: Date.now(),
                    type: 'openai',
                    label: 'GPT-4',
                    api_key: '',
                    model: 'gpt-4-turbo',
                    temperature: 0.7,
                    max_tokens: 4096,
                    active: true,
                    is_default: true,
                    has_key: false,
                    features: { feedback: true, summary: true, suggestions: false },
                    expanded: true
                }],
                addProvider() {
                    this.providers.push({
                        id: Date.now(),
                        type: 'openai',
                        label: '',
                        api_key: '',
                        model: 'gpt-4-turbo',
                        temperature: 0.7,
                        max_tokens: 4096,
                        active: true,
                        is_default: false,
                        has_key: false,
                        features: { feedback: false, summary: false, suggestions: false },
                        expanded: true
                    });
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
                        openai: { model: 'gpt-4-turbo', label: 'GPT-4 Turbo' },
                        gemini: { model: 'gemini-1.5-pro', label: 'Gemini 1.5 Pro' },
                        anthropic: { model: 'claude-3-5-sonnet-20241022', label: 'Claude 3.5 Sonnet' },
                        custom: { model: '', label: 'Custom Provider' },
                    };
                    if (defaults[provider.type] && !provider.label) {
                        provider.label = defaults[provider.type].label;
                        provider.model = defaults[provider.type].model;
                    }
                },
                modelPlaceholder(type) {
                    const map = {
                        openai: 'e.g. gpt-4-turbo',
                        gemini: 'e.g. gemini-1.5-pro',
                        anthropic: 'e.g. claude-3-5-sonnet-20241022',
                        custom: 'Model name or ID',
                    };
                    return map[type] || 'Model identifier';
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
