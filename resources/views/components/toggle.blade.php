@props([
    'name' => null,
    'checked' => false,
    'label' => null,
    'description' => null,
    'disabled' => false,
])

<div class="flex items-center justify-between">
    <div>
        @if($label)
            <label for="{{ $name }}" class="text-sm font-medium text-primary">{{ $label }}</label>
        @endif
        @if($description)
            <p class="text-xs text-secondary mt-0.5">{{ $description }}</p>
        @endif
    </div>
    <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox"
               name="{{ $name }}"
               @if($checked) checked @endif
               @if($disabled) disabled @endif
               class="sr-only peer"
               {{ $attributes }}>
        <div class="w-11 h-6 bg-secondary peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-accent/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent {{ $disabled ? 'opacity-50 cursor-not-allowed' : '' }}"></div>
    </label>
</div>
