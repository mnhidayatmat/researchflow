@php
    $roles = [
        'student' => 'Student',
        'supervisor' => 'Supervisor / Lecturer'
    ];
@endphp

<x-layouts.guest title="Complete Your Profile">
    <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-accent shadow-[0_18px_45px_rgba(217,119,6,0.28)]">
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <h1 class="text-2xl font-semibold tracking-tight text-primary">Complete Your Profile</h1>
        <p class="mt-2 text-sm text-secondary">Just a few more details to set up your ResearchFlow account.</p>
    </div>

    {{-- Google account info banner --}}
    <div class="flex items-center gap-3 mb-4 px-4 py-3 bg-surface rounded-xl border border-border">
        @if($googleUser['avatar'])
            <img src="{{ $googleUser['avatar'] }}" alt="{{ $googleUser['name'] }}" class="w-9 h-9 rounded-full">
        @else
            <div class="w-9 h-9 rounded-full bg-accent/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
        @endif
        <div class="min-w-0">
            <p class="text-sm font-medium text-primary">{{ $googleUser['name'] }}</p>
            <p class="text-xs text-secondary">{{ $googleUser['email'] }}</p>
        </div>
        <div class="ml-auto shrink-0">
            <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <form method="POST" action="{{ route('auth.google.complete.post') }}" class="space-y-4" id="googleCompleteForm">
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

            <x-input name="name" label="Full Name" required :value="$googleUser['name']" />
            <x-university-select name="university_name" label="University Name" :required="true" :value="old('university_name')" />

            <!-- Student Fields -->
            <div id="studentFields" class="space-y-4">
                <x-input name="matric_number" label="Matric Number" :value="old('matric_number')" />
                <x-select name="student_category" label="Student Category" required
                    :options="['fyp' => 'Final Year Project (FYP)', 'master' => 'Master', 'phd' => 'PhD', 'other' => 'Other (please specify)']"
                    :value="old('student_category')" placeholder="Select category..."
                    :error="$errors->first('student_category')" />
                <div id="otherCategoryField" class="hidden">
                    <x-input name="student_category_other" label="Please specify category" placeholder="e.g. Diploma Project" :value="old('student_category_other')" />
                </div>
                <x-input name="programme_name" label="Programme" placeholder="e.g. Master of Computer Science" :value="old('programme_name')" />
                <x-input name="supervisor_email" type="email" label="Supervisor Email" placeholder="supervisor@university.edu" :value="old('supervisor_email')" />
                <x-input name="cosupervisor_email" type="email" label="Co-Supervisor Email (Optional)" placeholder="cosupervisor@university.edu" :value="old('cosupervisor_email')" />
            </div>

            <!-- Supervisor Fields -->
            <div id="supervisorFields" class="space-y-4 hidden">
                <x-input name="title" label="Title" placeholder="e.g. Dr., Prof., Ts., Ir." :value="old('title')" />
                <x-input name="staff_id" label="Staff ID" :value="old('staff_id')" />
                <x-input name="department" label="Department" :value="old('department')" />
                <x-input name="faculty" label="Faculty" :value="old('faculty')" />
            </div>

            <div>
                <x-input name="phone" label="Phone" placeholder="+601X-XXXXXXXX" :value="old('phone')" />
                <p class="text-[11px] text-gray-400 mt-1">Format: +[country code][number], e.g. +60123456789</p>
            </div>

            @if($errors->any())
                <div class="text-sm text-red-500 space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <x-button type="submit" variant="accent" class="w-full">Complete Registration</x-button>
        </form>
    </div>

    <p class="text-center text-sm text-gray-500 mt-4">
        <a href="{{ route('login') }}" class="text-accent hover:underline">Back to sign in</a>
    </p>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleInputs = document.querySelectorAll('input[name="role"]');
            const studentFields = document.getElementById('studentFields');
            const supervisorFields = document.getElementById('supervisorFields');

            const optionalFields = ['cosupervisor_email', 'matric_number', 'student_category_other'];
            const categorySelect = document.querySelector('select[name="student_category"]');
            const otherCategoryField = document.getElementById('otherCategoryField');
            const otherCategoryInput = otherCategoryField?.querySelector('input');

            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        otherCategoryField.classList.remove('hidden');
                        otherCategoryInput.required = true;
                    } else {
                        otherCategoryField.classList.add('hidden');
                        otherCategoryInput.required = false;
                        otherCategoryInput.value = '';
                    }
                });
                if (categorySelect.value === 'other') {
                    otherCategoryField.classList.remove('hidden');
                    otherCategoryInput.required = true;
                }
            }

            roleInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value === 'student') {
                        studentFields.classList.remove('hidden');
                        supervisorFields.classList.add('hidden');
                        studentFields.querySelectorAll('input, select').forEach(el => {
                            el.required = !optionalFields.includes(el.name);
                        });
                        supervisorFields.querySelectorAll('input, select').forEach(el => el.required = false);
                    } else {
                        studentFields.classList.add('hidden');
                        supervisorFields.classList.remove('hidden');
                        studentFields.querySelectorAll('input, select').forEach(el => el.required = false);
                        supervisorFields.querySelectorAll('input, select').forEach(el => el.required = true);
                    }
                });
            });

            studentFields.querySelectorAll('input, select').forEach(el => {
                el.required = !optionalFields.includes(el.name);
            });
        });
    </script>
</x-layouts.guest>
