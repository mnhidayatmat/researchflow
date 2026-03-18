@props([
    'id' => null,
    'title' => null,
    'subtitle' => null,
    'size' => 'md',
    'show' => false,
    'closeOnEscape' => true,
    'closeOnBackdrop' => true,
])

@php
$id = $id ?? 'modal-' . uniqid();
$sizes = [
    'xs' => 'max-w-sm',
    'sm' => 'max-w-md',
    'md' => 'max-w-lg',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
    'full' => 'max-w-6xl',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }}
     x-data="{ show: {{ $show ? 'true' : 'false' }}, id: '{{ $id }}' }"
     @escape-key.window="{{ $closeOnEscape ? 'show = false' : '' }}">

    {{-- Trigger button if slot named 'trigger' exists --}}
    @if($trigger)
        <div @click="show = true">{{ $trigger }}</div>
    @endif

    {{-- Modal backdrop --}}
    <div x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50"
         @click="{{ $closeOnBackdrop ? 'show = false' : '' }}">
        <div class="absolute inset-0 bg-primary/30 backdrop-blur-sm"></div>
    </div>

    {{-- Modal panel --}}
    <div x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
        <div class="relative {{ $sizeClass }} w-full bg-white rounded-2xl shadow-2xl overflow-hidden"
             @click.stop>

            {{-- Header --}}
            @if($title || $subtitle)
                <div class="flex items-start justify-between px-6 py-5 border-b border-border">
                    <div>
                        @if($title)
                            <h3 class="text-lg font-semibold text-primary">{{ $title }}</h3>
                        @endif
                        @if($subtitle)
                            <p class="text-sm text-secondary mt-1">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button @click="show = false"
                            class="p-1.5 -mr-1.5 text-secondary hover:text-primary rounded-lg hover:bg-surface transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 {{ $title || $subtitle ? 'py-5' : 'py-6' }}">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if(isset($footer))
                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-surface border-t border-border">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
