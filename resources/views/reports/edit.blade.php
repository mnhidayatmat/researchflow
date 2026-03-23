<x-layouts.app title="Edit Report">
    <x-slot:header>Edit Report</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            @php
                $usesGoogleDrive = ($storageProfile->storage_disk ?? 'local') === 'google_drive';
            @endphp

            <form method="POST" action="{{ route('reports.update', [$student, $report]) }}" enctype="multipart/form-data" class="space-y-4" x-data="{ reportType: '{{ old('type', $report->type) }}' }">
                @csrf @method('PUT')
                <x-input name="title" label="Title" required :value="$report->title" />
                <x-select name="type" label="Report Type" required :options="$reportTypeOptions" :value="old('type', $report->type)" x-model="reportType" />
                <div x-show="reportType === 'other'" x-cloak>
                    <x-input name="custom_type" label="Other Type" required placeholder="e.g. Journal Response Letter" :value="old('custom_type', $report->custom_type)" />
                </div>

                <x-textarea name="content" label="Report Content" required rows="6">{{ $report->content }}</x-textarea>
                <x-textarea name="achievements" label="Key Achievements" rows="3">{{ $report->achievements }}</x-textarea>
                <x-textarea name="challenges" label="Challenges" rows="3">{{ $report->challenges }}</x-textarea>
                <x-textarea name="next_steps" label="Next Steps" rows="3">{{ $report->next_steps }}</x-textarea>

                <div class="rounded-xl border border-border bg-surface p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-primary">Report Attachment</p>
                            <p class="text-xs text-secondary mt-1">
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
                        <div class="mt-3 flex items-center justify-between rounded-xl border border-border bg-white px-4 py-3">
                            <div>
                                <p class="text-sm font-medium text-primary">{{ $report->attachment_original_name }}</p>
                                <p class="text-xs text-secondary">{{ number_format(($report->attachment_size ?? 0) / 1024, 1) }} KB</p>
                            </div>
                            <a href="{{ route('reports.download-attachment', [$student, $report]) }}" class="text-xs font-medium text-accent hover:underline">Download</a>
                        </div>
                    @endif

                    <div class="mt-3">
                        <input type="file" name="attachment" class="w-full rounded-xl border border-border bg-white px-4 py-3 text-sm text-primary">
                        <p class="text-xs text-secondary mt-2">Upload a new file to replace the current attachment.</p>
                        @error('attachment')
                            <p class="text-xs text-danger mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" name="save" variant="secondary">Save Draft</x-button>
                    <x-button type="submit" name="submit" value="1" variant="accent">Submit to Supervisor</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
