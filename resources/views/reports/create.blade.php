<x-layouts.app title="Create Report">
    <x-slot:header>Create Report</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            <form method="POST" action="{{ route('reports.store', $student) }}" enctype="multipart/form-data" class="space-y-4" x-data="{ reportType: '{{ old('type', 'progress_report') }}', submitting: false, submitAction: '' }" @submit="submitting = true">
                @php
                    $usesGoogleDrive = ($storageProfile?->storage_disk ?? 'local') === 'google_drive';
                @endphp
                @csrf
                <x-input name="title" label="Title" required placeholder="e.g. Week 12 Progress Report" />
                <x-select name="type" label="Report Type" required :options="$reportTypeOptions" :value="old('type', 'progress_report')" x-model="reportType" />
                <div x-show="reportType === 'other'" x-cloak>
                    <x-input name="custom_type" label="Other Type" required placeholder="e.g. Conference Abstract" :value="old('custom_type')" />
                </div>

                <x-textarea name="content" label="Report Content" required rows="6" placeholder="Describe your progress during this period..." />
                <x-textarea name="achievements" label="Key Achievements" rows="3" placeholder="What did you accomplish?" />
                <x-textarea name="challenges" label="Challenges" rows="3" placeholder="What challenges did you face?" />
                <x-textarea name="next_steps" label="Next Steps" rows="3" placeholder="What are your plans for the next period?" />

                <div class="rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-primary dark:text-dark-primary">Report Attachment</p>
                            <p class="text-xs text-secondary dark:text-dark-secondary mt-1">
                                @if($storageOwner)
                                    Files stored in {{ $storageOwner->name }}'s {{ $usesGoogleDrive ? 'Google Drive' : 'local storage' }}.
                                @else
                                    No supervisor storage owner assigned yet.
                                @endif
                            </p>
                        </div>
                        <span class="text-[10px] px-2 py-1 rounded-full shrink-0 {{ $usesGoogleDrive ? 'bg-info/10 text-info' : 'bg-secondary/10 text-secondary' }}">
                            {{ $usesGoogleDrive ? 'Google Drive' : 'Local' }}
                        </span>
                    </div>
                    <div class="mt-3">
                        @if($storageOwner)
                            <input type="file" name="attachment" class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-3 text-sm text-primary dark:text-dark-primary">
                        @else
                            <p class="text-xs text-tertiary italic">Attachment upload is unavailable until a supervisor is assigned.</p>
                        @endif
                        @error('attachment')
                            <p class="text-xs text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <button type="submit" name="save" @click="submitAction = 'save'"
                        :disabled="submitting"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto order-2 sm:order-1 px-4 py-2 text-sm font-medium rounded-lg border border-border bg-white hover:bg-gray-50 text-primary transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg x-show="submitting && submitAction === 'save'" class="w-4 h-4 animate-spin text-secondary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="submitting && submitAction === 'save' ? 'Saving...' : 'Save Draft'"></span>
                    </button>
                    <button type="submit" name="submit" value="1" @click="submitAction = 'submit'"
                        :disabled="submitting"
                        class="inline-flex items-center justify-center gap-2 w-full sm:w-auto order-1 sm:order-2 px-4 py-2 text-sm font-medium rounded-lg bg-accent text-white hover:bg-amber-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg x-show="submitting && submitAction === 'submit'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="submitting && submitAction === 'submit' ? 'Submitting...' : 'Submit to Supervisor'"></span>
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
