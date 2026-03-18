@props([
    'label' => null,
    'name' => null,
    'id' => null,
    'options' => [],
    'placeholder' => 'Select...',
    'value' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
])

@php
$selectId = $id ?? $name ?? 'select-' . uniqid();
$hasError = $error !== null;
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $selectId }}" class="block text-sm font-medium text-primary">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <select
        id="{{ $selectId }}"
        name="{{ $name }}"
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => 'w-full text-sm rounded-lg border ' .
            ($hasError ? 'border-danger focus:ring-danger/20 focus:border-danger' : 'border-border focus:ring-accent/20 focus:border-accent') .
            ' bg-white px-3.5 py-2.5 text-primary focus:outline-none focus:ring-2 transition-all duration-150']) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $key => $option)
            @if(is_array($option))
                <option value="{{ $option['value'] ?? $key }}" @if((string) $value === (string) ($option['value'] ?? $key)) selected @endif>
                    {{ $option['label'] ?? $key }}
                </option>
            @else
                <option value="{{ $key }}" @if((string) $value === (string) $key) selected @endif>{{ $option }}</option>
            @endif
        @endforeach
    </select>

    @if($hasError)
        <p class="text-xs text-danger">{{ $error }}</p>
    @elseif($hint)
        <p class="text-xs text-secondary">{{ $hint }}</p>
    @endif
</div>
