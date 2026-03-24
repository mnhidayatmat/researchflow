@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
    'color' => null,
])

@php
// Support both 'variant' and legacy 'color' prop
$effectiveVariant = $color ? match($color) {
    'red' => 'danger',
    'orange' => 'warning',
    'yellow' => 'warning',
    'green' => 'success',
    'blue' => 'info',
    'purple' => 'primary',
    'cyan' => 'info',
    'gray' => 'neutral',
    default => $color,
} : $variant;

$variants = [
    'default' => 'bg-surface dark:bg-dark-surface text-secondary dark:text-dark-secondary border border-border dark:border-dark-border',
    'primary' => 'bg-primary/5 dark:bg-dark-primary/10 text-primary dark:text-dark-primary border border-primary/10 dark:border-dark-primary/10',
    'accent' => 'bg-accent-light dark:bg-dark-accent-light text-accent dark:text-dark-accent border border-accent/20 dark:border-dark-accent/20',
    'success' => 'bg-success-light dark:bg-dark-success-light text-success dark:text-dark-success border border-success/20 dark:border-dark-success/20',
    'warning' => 'bg-warning-light dark:bg-dark-warning-light text-warning dark:text-dark-warning border border-warning/20 dark:border-dark-warning/20',
    'danger' => 'bg-danger-light dark:bg-dark-danger-light text-danger dark:text-dark-danger border border-danger/20 dark:border-dark-danger/20',
    'info' => 'bg-info-light dark:bg-dark-info-light text-info dark:text-dark-info border border-info/20 dark:border-dark-info/20',
    'neutral' => 'bg-gray-100 dark:bg-dark-surface text-gray-700 dark:text-dark-secondary border border-gray-200 dark:border-dark-border',
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
    'default' => 'bg-secondary dark:bg-dark-secondary',
    'primary' => 'bg-primary dark:bg-dark-primary',
    'accent' => 'bg-accent dark:bg-dark-accent',
    'success' => 'bg-success dark:bg-dark-success',
    'warning' => 'bg-warning dark:bg-dark-warning',
    'danger' => 'bg-danger dark:bg-dark-danger',
    'info' => 'bg-info dark:bg-dark-info',
    'neutral' => 'bg-gray-500 dark:bg-dark-secondary',
];

$classes = 'inline-flex items-center gap-1.5 rounded-full font-medium '
    . ($variants[$effectiveVariant] ?? $variants['default'])
    . ' ' . ($sizes[$size] ?? $sizes['md']);

$dotClass = $dotSizes[$size] ?? $dotSizes['md'];
$dotColorClass = $dotColors[$effectiveVariant] ?? $dotColors['default'];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="rounded-full {{ $dotClass }} {{ $dotColorClass }}"></span>
    @endif
    {{ $slot }}
</span>
