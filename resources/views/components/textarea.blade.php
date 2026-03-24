@props(['label' => null, 'name', 'required' => false, 'rows' => 4])

<div>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-primary dark:text-dark-primary mb-1">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 dark:placeholder-dark-secondary/50 focus:border-accent dark:focus:border-dark-accent focus:ring-1 focus:ring-accent/30 dark:focus:ring-dark-accent/30 outline-none transition-colors resize-y']) }}
    >{{ old($name, $slot) }}</textarea>
    @error($name)
        <p class="mt-1 text-xs text-red-500 dark:text-dark-danger">{{ $message }}</p>
    @enderror
</div>
