<x-layouts.app title="Add Student">
    <x-slot:header>Add Student</x-slot:header>

    <div class="max-w-3xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary mb-4 sm:mb-5">
            <a href="{{ route('admin.students.index') }}" class="hover:text-primary dark:hover:text-dark-primary transition-colors">Students</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-primary dark:text-dark-primary">New Student</span>
        </nav>

        <form method="POST" action="{{ route('admin.students.store') }}">
            @csrf

            {{-- Personal Information --}}
            <x-card title="Personal Information" class="mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input label="Full Name" name="name" required placeholder="e.g. Ahmad Fauzi bin Abdullah" />
                    </div>
                    <x-input label="Email Address" name="email" type="email" required placeholder="student@university.edu.my" />
                    <x-input label="Matric Number" name="matric_number" required placeholder="e.g. P12345" />
                    <x-input label="Password" name="password" type="password" required placeholder="Minimum 8 characters" />
                    <x-input label="Confirm Password" name="password_confirmation" type="password" required placeholder="Repeat password" />
                </div>
            </x-card>

            {{-- Academic Information --}}
            <x-card title="Academic Information" class="mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-select
                            label="Programme"
                            name="programme_id"
                            required
                            :options="$programmes->pluck('name', 'id')->toArray()"
                        />
                    </div>
                    <x-select
                        label="Main Supervisor"
                        name="supervisor_id"
                        required
                        :options="$supervisors->pluck('name', 'id')->toArray()"
                    />
                    <x-select
                        label="Co-Supervisor (Optional)"
                        name="cosupervisor_id"
                        :options="$supervisors->pluck('name', 'id')->toArray()"
                    />
                    <div class="sm:col-span-2">
                        <x-input
                            label="Research Title"
                            name="research_title"
                            placeholder="Provisional research title (can be updated later)"
                        />
                    </div>
                    <div>
                        <label for="intake" class="block text-sm font-medium text-primary dark:text-dark-primary mb-1">Intake</label>
                        <select name="intake" id="intake" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors">
                            <option value="">Select intake...</option>
                            @for($year = now()->year + 1; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}/1" {{ old('intake') === "$year/1" ? 'selected' : '' }}>{{ $year }} Semester 1</option>
                                <option value="{{ $year }}/2" {{ old('intake') === "$year/2" ? 'selected' : '' }}>{{ $year }} Semester 2</option>
                            @endfor
                        </select>
                        @error('intake')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            {{-- Timeline --}}
            <x-card title="Timeline" class="mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="Start Date" name="start_date" type="date" :value="old('start_date', now()->toDateString())" />
                    <x-input label="Expected Completion" name="expected_completion" type="date" />
                </div>
            </x-card>

            {{-- Actions --}}
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <x-button href="{{ route('admin.students.index') }}" variant="secondary" class="w-full justify-center sm:w-auto">Cancel</x-button>
                <x-button type="submit" variant="primary" class="w-full justify-center sm:w-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Student
                </x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
