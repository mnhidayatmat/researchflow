<x-layouts.app :title="$grant->grant_name">
    <x-slot:header>Grant Detail</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-base font-semibold text-primary">{{ $grant->proposal_title }}</h2>
                <p class="mt-1 text-sm text-secondary">{{ $grant->grant_name }} | {{ $grant->grant_type }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('supervisor.grants.edit', $grant) }}" variant="secondary">Edit</x-button>
                <form method="POST" action="{{ route('supervisor.grants.destroy', $grant) }}" onsubmit="return confirm('Delete this grant record?');">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">Delete</x-button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Stage</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $grant->stage }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Amount</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $grant->formatted_amount }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Duration</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $grant->duration ?: 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">International/National</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ ucfirst($grant->scope) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Submission Date</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $grant->submission_date?->format('j M Y') ?? 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Dateline</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $grant->deadline?->format('j M Y') ?? 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Announcement Date</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $grant->announcement_date?->format('j M Y') ?? 'TBA' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Rejected Count</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $grant->rejection_count }}</p>
                        </div>
                    </div>

                    @if($grant->notes)
                        <div class="mt-5 border-t border-border pt-4">
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Notes</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-primary">{{ $grant->notes }}</p>
                        </div>
                    @endif
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card>
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-primary">Checklist</h3>
                        <span class="text-xs font-medium text-primary">{{ $grant->checklist_completion }}%</span>
                    </div>
                    <div class="mb-4 h-2 rounded-full bg-border">
                        <div class="h-2 rounded-full bg-accent" style="width: {{ $grant->checklist_completion }}%"></div>
                    </div>

                    <div class="space-y-3">
                        @forelse($grant->checklistItems as $item)
                            <div class="rounded-xl border border-border bg-surface/60 px-3 py-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 flex h-5 w-5 items-center justify-center rounded-full {{ $item->is_completed ? 'bg-success/15 text-success' : 'bg-border text-tertiary' }}">
                                        @if($item->is_completed)
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-primary">{{ $item->title }}</p>
                                        @if($item->notes)
                                            <p class="mt-1 text-xs leading-5 text-secondary">{{ $item->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-secondary">No checklist items recorded.</p>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
