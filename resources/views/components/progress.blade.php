@props([
    'value' => 0,
    'max' => 100,
    'size' => 'md',
    'variant' => 'default',
    'showLabel' => false,
])

@php
$percentage = min(max(round(($value / $max) * 100), 0), 100);

$sizes = [
    'xs' => 'h-1',
    'sm' => 'h-1.5',
    'md' => 'h-2',
    'lg' => 'h-3',
    'xl' => 'h-4',
];

$variants = [
    'default' => 'bg-accent dark:bg-dark-accent',
    'success' => 'bg-success dark:bg-dark-success',
    'warning' => 'bg-warning dark:bg-dark-warning',
    'danger' => 'bg-danger dark:bg-dark-danger',
    'info' => 'bg-info dark:bg-dark-info',
];
@endphp

<div class="w-full">
    @if($showLabel)
        <div class="flex items-center justify-between mb-1">
            <span class="text-xs font-medium text-secondary dark:text-dark-secondary">Progress</span>
            <span class="text-xs font-medium text-primary dark:text-dark-primary">{{ $percentage }}%</span>
        </div>
    @endif

    <div class="overflow-hidden rounded-full bg-border dark:bg-dark-border {{ $sizes[$size] ?? $sizes['md'] }}">
        <div class="{{ $variants[$variant] ?? $variants['default'] }} {{ $sizes[$size] ?? $sizes['md'] }} transition-all duration-300 ease-out"
             style="width: {{ $percentage }}%"
             role="progressbar"
             aria-valuenow="{{ $value }}"
             aria-valuemin="0"
             aria-valuemax="{{ $max }}">
        </div>
    </div>
</div>
