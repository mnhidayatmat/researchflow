@props([
    'variant' => 'default',
    'padding' => 'normal',
    'title' => null,
    'subtitle' => null,
    'action' => null,
    'borderless' => false,
    'hoverable' => false,
])

@php
$variants = [
    'default' => 'bg-white border border-border',
    'bordered' => 'bg-white border border-border',
    'borderless' => 'bg-white border-0',
    'elevated' => 'bg-white border-0 shadow-medium',
    'surface' => 'bg-surface border-0',
];

$paddings = [
    'none' => '',
    'tight' => 'p-4',
    'normal' => 'p-5',
    'loose' => 'p-6',
    'xl' => 'p-8',
];

$classes = ($variants[$variant] ?? $variants['default'])
    . ($borderless ? ' border-0' : '')
    . ($hoverable ? ' hover:shadow-soft cursor-pointer transition-shadow duration-150' : '');

$paddingClass = $paddings[$padding] ?? $paddings['normal'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl overflow-hidden ' . $classes]) }}>
    @if($title || $subtitle || $action)
        <div class="flex items-center justify-between {{ $padding !== 'none' ? substr_replace($paddingClass, '-y', -1) : 'px-5 py-4' }} {{ $padding !== 'none' && !$action ? 'border-b border-border' : '' }}">
            <div>
                @if($title)
                    <h3 class="text-sm font-semibold text-primary">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-secondary mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            @if($action)
                {{ $action }}
            @endif
        </div>
    @endif

    <div class="{{ $paddingClass }}">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-5 py-3 bg-surface border-t border-border">
            {{ $footer }}
        </div>
    @endif
</div>
