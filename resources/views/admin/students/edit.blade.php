<x-layouts.app :title="'Edit — ' . $student->user->name">
    <x-slot:header>Edit Student</x-slot:header>

    <div class="max-w-3xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-secondary dark:text-dark-secondary mb-4 sm:mb-5 overflow-x-auto">
            <a href="{{ route('admin.students.index') }}" class="hover:text-primary dark:hover:text-dark-primary transition-colors shrink-0">Students</a>
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <a href="{{ route('admin.students.show', $student) }}" class="hover:text-primary dark:hover:text-dark-primary transition-colors truncate">{{ $student->user->name }}</a>
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-primary dark:text-dark-primary shrink-0">Edit</span>
        </nav>

        <form method="POST" action="{{ route('admin.students.update', $student) }}">
            @csrf
            @method('PUT')

            {{-- Personal Information --}}
            <x-card title="Personal Information" class="mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input
                            label="Full Name"
                            name="name"
                            required
                            :value="old('name', $student->user->name)"
                        />
                    </div>
                    <x-input
                        label="Email Address"
                        name="email"
                        type="email"
                        required
                        :value="old('email', $student->user->email)"
                    />
                    <x-input
                        label="Matric Number"
                        name="matric_number"
                        :value="old('matric_number', $student->user->matric_number)"
                    />
                </div>
                <div class="mt-4 pt-4 border-t border-border dark:border-dark-border">
                    <p class="text-xs font-medium text-secondary dark:text-dark-secondary mb-3">Change Password <span class="font-normal">(leave blank to keep current)</span></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-input label="New Password" name="password" type="password" placeholder="Min. 8 characters" />
                        <x-input label="Confirm Password" name="password_confirmation" type="password" placeholder="Repeat new password" />
                    </div>
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
                            :selected="old('programme_id', $student->programme_id)"
                        />
                    </div>
                    <x-select
                        label="Main Supervisor"
                        name="supervisor_id"
                        required
                        :options="$supervisors->pluck('name', 'id')->toArray()"
                        :selected="old('supervisor_id', $student->supervisor_id)"
                    />
                    <x-select
                        label="Co-Supervisor (Optional)"
                        name="cosupervisor_id"
                        :options="$supervisors->pluck('name', 'id')->toArray()"
                        :selected="old('cosupervisor_id', $student->cosupervisor_id)"
                    />
                    <div class="sm:col-span-2">
                        <x-input
                            label="Research Title"
                            name="research_title"
                            :value="old('research_title', $student->research_title)"
                        />
                    </div>
                    <div>
                        <label for="intake" class="block text-sm font-medium text-primary dark:text-dark-primary mb-1">Intake</label>
                        <select name="intake" id="intake" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors">
                            <option value="">Select intake...</option>
                            @for($year = now()->year + 1; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}/1" {{ old('intake', $student->intake) === "$year/1" ? 'selected' : '' }}>{{ $year }} Semester 1</option>
                                <option value="{{ $year }}/2" {{ old('intake', $student->intake) === "$year/2" ? 'selected' : '' }}>{{ $year }} Semester 2</option>
                            @endfor
                        </select>
                        @error('intake')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-primary dark:text-dark-primary mb-1">Status</label>
                        <select name="status" id="status" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors">
                            @foreach(['pending' => 'Pending', 'active' => 'Active', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'withdrawn' => 'Withdrawn'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status', $student->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-card>

            {{-- Timeline --}}
            <x-card title="Timeline" class="mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input
                        label="Start Date"
                        name="start_date"
                        type="date"
                        :value="old('start_date', $student->start_date?->toDateString())"
                    />
                    <x-input
                        label="Expected Completion"
                        name="expected_completion"
                        type="date"
                        :value="old('expected_completion', $student->expected_completion?->toDateString())"
                    />
                </div>
            </x-card>

            {{-- Actions --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Are you sure you want to delete this student? This action cannot be undone.')" class="order-last sm:order-first">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm" class="w-full justify-center sm:w-auto">Delete Student</x-button>
                </form>
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3">
                    <x-button href="{{ route('admin.students.show', $student) }}" variant="secondary" class="w-full justify-center sm:w-auto">Cancel</x-button>
                    <x-button type="submit" variant="primary" class="w-full justify-center sm:w-auto">Save Changes</x-button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
