@props([
    'tabs' => [],
    'active' => null,
    'variant' => 'pills',
])

@php
$variants = [
    'pills' => [
        'container' => 'bg-surface rounded-lg p-1 -space-x-1',
        'tab' => 'rounded-md px-3 py-1.5 text-sm font-medium transition-all duration-150',
        'active' => 'bg-white text-primary shadow-soft',
        'inactive' => 'text-secondary hover:text-primary hover:bg-white/50',
    ],
    'underline' => [
        'container' => 'border-b border-border',
        'tab' => 'px-4 py-3 text-sm font-medium border-b-2 transition-all duration-150',
        'active' => 'border-accent text-accent',
        'inactive' => 'border-transparent text-secondary hover:text-primary',
    ],
];

$config = $variants[$variant] ?? $variants['pills'];
@endphp

<div class="w-full">
    <div class="flex items-center gap-1 {{ $config['container'] }}">
        @foreach($tabs as $key => $tab)
            @php
                $label = is_string($tab) ? $tab : ($tab['label'] ?? $key);
                $icon = is_array($tab) ? ($tab['icon'] ?? null) : null;
                $isActive = $active === $key;
                $href = is_array($tab) ? ($tab['href'] ?? '#' . $key) : '#' . $key;
            @endphp

            <a href="{{ $href }}"
               class="flex items-center gap-2 {{ $config['tab'] }} {{ $isActive ? $config['active'] : $config['inactive'] }}">
                @if($icon)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                @endif
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if(isset($content))
        <div class="mt-4">{{ $content }}</div>
    @endif
</div>
