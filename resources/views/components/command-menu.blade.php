{{-- Command Palette / Quick Search --}}
<div x-data="commandPalette()" @keydown.window.prevent.cmd.k="open = true" @keydown.window.prevent.ctrl.k="open = true">
    {{-- Trigger button --}}
    <button @click="open = true" class="hidden sm:flex items-center gap-2 px-3 py-1.5 text-sm text-secondary bg-white border border-border rounded-lg hover:border-tertiary transition-colors w-64">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <span>Search...</span>
        <span class="ml-auto flex items-center gap-1">
            <kbd class="px-1.5 py-0.5 text-xs bg-surface border border-border rounded font-mono">⌘</kbd>
            <kbd class="px-1.5 py-0.5 text-xs bg-surface border border-border rounded font-mono">K</kbd>
        </span>
    </button>

    {{-- Modal --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="open = false" @keydown.escape.prevent="open = false"
         class="fixed inset-0 z-50 flex items-start justify-center pt-[15vh]">
        <div class="absolute inset-0 bg-primary/10 backdrop-blur-sm"></div>
        <div class="relative w-full max-w-xl mx-4 bg-white rounded-2xl shadow-2xl overflow-hidden" @click.stop>
            {{-- Search input --}}
            <div class="flex items-center gap-3 px-4 border-b border-border">
                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input x-model="query" type="text" placeholder="Type a command or search..." class="flex-1 py-4 text-sm bg-transparent border-0 focus:outline-none focus:ring-0 text-primary placeholder:text-tertiary">
                <kbd class="hidden sm:inline-block px-1.5 py-0.5 text-xs bg-surface border border-border rounded font-mono text-tertiary">ESC</kbd>
            </div>

            {{-- Results --}}
            <div class="max-h-[60vh] overflow-y-auto scrollbar-thin p-2">
                @if(isset($groups))
                    @foreach($groups as $group => $items)
                        <div x-show="filtered('{{ $group }}').length > 0" class="mb-2">
                            <p class="px-2 py-1 text-xs font-semibold text-tertiary uppercase tracking-wider">{{ $group }}</p>
                            <template x-for="item in filtered('{{ $group }}')" :key="item.id">
                                <a :href="item.href" @click="open = false" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-primary hover:bg-surface transition-colors" :class="{ 'bg-surface': highlighted === item.id }">
                                    <span x-html="item.icon" class="w-4 h-4 text-secondary"></span>
                                    <span x-html="item.label"></span>
                                    <span x-if="item.shortcut" class="ml-auto text-xs text-tertiary" x-text="item.shortcut"></span>
                                </a>
                            </template>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-secondary text-center py-8">No results found</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function commandPalette() {
    return {
        open: false,
        query: '',
        items: @json($items ?? []),
        filtered(group) {
            if (!this.query) return this.items.filter(i => i.group === group);
            return this.items.filter(i => i.group === group && i.label.toLowerCase().includes(this.query.toLowerCase()));
        }
    }
}
</script>
