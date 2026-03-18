@props(['items' => []])

<div class="space-y-2" x-data="{ open: {{ $defaultOpen ?? 0 }} }">
    @foreach($items as $index => $item)
        @php
            $title = is_string($item) ? $item : ($item['title'] ?? "Item $index");
            $content = is_string($item) ? null : ($item['content'] ?? null);
            $id = 'accordion-' . $index;
        @endphp

        <div class="border border-border rounded-xl overflow-hidden">
            <button @click="open = open === {{ $index }} ? null : {{ $index }}"
                    class="w-full flex items-center justify-between px-4 py-3 text-left bg-white hover:bg-surface transition-colors">
                <span class="text-sm font-medium text-primary">{{ $title }}</span>
                <svg class="w-4 h-4 text-secondary transition-transform" :class="{ 'rotate-180': open === {{ $index }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open === {{ $index }}" x-collapse class="border-t border-border bg-white">
                <div class="p-4 text-sm text-secondary">
                    {{ $content ?? ($slot->has("item-$index") ? $slot["item-$index"] : '') }}
                </div>
            </div>
        </div>
    @endforeach
</div>
