@props([
    'title' => null,
    'value' => null,
    'change' => null,
    'icon' => null,
    'variant' => 'default',
    'href' => null,
])

@php
$variants = [
    'default' => [
        'gradient' => 'from-gray-50 to-white dark:from-dark-surface dark:to-dark-card',
        'icon' => 'bg-gray-100 dark:bg-dark-surface text-gray-700 dark:text-dark-secondary',
    ],
    'accent' => [
        'gradient' => 'from-accent-light to-white dark:from-dark-accent-light dark:to-dark-card',
        'icon' => 'bg-accent/10 text-accent dark:text-dark-accent',
    ],
    'success' => [
        'gradient' => 'from-success-light to-white dark:from-dark-success-light dark:to-dark-card',
        'icon' => 'bg-success/10 text-success dark:text-dark-success',
    ],
    'info' => [
        'gradient' => 'from-info-light to-white dark:from-dark-info-light dark:to-dark-card',
        'icon' => 'bg-info/10 text-info dark:text-dark-info',
    ],
    'warning' => [
        'gradient' => 'from-warning-light to-white dark:from-dark-warning-light dark:to-dark-card',
        'icon' => 'bg-warning/10 text-warning dark:text-dark-warning',
    ],
    'danger' => [
        'gradient' => 'from-danger-light to-white dark:from-dark-danger-light dark:to-dark-card',
        'icon' => 'bg-danger/10 text-danger dark:text-dark-danger',
    ],
];

$config = $variants[$variant] ?? $variants['default'];
$isPositive = $change && ($change > 0 || str_starts_with($change, '+'));
@endphp

@isset($href)
    <a href="{{ $href }}" class="block group">
@else
    <div class="group">
@endisset

    <div class="relative overflow-hidden rounded-xl border border-border dark:border-dark-border bg-gradient-to-br {{ $config['gradient'] }} p-5 transition-all duration-200 hover:shadow-soft dark:hover:shadow-dark-soft">
        @if($icon)
            <div class="flex items-start justify-between">
                <div class="flex-1">
        @endif

        @if($title)
            <p class="text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wide">{{ $title }}</p>
        @endif

        @if($value !== null)
            <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $value }}</p>
        @endif

        @if($change !== null)
            <div class="mt-2 flex items-center gap-1 text-xs font-medium">
                @if($isPositive)
                    <svg class="w-3.5 h-3.5 text-success dark:text-dark-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <span class="text-success dark:text-dark-success">{{ $change }}</span>
                @else
                    <svg class="w-3.5 h-3.5 text-danger dark:text-dark-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    <span class="text-danger dark:text-dark-danger">{{ $change }}</span>
                @endif
                <span class="text-secondary dark:text-dark-secondary">vs last period</span>
            </div>
        @endif

        @if($icon)
                </div>
                <div class="w-10 h-10 rounded-xl {{ $config['icon'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                </div>
            </div>
        @endif
    </div>

@isset($href)
    </a>
@else
    </div>
@endisset
