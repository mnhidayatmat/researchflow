<x-layouts.app title="Edit Report">
    <x-slot:header>Edit Report</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            @php
                $usesGoogleDrive = ($storageProfile?->storage_disk ?? 'local') === 'google_drive';
            @endphp

            <form method="POST" action="{{ route('reports.update', [$student, $report]) }}" enctype="multipart/form-data" class="space-y-4" x-data="{ reportType: '{{ old('type', $report->type) }}', submitting: false, submitAction: '' }" @submit="submitting = true">
                @csrf @method('PUT')
                <x-input name="title" label="Title" required :value="$report->title" />
                <x-select name="type" label="Report Type" required :options="$reportTypeOptions" :value="old('type', $report->type)" x-model="reportType" />
                <div x-show="reportType === 'other'" x-cloak>
                    <label for="custom_type" class="block text-sm font-medium text-primary dark:text-dark-primary mb-1">Other Type <span class="text-red-500">*</span></label>
                    <input type="text" name="custom_type" id="custom_type"
                        value="{{ old('custom_type', $report->custom_type) }}"
                        placeholder="e.g. Journal Response Letter"
                        :required="reportType === 'other'"
                        class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 dark:placeholder-dark-secondary/50 focus:border-accent dark:focus:border-dark-accent focus:ring-1 focus:ring-accent/30 dark:focus:ring-dark-accent/30 outline-none transition-colors">
                    @error('custom_type')
                        <p class="mt-1 text-xs text-red-500 dark:text-dark-danger">{{ $message }}</p>
                    @enderror
                </div>

                <x-textarea name="content" label="Report Content" required rows="6">{{ $report->content }}</x-textarea>
                <x-textarea name="achievements" label="Key Achievements" rows="3">{{ $report->achievements }}</x-textarea>
                <x-textarea name="challenges" label="Challenges" rows="3">{{ $report->challenges }}</x-textarea>
                <x-textarea name="next_steps" label="Next Steps" rows="3">{{ $report->next_steps }}</x-textarea>

                <div class="rounded-xl border border-border dark:border-dark-border bg-surface dark:bg-dark-surface p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-primary dark:text-dark-primary">Report Attachment</p>
                            <p class="text-xs text-secondary dark:text-dark-secondary mt-1">
                                @if($storageOwner)
                                    Files will be stored in {{ $storageOwner->name }}'s {{ $usesGoogleDrive ? 'Google Drive' : 'local storage' }}.
                                @else
                                    No supervisor storage owner is assigned yet.
                                @endif
                            </p>
                        </div>
                        <span class="text-[10px] px-2 py-1 rounded-full {{ $usesGoogleDrive ? 'bg-info/10 text-info' : 'bg-secondary/10 text-secondary' }}">
                            {{ $usesGoogleDrive ? 'Google Drive' : 'Local' }}
                        </span>
                    </div>

                    @if($report->attachment_path)
                        <div class="mt-3 flex items-center justify-between rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-primary dark:text-dark-primary">{{ $report->attachment_original_name }}</p>
                                <p class="text-xs text-secondary dark:text-dark-secondary">{{ number_format(($report->attachment_size ?? 0) / 1024, 1) }} KB</p>
                            </div>
                            <a href="{{ route('reports.download-attachment', [$student, $report]) }}" class="text-xs font-medium text-accent hover:underline">Download</a>
                        </div>
                    @endif

                    <div class="mt-3">
                        @if($storageOwner)
                            <input type="file" name="attachment" class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-3 text-sm text-primary dark:text-dark-primary">
                            <p class="text-xs text-secondary dark:text-dark-secondary mt-2">Upload a new file to replace the current attachment.</p>
                        @else
                            <p class="text-xs text-tertiary italic">Attachment upload is unavailable until a supervisor is assigned.</p>
                        @endif
                        @error('attachment')
                            <p class="text-xs text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" name="save" @click="submitAction = 'save'"
                        :disabled="submitting"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg border border-border bg-white hover:bg-gray-50 text-primary transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <svg x-show="submitting && submitAction === 'save'" class="w-4 h-4 animate-spin text-secondary" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span x-text="submitting && submitAction === 'save' ? 'Saving...' : 'Save Draft'"></span>
                    </button>
                    <button type="submit" name="submit" value="1" @click="submitAction = 'submit'"
                        :disabled="submitting"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-accent text-white hover:bg-amber-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
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
