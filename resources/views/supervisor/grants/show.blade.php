<x-layouts.app :title="$grant->grant_name">
    <x-slot:header>Grant Detail</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-primary dark:text-dark-primary">{{ $grant->proposal_title }}</h2>
                <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm text-secondary dark:text-dark-secondary">{{ $grant->grant_name }} | {{ $grant->grant_type }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-button href="{{ route('supervisor.grants.edit', $grant) }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Edit</x-button>
                <form method="POST" action="{{ route('supervisor.grants.destroy', $grant) }}" onsubmit="return confirm('Delete this grant record?');" class="flex-1 sm:flex-none">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm" class="w-full justify-center sm:w-auto">Delete</x-button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">

                {{-- Grant Details --}}
                <x-card>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Stage</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">
                                {{ \App\Models\Grant::STAGES[$grant->stage] ?? ucfirst($grant->stage) }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Amount</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->formatted_amount }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Duration</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->duration ?: 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">International/National</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ ucfirst($grant->scope) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Submission Date</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->submission_date?->format('j M Y') ?? 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Dateline</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->deadline?->format('j M Y') ?? 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Announcement Date</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->announcement_date?->format('j M Y') ?? 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Rejected Count</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->rejection_count }}</p>
                        </div>
                    </div>

                    @if($grant->notes)
                        <div class="mt-5 border-t border-border dark:border-dark-border pt-4">
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Notes</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-primary dark:text-dark-primary">{{ $grant->notes }}</p>
                        </div>
                    @endif
                </x-card>

                {{-- Documents --}}
                <x-card>
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Grant Documents</h3>
                            <p class="mt-0.5 text-xs text-secondary dark:text-dark-secondary">Supporting documents attached to this grant.</p>
                        </div>
                        <a href="{{ route('supervisor.grants.edit', $grant) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-accent/10 text-accent hover:bg-accent/20 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Upload Document
                        </a>
                    </div>

                    @if($grant->documents->isNotEmpty())
                    <div class="divide-y divide-border dark:divide-dark-border rounded-xl border border-border dark:border-dark-border overflow-hidden">
                        @foreach($grant->documents as $doc)
                        <div class="flex items-center gap-3 px-4 py-3 bg-card dark:bg-dark-card">
                            <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-primary dark:text-dark-primary truncate">{{ $doc->original_name }}</p>
                                <p class="text-xs text-tertiary dark:text-dark-tertiary">{{ $doc->formatted_size }} · {{ $doc->created_at->format('j M Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <a href="{{ route('supervisor.grants.documents.download', [$grant, $doc]) }}"
                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-secondary dark:text-dark-secondary hover:text-primary hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download
                                </a>
                                <form method="POST" action="{{ route('supervisor.grants.documents.destroy', [$grant, $doc]) }}" onsubmit="return confirm('Delete this document?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-danger hover:bg-danger/10 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="rounded-xl border border-dashed border-border dark:border-dark-border py-8 text-center">
                        <svg class="w-8 h-8 text-tertiary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-xs text-tertiary dark:text-dark-tertiary">No documents uploaded yet. <a href="{{ route('supervisor.grants.edit', $grant) }}" class="text-accent hover:underline">Upload one</a>.</p>
                    </div>
                    @endif
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Checklist</h3>
                        <span class="text-xs font-medium text-primary dark:text-dark-primary">{{ $grant->checklist_completion }}%</span>
                    </div>
                    <div class="mb-4 h-2 rounded-full bg-border">
                        <div class="h-2 rounded-full bg-accent" style="width: {{ $grant->checklist_completion }}%"></div>
                    </div>

                    <div class="space-y-3">
                        @forelse($grant->checklistItems as $item)
                            <div class="rounded-xl border border-border dark:border-dark-border bg-surface/60 px-3 py-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full {{ $item->is_completed ? 'bg-success/15 text-success' : 'bg-border text-tertiary' }}">
                                        @if($item->is_completed)
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-primary dark:text-dark-primary">{{ $item->title }}</p>
                                        @if($item->notes)
                                            <p class="mt-1 text-xs leading-5 text-secondary dark:text-dark-secondary">{{ $item->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-secondary dark:text-dark-secondary">No checklist items recorded.</p>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
