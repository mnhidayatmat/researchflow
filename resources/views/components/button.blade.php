@props(['variant' => 'primary', 'size' => 'md', 'href' => null])

@php
$base = 'inline-flex items-center justify-center font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-accent/50 dark:focus:ring-offset-dark-bg';
$variants = [
    'primary' => 'bg-primary dark:bg-dark-primary text-white dark:text-dark-bg hover:bg-gray-700 dark:hover:bg-gray-200',
    'accent' => 'bg-accent dark:bg-dark-accent text-white hover:bg-amber-600 dark:hover:bg-amber-500',
    'secondary' => 'bg-white dark:bg-dark-card text-primary dark:text-dark-primary border border-border dark:border-dark-border hover:bg-gray-50 dark:hover:bg-dark-surface',
    'danger' => 'bg-red-600 dark:bg-red-500 text-white hover:bg-red-700 dark:hover:bg-red-600',
    'ghost' => 'text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-gray-50 dark:hover:bg-dark-surface',
];
$sizes = [
    'sm' => 'px-3 py-1.5 text-xs gap-1.5',
    'md' => 'px-4 py-2 text-sm gap-2',
    'lg' => 'px-5 py-2.5 text-sm gap-2',
];
$classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
