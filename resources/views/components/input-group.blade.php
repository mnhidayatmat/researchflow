@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'type' => 'text',
    'placeholder' => null,
    'value' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'prepend' => null,
    'append' => null,
    'icon' => null,
])

@php
$inputId = $id ?? $name ?? 'input-' . uniqid();
$hasError = $error !== null;
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-primary">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if($prepend)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <span class="text-sm text-secondary">{{ $prepend }}</span>
            </div>
        @endif

        @if($icon)
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $inputId }}"
            name="{{ $name }}"
            @if($value !== null) value="{{ $value }}" @endif
            @if($placeholder) placeholder="{{ $placeholder }}" @endif
            @if($disabled) disabled @endif
            @if($readonly) readonly @endif
            {{ $attributes->merge(['class' => 'w-full text-sm rounded-lg border ' .
                ($hasError ? 'border-danger focus:ring-danger/20 focus:border-danger' : 'border-border focus:ring-accent/20 focus:border-accent') .
                ' bg-white px-3.5 py-2.5 text-primary placeholder:text-tertiary' .
                ($prepend || $icon ? ' pl-' . (strlen($prepend) > 2 ? '10' : '10') : '') .
                ($append ? ' pr-20' : '') .
                ' focus:outline-none focus:ring-2 transition-all duration-150']) })
        >

        @if($append)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <span class="text-sm text-secondary">{{ $append }}</span>
            </div>
        @endif
    </div>

    @if($hasError)
        <p class="text-xs text-danger">{{ $error }}</p>
    @elseif($hint)
        <p class="text-xs text-secondary">{{ $hint }}</p>
    @endif
</div>
