<x-layouts.app title="{{ $report->title }}">
    <x-slot:header>Report Detail</x-slot:header>

    <div class="max-w-3xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-5 sm:mb-6">
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-semibold text-primary dark:text-dark-primary">{{ $report->title }}</h2>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $report->type_label }} &middot; {{ $report->created_at->format('d M Y') }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-status-badge :status="$report->status" />
                @if(in_array($report->status, ['draft', 'revision_needed']) && auth()->id() === $student->user_id)
                    <x-button href="{{ route('reports.edit', [$student, $report]) }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Edit</x-button>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <x-card title="Content">
                <div class="text-sm text-secondary dark:text-dark-secondary leading-relaxed whitespace-pre-wrap">{{ $report->content }}</div>
            </x-card>

            @if($report->achievements)
                <x-card title="Key Achievements">
                    <div class="text-sm text-secondary dark:text-dark-secondary leading-relaxed whitespace-pre-wrap">{{ $report->achievements }}</div>
                </x-card>
            @endif

            @if($report->challenges)
                <x-card title="Challenges">
                    <div class="text-sm text-secondary dark:text-dark-secondary leading-relaxed whitespace-pre-wrap">{{ $report->challenges }}</div>
                </x-card>
            @endif

            @if($report->next_steps)
                <x-card title="Next Steps">
                    <div class="text-sm text-secondary dark:text-dark-secondary leading-relaxed whitespace-pre-wrap">{{ $report->next_steps }}</div>
                </x-card>
            @endif

            @php $canManageAttachment = auth()->id() === $student->user_id || auth()->user()->isAdmin(); @endphp

            <x-card title="Attachment">
                @if($report->attachment_path)
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-primary dark:text-dark-primary truncate">{{ $report->attachment_original_name }}</p>
                            <p class="text-xs text-secondary dark:text-dark-secondary mt-1">
                                {{ number_format(($report->attachment_size ?? 0) / 1024, 1) }} KB
                                @if($report->attachmentStorageOwner)
                                    &middot; Stored in {{ $report->attachmentStorageOwner->name }}'s {{ $report->attachment_disk === 'google_drive' ? 'Google Drive' : 'local storage' }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-button href="{{ route('reports.download-attachment', [$student, $report]) }}" variant="secondary" size="sm">Download</x-button>
                            @if($canManageAttachment)
                                <form method="POST" action="{{ route('reports.remove-attachment', [$student, $report]) }}" onsubmit="return confirm('Remove this attachment?')">
                                    @csrf @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">Remove</x-button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($canManageAttachment)
                        <div x-data="{ replacing: false, submitting: false }" class="mt-4 pt-4 border-t border-border dark:border-dark-border">
                            <button @click="replacing = !replacing" x-show="!replacing" type="button" class="text-xs text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Replace with new file
                            </button>
                            <form x-show="replacing" x-cloak method="POST" action="{{ route('reports.replace-attachment', [$student, $report]) }}" enctype="multipart/form-data" @submit="submitting = true" class="space-y-3">
                                @csrf
                                <input type="file" name="attachment" required class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-3 text-sm text-primary dark:text-dark-primary">
                                <div class="flex items-center gap-2">
                                    <button type="submit" :disabled="submitting" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-accent text-white hover:bg-amber-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                                        <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span x-text="submitting ? 'Uploading...' : 'Upload'"></span>
                                    </button>
                                    <button @click="replacing = false" type="button" class="px-4 py-2 text-sm font-medium rounded-lg border border-border text-secondary hover:text-primary hover:bg-surface transition-colors">Cancel</button>
                                </div>
                                @error('attachment')
                                    <p class="text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    @endif
                @else
                    @if($canManageAttachment)
                        <div x-data="{ submitting: false }">
                            <form method="POST" action="{{ route('reports.replace-attachment', [$student, $report]) }}" enctype="multipart/form-data" @submit="submitting = true" class="space-y-3">
                                @csrf
                                <p class="text-xs text-secondary dark:text-dark-secondary mb-3">No attachment yet. Upload a file for this report.</p>
                                <input type="file" name="attachment" required class="w-full rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card px-4 py-3 text-sm text-primary dark:text-dark-primary">
                                <button type="submit" :disabled="submitting" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-accent text-white hover:bg-amber-600 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span x-text="submitting ? 'Uploading...' : 'Upload Attachment'"></span>
                                </button>
                                @error('attachment')
                                    <p class="text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </form>
                        </div>
                    @else
                        <p class="text-sm text-secondary dark:text-dark-secondary">No attachment.</p>
                    @endif
                @endif
            </x-card>

            {{-- Supervisor feedback --}}
            @if($report->supervisor_feedback)
                <x-card title="Supervisor Feedback">
                    <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 text-sm">
                        <p class="font-medium text-amber-800 text-xs mb-1">{{ $report->reviewer?->name }} &middot; {{ $report->reviewed_at?->format('d M Y') }}</p>
                        <div class="text-amber-900 whitespace-pre-wrap">{{ $report->supervisor_feedback }}</div>
                    </div>
                </x-card>
            @endif

            {{-- Review form (for supervisors) --}}
            @if($report->status === 'submitted' && (auth()->id() === $student->supervisor_id || auth()->id() === $student->cosupervisor_id || auth()->user()->isAdmin()))
                <x-card title="Review">
                    <form method="POST" action="{{ route('reports.review', [$student, $report]) }}" class="space-y-4">
                        @csrf
                        <x-textarea name="supervisor_feedback" label="Feedback" required rows="4" placeholder="Provide your feedback..." />
                        <div class="flex items-center gap-3">
                            <x-button type="submit" name="decision" value="accepted" variant="accent">Accept</x-button>
                            <x-button type="submit" name="decision" value="revision_needed" variant="secondary">Request Revision</x-button>
                        </div>
                    </form>
                </x-card>
            @endif
        </div>
    </div>
</x-layouts.app>
