@props([
    'variant' => 'info',
    'dismissible' => false,
    'icon' => null,
])

@php
$variants = [
    'info' => [
        'bg' => 'bg-info-light dark:bg-dark-info-light',
        'border' => 'border-info/20 dark:border-dark-info/30',
        'text' => 'text-info dark:text-dark-info',
        'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'success' => [
        'bg' => 'bg-success-light dark:bg-dark-success-light',
        'border' => 'border-success/20 dark:border-dark-success/30',
        'text' => 'text-success dark:text-dark-success',
        'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
    'warning' => [
        'bg' => 'bg-warning-light dark:bg-dark-warning-light',
        'border' => 'border-warning/20 dark:border-dark-warning/30',
        'text' => 'text-warning dark:text-dark-warning',
        'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    ],
    'danger' => [
        'bg' => 'bg-danger-light dark:bg-dark-danger-light',
        'border' => 'border-danger/20 dark:border-dark-danger/30',
        'text' => 'text-danger dark:text-dark-danger',
        'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ],
];

$config = $variants[$variant] ?? $variants['info'];
$iconPath = $icon ?? $config['icon'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border ' . $config['bg'] . ' ' . $config['border']]) }}
     x-data="{ show: true }"
     x-show="show">
    <div class="flex items-start gap-3 p-4">
        <svg class="w-5 h-5 shrink-0 mt-0.5 {{ $config['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPath }}"/>
        </svg>
        <div class="flex-1 min-w-0">
            <div class="text-sm {{ $config['text'] }}">
                {{ $slot }}
            </div>
        </div>
        @if($dismissible)
            <button @click="show = false" class="shrink-0 {{ $config['text'] }} opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>
