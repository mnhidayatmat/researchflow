@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
])

@php
$variants = [
    'default' => 'bg-surface text-secondary border border-border',
    'primary' => 'bg-primary/5 text-primary border border-primary/10',
    'accent' => 'bg-accent-light text-accent border border-accent/20',
    'success' => 'bg-success-light text-success border border-success/20',
    'warning' => 'bg-warning-light text-warning border border-warning/20',
    'danger' => 'bg-danger-light text-danger border border-danger/20',
    'info' => 'bg-info-light text-info border border-info/20',
    'neutral' => 'bg-gray-100 text-gray-700 border border-gray-200',
];

$sizes = [
    'xs' => 'px-1.5 py-0.5 text-[10px]',
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-xs',
    'lg' => 'px-3 py-1.5 text-sm',
];

$dotSizes = [
    'xs' => 'w-1 h-1',
    'sm' => 'w-1.5 h-1.5',
    'md' => 'w-1.5 h-1.5',
    'lg' => 'w-2 h-2',
];

$dotColors = [
    'default' => 'bg-secondary',
    'primary' => 'bg-primary',
    'accent' => 'bg-accent',
    'success' => 'bg-success',
    'warning' => 'bg-warning',
    'danger' => 'bg-danger',
    'info' => 'bg-info',
    'neutral' => 'bg-gray-500',
];

$classes = 'inline-flex items-center gap-1.5 rounded-full font-medium '
    . ($variants[$variant] ?? $variants['default'])
    . ' ' . ($sizes[$size] ?? $sizes['md']);

$dotClass = $dotSizes[$size] ?? $dotSizes['md'];
$dotColorClass = $dotColors[$variant] ?? $dotColors['default'];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="rounded-full {{ $dotClass }} {{ $dotColorClass }}"></span>
    @endif
    {{ $slot }}
</span>
