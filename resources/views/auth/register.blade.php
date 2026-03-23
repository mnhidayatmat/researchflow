@php
    $roles = [
        'student' => 'Student',
        'supervisor' => 'Supervisor / Lecturer'
    ];
@endphp

<x-layouts.guest title="Register">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent shadow-[0_18px_45px_rgba(217,119,6,0.28)]">
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-primary">ResearchFlow</h1>
        <p class="mt-2 text-sm text-secondary">Academic supervision workspace for students, supervisors, and administrators.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Create your account</h2>

        <form method="POST" action="/register" class="space-y-4" id="registerForm">
            @csrf

            <!-- Role Selection -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">I am a <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex cursor-pointer">
                        <input type="radio" name="role" value="student" class="peer sr-only" required checked>
                        <div class="w-full rounded-lg border-2 border-gray-200 p-3 text-center transition-all peer-checked:border-accent peer-checked:bg-accent/5 hover:border-gray-300">
                            <svg class="mx-auto h-6 w-6 text-gray-400 peer-checked:text-accent mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                            <span class="text-sm font-medium">Student</span>
                        </div>
                    </label>
                    <label class="relative flex cursor-pointer">
                        <input type="radio" name="role" value="supervisor" class="peer sr-only">
                        <div class="w-full rounded-lg border-2 border-gray-200 p-3 text-center transition-all peer-checked:border-accent peer-checked:bg-accent/5 hover:border-gray-300">
                            <svg class="mx-auto h-6 w-6 text-gray-400 peer-checked:text-accent mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            <span class="text-sm font-medium">Supervisor / Lecturer</span>
                        </div>
                    </label>
                </div>
                @error('role') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
            </div>

            <x-input name="name" label="Full Name" required />
            <x-input name="email" type="email" label="Email" required />
            <x-input name="university_name" label="University Name" required />

            <!-- Student Fields -->
            <div id="studentFields" class="space-y-4">
                <x-input name="matric_number" label="Matric Number" />
                <x-select name="programme_id" label="Programme" :options="$programmes->pluck('name', 'id')->toArray()" />
                <x-input name="supervisor_email" type="email" label="Supervisor Email" />
                <x-input name="cosupervisor_email" type="email" label="Co-Supervisor Email" />
            </div>

            <!-- Supervisor Fields -->
            <div id="supervisorFields" class="space-y-4 hidden">
                <x-input name="staff_id" label="Staff ID" />
                <x-input name="department" label="Department" />
                <x-input name="faculty" label="Faculty" />
            </div>

            <x-input name="phone" label="Phone" />

            <x-input name="password" type="password" label="Password" required />
            <x-input name="password_confirmation" type="password" label="Confirm Password" required />

            <x-button type="submit" variant="primary" class="w-full">Register</x-button>
        </form>
    </div>

    <p class="text-center text-sm text-gray-500 mt-4">
        Already have an account? <a href="/login" class="text-accent hover:underline">Sign in</a>
    </p>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleInputs = document.querySelectorAll('input[name="role"]');
            const studentFields = document.getElementById('studentFields');
            const supervisorFields = document.getElementById('supervisorFields');

            roleInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value === 'student') {
                        studentFields.classList.remove('hidden');
                        supervisorFields.classList.add('hidden');
                        // Make student fields required
                        studentFields.querySelectorAll('input, select').forEach(el => el.required = true);
                        supervisorFields.querySelectorAll('input, select').forEach(el => el.required = false);
                    } else {
                        studentFields.classList.add('hidden');
                        supervisorFields.classList.remove('hidden');
                        // Make supervisor fields required
                        studentFields.querySelectorAll('input, select').forEach(el => el.required = false);
                        supervisorFields.querySelectorAll('input, select').forEach(el => el.required = true);
                    }
                });
            });

            // Initialize based on default selection
            studentFields.querySelectorAll('input, select').forEach(el => el.required = true);
        });
    </script>
</x-layouts.guest>
