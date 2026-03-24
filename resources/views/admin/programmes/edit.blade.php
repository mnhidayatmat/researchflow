<x-layouts.app :title="'Edit — ' . $programme->name">
    <x-slot:header>Programmes</x-slot:header>

    <div class="max-w-xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary mb-4 sm:mb-5">
            <a href="{{ route('admin.programmes.index') }}" class="hover:text-primary dark:hover:text-dark-primary transition-colors">Programmes</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-primary dark:text-dark-primary truncate">{{ $programme->name }}</span>
        </nav>

        <form method="POST" action="{{ route('admin.programmes.update', $programme) }}">
            @csrf
            @method('PUT')

            <x-card title="Programme Details" class="mb-6">
                <div class="space-y-4">
                    <x-input
                        label="Programme Name"
                        name="name"
                        required
                        :value="old('name', $programme->name)"
                    />
                    <x-input
                        label="Programme Code"
                        name="code"
                        required
                        :value="old('code', $programme->code)"
                    />
                    <div>
                        <label for="duration_months" class="block text-sm font-medium text-primary dark:text-dark-primary mb-1">
                            Duration <span class="text-secondary dark:text-dark-secondary font-normal">(months)</span>
                        </label>
                        <input
                            type="number"
                            name="duration_months"
                            id="duration_months"
                            value="{{ old('duration_months', $programme->duration_months) }}"
                            min="1"
                            max="120"
                            class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                        >
                        @error('duration_months')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <x-textarea
                        label="Description"
                        name="description"
                        :rows="3"
                    >{{ old('description', $programme->description) }}</x-textarea>
                </div>
            </x-card>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form method="POST" action="{{ route('admin.programmes.destroy', $programme) }}" onsubmit="return confirm('Delete this programme? This cannot be undone.')" class="order-last sm:order-first">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm" class="w-full justify-center sm:w-auto">Delete Programme</x-button>
                </form>
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3">
                    <x-button href="{{ route('admin.programmes.index') }}" variant="secondary" class="w-full justify-center sm:w-auto">Cancel</x-button>
                    <x-button type="submit" variant="primary" class="w-full justify-center sm:w-auto">Save Changes</x-button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
