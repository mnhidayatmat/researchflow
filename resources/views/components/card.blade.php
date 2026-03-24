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
    'default' => 'bg-white dark:bg-dark-card border border-border dark:border-dark-border',
    'bordered' => 'bg-white dark:bg-dark-card border border-border dark:border-dark-border',
    'borderless' => 'bg-white dark:bg-dark-card border-0',
    'elevated' => 'bg-white dark:bg-dark-card border-0 shadow-medium dark:shadow-dark-medium',
    'surface' => 'bg-surface dark:bg-dark-surface border-0',
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
    . ($hoverable ? ' hover:shadow-soft dark:hover:shadow-dark-soft cursor-pointer transition-shadow duration-150' : '');

$paddingClass = $paddings[$padding] ?? $paddings['normal'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl overflow-hidden ' . $classes]) }}>
    @if($title || $subtitle || $action)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between {{ $padding !== 'none' ? substr_replace($paddingClass, '-y', -1) : 'px-5 py-4' }} {{ $padding !== 'none' && !$action ? 'border-b border-border dark:border-dark-border' : '' }}">
            <div>
                @if($title)
                    <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $subtitle }}</p>
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
        <div class="px-5 py-3 bg-surface dark:bg-dark-surface border-t border-border dark:border-dark-border">
            {{ $footer }}
        </div>
    @endif
</div>
