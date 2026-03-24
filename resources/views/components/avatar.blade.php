@props([
    'name' => null,
    'src' => null,
    'size' => 'md',
    'status' => null,
])

@php
$sizes = [
    'xs' => 'w-5 h-5 text-[10px]',
    'sm' => 'w-6 h-6 text-xs',
    'md' => 'w-8 h-8 text-xs',
    'lg' => 'w-10 h-10 text-sm',
    'xl' => 'w-12 h-12 text-base',
    '2xl' => 'w-16 h-16 text-lg',
];

$statusSizes = [
    'xs' => 'w-1.5 h-1.5 -bottom-0 -right-0',
    'sm' => 'w-2 h-2 -bottom-0 -right-0',
    'md' => 'w-2.5 h-2.5 -bottom-0.5 -right-0.5',
    'lg' => 'w-3 h-3 -bottom-0.5 -right-0.5',
    'xl' => 'w-3.5 h-3.5 -bottom-1 -right-1',
    '2xl' => 'w-4 h-4 -bottom-1 -right-1',
];

$statusColors = [
    'online' => 'bg-success dark:bg-dark-success border-2 border-white dark:border-dark-card',
    'offline' => 'bg-secondary dark:bg-dark-secondary border-2 border-white dark:border-dark-card',
    'away' => 'bg-warning dark:bg-dark-warning border-2 border-white dark:border-dark-card',
    'busy' => 'bg-danger dark:bg-dark-danger border-2 border-white dark:border-dark-card',
];

$initials = $name ? collect(explode(' ', $name))
    ->map(fn($word) => strtoupper(substr($word, 0, 1)))
    ->take(2)
    ->join('') : '?';

$gradient = match(strtolower($initials[0] ?? 'x')) {
    'a', 'b' => 'from-blue-400 to-blue-600',
    'c', 'd' => 'from-green-400 to-green-600',
    'e', 'f' => 'from-yellow-400 to-yellow-600',
    'g', 'h' => 'from-orange-400 to-orange-600',
    'i', 'j' => 'from-red-400 to-red-600',
    'k', 'l' => 'from-purple-400 to-purple-600',
    'm', 'n' => 'from-pink-400 to-pink-600',
    'o', 'p' => 'from-indigo-400 to-indigo-600',
    'q', 'r' => 'from-teal-400 to-teal-600',
    's', 't' => 'from-cyan-400 to-cyan-600',
    default => 'from-gray-400 to-gray-600',
};
@endphp

<div class="relative inline-flex shrink-0">
    @if($src)
        <img src="{{ $src }}" alt="{{ $name }}"
             class="{{ $sizes[$size] ?? $sizes['md'] }} rounded-full object-cover">
    @else
        <div class="{{ $sizes[$size] ?? $sizes['md'] }} rounded-full bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white font-semibold">
            {{ $initials }}
        </div>
    @endif

    @if($status && $status !== 'none')
        <span class="absolute {{ $statusSizes[$size] ?? $statusSizes['md'] }} rounded-full {{ $statusColors[$status] ?? '' }}"></span>
    @endif
</div>
