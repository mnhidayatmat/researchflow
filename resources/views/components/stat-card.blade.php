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
        'gradient' => 'from-gray-50 to-white',
        'icon' => 'bg-gray-100 text-gray-700',
    ],
    'accent' => [
        'gradient' => 'from-accent-light to-white',
        'icon' => 'bg-accent/10 text-accent',
    ],
    'success' => [
        'gradient' => 'from-success-light to-white',
        'icon' => 'bg-success/10 text-success',
    ],
    'info' => [
        'gradient' => 'from-info-light to-white',
        'icon' => 'bg-info/10 text-info',
    ],
    'warning' => [
        'gradient' => 'from-warning-light to-white',
        'icon' => 'bg-warning/10 text-warning',
    ],
    'danger' => [
        'gradient' => 'from-danger-light to-white',
        'icon' => 'bg-danger/10 text-danger',
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

    <div class="relative overflow-hidden rounded-xl border border-border bg-gradient-to-br {{ $config['gradient'] }} p-5 transition-all duration-200 hover:shadow-soft">
        @if($icon)
            <div class="flex items-start justify-between">
                <div class="flex-1">
        @endif

        @if($title)
            <p class="text-xs font-medium text-secondary uppercase tracking-wide">{{ $title }}</p>
        @endif

        @if($value !== null)
            <p class="mt-2 text-2xl font-semibold text-primary">{{ $value }}</p>
        @endif

        @if($change !== null)
            <div class="mt-2 flex items-center gap-1 text-xs font-medium">
                @if($isPositive)
                    <svg class="w-3.5 h-3.5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <span class="text-success">{{ $change }}</span>
                @else
                    <svg class="w-3.5 h-3.5 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    <span class="text-danger">{{ $change }}</span>
                @endif
                <span class="text-secondary">vs last period</span>
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
