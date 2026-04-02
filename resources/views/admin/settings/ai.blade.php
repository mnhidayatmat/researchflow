<x-layouts.app title="AI Settings">
    <x-slot:header>Settings</x-slot:header>

    <div class="max-w-3xl" x-data="aiSettingsData" x-init="init()">
        {{-- Page header --}}
        <div class="mb-6">
            <h2 class="text-base font-semibold text-primary dark:text-dark-primary">AI Provider Configuration</h2>
            <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Configure AI providers for automated feedback and suggestions</p>
        </div>

        {{-- Info card --}}
        <div class="mb-6 p-4 bg-info-light/30 border border-info-light rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-info shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-xs text-primary dark:text-dark-primary">
                <p class="font-medium mb-1">Get your API keys:</p>
                <ul class="space-y-0.5 text-secondary dark:text-dark-secondary">
                    <li>• <strong>OpenAI:</strong> <a href="https://platform.openai.com/api-keys" target="_blank" class="text-info hover:underline">platform.openai.com/api-keys</a></li>
                    <li>• <strong>Google:</strong> <a href="https://console.cloud.google.com/" target="_blank" class="text-info hover:underline">console.cloud.google.com</a></li>
                    <li>• <strong>Anthropic:</strong> <a href="https://console.anthropic.com/" target="_blank" class="text-info hover:underline">console.anthropic.com</a></li>
                    <li>• <strong>Z.Ai:</strong> <a href="https://z.ai/manage-apikey/apikey-list" target="_blank" class="text-info hover:underline">z.ai/manage-apikey</a> (Models: glm-4.7, glm-5-turbo)</li>
                </ul>
            </div>
        </div>

        {{-- Success / validation error messages --}}
        @if(session('success'))
            <div class="mb-4 p-4 rounded-xl bg-success/10 border border-success/20 flex items-center gap-3">
                <svg class="w-5 h-5 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <p class="text-sm font-medium text-success">{{ session('success') }}</p>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-4 rounded-xl bg-danger/10 border border-danger/20">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-danger shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-sm font-semibold text-danger mb-1">Please fix the following errors:</p>
                        <ul class="text-xs text-danger/80 space-y-0.5 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.ai.update') }}">
            @csrf

            {{-- Providers list --}}
            <div class="space-y-4 mb-6">
                <template x-for="(provider, index) in providers" :key="provider.id">
                    <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden" :class="!provider.is_active && 'opacity-60'">
                        {{-- Provider header --}}
                        {{-- Always-present hidden inputs: id and is_active --}}
                        <input type="hidden" :name="`providers[${index}][id]`" :value="provider.id ?? ''">
                        <input type="hidden" :name="`providers[${index}][is_active]`" value="0">

                        <div class="flex items-center justify-between px-6 py-4 border-b border-border dark:border-dark-border bg-surface/50">
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
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-accent/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white dark:bg-dark-card after:border-gray-300 dark:border-dark-border after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent"></div>
                                </label>
                                <div>
                                    <span class="text-sm font-medium text-primary dark:text-dark-primary" x-text="provider.name || provider.slug || 'New Provider'"></span>
                                    <span class="ml-2 text-xs text-secondary dark:text-dark-secondary" x-show="provider.is_default">(Default)</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="setDefault(index)"
                                    x-show="!provider.is_default"
                                    class="px-3 py-1.5 text-xs text-secondary dark:text-dark-secondary border border-border dark:border-dark-border rounded-lg hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface transition-colors"
                                >
                                    Set Default
                                </button>
                                <button
                                    type="button"
                                    @click="toggleExpand(index)"
                                    class="p-2 text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary dark:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4 transition-transform" :class="provider.expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button
                                    type="button"
                                    @click="removeProvider(index)"
                                    x-show="providers.length > 1"
                                    class="p-2 text-secondary dark:text-dark-secondary hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Provider config (collapsible) --}}
                        <div x-show="provider.expanded" x-cloak class="p-6">
                            <input type="hidden" :name="`providers[${index}][is_default]`" :value="provider.is_default ? 1 : 0">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                {{-- Provider type/slug --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Provider Type</label>
                                    <select
                                        :name="`providers[${index}][slug]`"
                                        x-model="provider.slug"
                                        @change="setProviderDefaults(provider)"
                                        class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-2.5 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
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
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Display Name</label>
                                    <input
                                        type="text"
                                        :name="`providers[${index}][name]`"
                                        x-model="provider.name"
                                        placeholder="e.g. GPT-4o Mini"
                                        class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-2.5 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- API Key --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">
                                        API Key <span class="text-red-400">*</span>
                                    </label>
                                    <input
                                        type="password"
                                        :name="`providers[${index}][api_key]`"
                                        x-model="provider.api_key"
                                        :placeholder="provider.has_key ? '••••••••••••  (saved)' : 'Enter API key'"
                                        class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-2.5 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Model --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Model</label>
                                    <input
                                        type="text"
                                        :name="`providers[${index}][model]`"
                                        x-model="provider.model"
                                        :placeholder="modelPlaceholder(provider.slug)"
                                        class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-2.5 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Temperature --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">
                                        Temperature <span class="text-secondary dark:text-dark-secondary font-normal ml-1" x-text="`(${provider.temperature ?? 0.7})`"></span>
                                    </label>
                                    <input
                                        type="range"
                                        :name="`providers[${index}][temperature]`"
                                        x-model="provider.temperature"
                                        min="0" max="1" step="0.1"
                                        class="w-full accent-amber-500"
                                    >
                                    <div class="flex justify-between text-[10px] text-secondary dark:text-dark-secondary mt-1">
                                        <span>Precise</span>
                                        <span>Creative</span>
                                    </div>
                                </div>

                                {{-- Custom endpoint (for custom/zai providers) --}}
                                <div class="sm:col-span-2" x-show="provider.slug === 'custom' || provider.slug === 'zai'">
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">API Base URL <span class="font-normal text-tertiary dark:text-dark-tertiary" x-show="provider.slug === 'zai'">(Coding Plan: https://api.z.ai/api/anthropic)</span></label>
                                    <input
                                        type="url"
                                        :name="`providers[${index}][base_url]`"
                                        x-model="provider.base_url"
                                        placeholder="https://api.yourprovider.com/v1/chat/completions"
                                        class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-2.5 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Max tokens --}}
                                <div>
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1.5">Max Tokens</label>
                                    <input
                                        type="number"
                                        :name="`providers[${index}][max_tokens]`"
                                        x-model="provider.max_tokens"
                                        min="100"
                                        max="128000"
                                        placeholder="e.g. 4096"
                                        class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-2.5 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none transition-all"
                                    >
                                </div>

                                {{-- Features --}}
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-2">Enable For</label>
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach(['feedback' => 'AI Feedback', 'summary' => 'Report Summary', 'suggestions' => 'Task Suggestions'] as $feat => $featlabel)
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-border dark:border-dark-border hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface cursor-pointer transition-colors">
                                                <input type="hidden" :name="`providers[${index}][features][{{ $feat }}]`" value="0">
                                                <input
                                                    type="checkbox"
                                                    :name="`providers[${index}][features][{{ $feat }}]`"
                                                    value="1"
                                                    x-model="provider.features['{{ $feat }}']"
                                                    class="rounded border-border dark:border-dark-border text-accent focus:ring-accent/30"
                                                >
                                                <span class="text-xs text-primary dark:text-dark-primary">{{ $featlabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Test Connection --}}
                                <div class="sm:col-span-2 pt-2 border-t border-border dark:border-dark-border">
                                    <button type="button"
                                            @click="testProvider(provider, index)"
                                            :disabled="provider._testing"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium rounded-xl border border-border dark:border-dark-border text-secondary dark:text-dark-secondary hover:bg-surface dark:hover:bg-dark-surface disabled:opacity-50 transition-all">
                                        <svg x-show="!provider._testing" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <svg x-show="provider._testing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                        <span x-text="provider._testing ? 'Testing...' : 'Test Connection'"></span>
                                    </button>
                                    <div x-show="provider._testResult" class="mt-2 p-3 rounded-xl text-xs" x-cloak
                                         :class="provider._testSuccess ? 'bg-success/10 text-success border border-success/20' : 'bg-danger/10 text-danger border border-danger/20'">
                                        <p class="font-medium" x-text="provider._testResult"></p>
                                        <p x-show="provider._testDiag" class="mt-1 text-[10px] opacity-80 font-mono" x-text="provider._testDiag"></p>
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
                class="w-full flex items-center justify-center gap-2 py-3 text-sm text-secondary dark:text-dark-secondary hover:text-accent hover:bg-surface/50 border border-dashed border-border dark:border-dark-border hover:border-accent rounded-xl transition-all cursor-pointer mb-6"
                style="position: relative; z-index: 10;"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Another Provider
            </button>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 text-sm font-medium text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary dark:text-dark-primary border border-border dark:border-dark-border rounded-xl transition-all">
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
                        gemini: { model: 'gemini-2.5-flash', name: 'Gemini 2.5 Flash' },
                        anthropic: { model: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
                        zai: { model: 'glm-4.7', name: 'Z.Ai' },
                        custom: { model: '', name: 'Custom Provider' },
                    };

                    const newProvider = {
                        id: null,
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
                        openai:    { model: 'gpt-4o-mini',                  name: 'GPT-4o Mini' },
                        gemini:    { model: 'gemini-2.5-flash',              name: 'Gemini 2.5 Flash' },
                        anthropic: { model: 'claude-3-5-sonnet-20241022',    name: 'Claude 3.5 Sonnet' },
                        zai:       { model: 'glm-4.7',                       name: 'Z.Ai' },
                        custom:    { model: '',                               name: 'Custom Provider' },
                    };
                    if (defaults[provider.slug]) {
                        provider.name  = defaults[provider.slug].name;
                        provider.model = defaults[provider.slug].model;
                    }
                    // Clear base_url when switching away from zai/custom
                    if (provider.slug !== 'zai' && provider.slug !== 'custom') {
                        provider.base_url = '';
                    }
                },
                modelPlaceholder(slug) {
                    const map = {
                        openai: 'e.g. gpt-4o-mini',
                        gemini: 'e.g. gemini-2.5-flash',
                        anthropic: 'e.g. claude-3-5-sonnet-20241022',
                        zai: 'e.g. glm-4.7, glm-5-turbo',
                        custom: 'Model name or ID',
                    };
                    return map[slug] || 'Model identifier';
                },
                async testProvider(provider, index) {
                    if (!provider.id) {
                        provider._testResult = 'Please save this provider first before testing.';
                        provider._testSuccess = false;
                        provider._testDiag = null;
                        return;
                    }
                    provider._testing = true;
                    provider._testResult = null;
                    provider._testDiag = null;
                    try {
                        const res = await fetch('{{ route("admin.settings.ai.test") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ provider_id: provider.id }),
                        });
                        const data = await res.json();
                        provider._testSuccess = data.success;
                        provider._testResult = data.message + (data.response ? ' — Response: ' + data.response : '');
                        if (data.diagnostic) {
                            provider._testDiag = `slug=${data.diagnostic.slug} model=${data.diagnostic.model} base_url=${data.diagnostic.base_url || '(default)'} active=${data.diagnostic.is_active} default=${data.diagnostic.is_default} has_key=${data.diagnostic.has_key}`;
                        }
                    } catch (e) {
                        provider._testSuccess = false;
                        provider._testResult = 'Request failed: ' + e.message;
                    } finally {
                        provider._testing = false;
                    }
                },
                init() {
                    if (!Array.isArray(this.providers) || this.providers.length === 0) {
                        this.providers = [{
                            id: null,
                            slug: 'openai',
                            name: 'GPT-4o Mini',
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
                        this.providers = this.providers.map((p) => {
                            // Normalize features: could be array [] or object {feedback:'1'}
                            const rawFeatures = p.settings?.features;
                            const features = (rawFeatures && !Array.isArray(rawFeatures))
                                ? rawFeatures
                                : {};
                            return {
                                ...p,
                                features,
                                expanded: p.is_default || p.is_active,
                            };
                        });
                    }
                }
            }));
        });
    </script>
    @endpush
</x-layouts.app>
