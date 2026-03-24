@props([
    'align' => 'right',
])

@php
$alignClass = $align === 'left' ? 'left-0' : 'right-0';
@endphp

<div x-data="{ open: false }" class="relative">
    <div @click="open = !open" @click.outside="open = false">
        {{ $trigger }}
    </div>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute {{ $alignClass }} mt-1 w-56 bg-white dark:bg-dark-card rounded-xl shadow-medium dark:shadow-dark-medium border border-border dark:border-dark-border py-1 z-50">
        {{ $slot }}
    </div>
</div>
