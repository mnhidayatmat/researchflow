<x-layouts.app title="Publications">
    <x-slot:header>Publications</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-primary">Publication Tracker</h2>
                <p class="mt-0.5 text-xs text-secondary">Track your submission pipeline, rejection rounds, and reviewer feedback in one place.</p>
            </div>
            <x-button href="{{ route('supervisor.publications.create') }}" variant="primary">New Publication</x-button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-card>
                <p class="text-xs text-secondary">Total Records</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['total'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Published</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['published'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Under Review</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['under_review'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Revision Required</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['revision_required'] }}</p>
            </x-card>
        </div>

        <x-card>
            <form method="GET" action="{{ route('supervisor.publications.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1 block text-xs font-medium text-secondary">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Title or journal..." class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <div class="min-w-[180px]">
                    <label class="mb-1 block text-xs font-medium text-secondary">Stage</label>
                    <select name="stage" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                        <option value="">All</option>
                        @foreach($stages as $value => $label)
                            <option value="{{ $value }}" @selected(request('stage') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="mb-1 block text-xs font-medium text-secondary">Journal Index</label>
                    <select name="journal_index" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                        <option value="">All</option>
                        @foreach($journalIndexes as $value => $label)
                            <option value="{{ $value }}" @selected(request('journal_index') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[160px]">
                    <label class="mb-1 block text-xs font-medium text-secondary">Quartile</label>
                    <select name="quartile" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                        <option value="">All</option>
                        @foreach($quartiles as $value => $label)
                            <option value="{{ $value }}" @selected(request('quartile') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" size="sm">Filter</x-button>
                    @if(request()->hasAny(['search', 'stage', 'journal_index', 'quartile']))
                        <x-button href="{{ route('supervisor.publications.index') }}" variant="secondary" size="sm">Clear</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        @if($publications->isEmpty())
            <x-card>
                <div class="py-12 text-center">
                    <p class="text-base font-semibold text-primary">No publication records yet</p>
                    <p class="mt-1 text-sm text-secondary">Create your first publication record to track submission and rejection history.</p>
                </div>
            </x-card>
        @else
            <x-table
                :headers="[
                    ['label' => 'No'],
                    ['label' => 'Title'],
                    ['label' => 'Journal'],
                    ['label' => 'Index'],
                    ['label' => 'Quartile'],
                    ['label' => 'IF'],
                    ['label' => 'Stage'],
                    ['label' => 'Submission Date'],
                    ['label' => 'Rejected 1'],
                    ['label' => 'Rejected 2'],
                    ['label' => 'Rejected 3'],
                    ['label' => 'Actions', 'key' => 'actions'],
                ]"
                empty="No publication records yet."
            >
                @foreach($publications as $index => $publication)
                    <tr class="hover:bg-surface/60 transition-colors">
                        <td class="px-5 py-4 text-sm text-secondary">{{ $publications->firstItem() + $index }}</td>
                        <td class="px-5 py-4 align-top">
                            <p class="font-medium text-primary">{{ $publication->title }}</p>
                        </td>
                        <td class="px-5 py-4 align-top text-sm text-primary">{{ $publication->journal }}</td>
                        <td class="px-5 py-4 align-top text-sm text-primary">{{ $publication->journal_index_label }}</td>
                        <td class="px-5 py-4 align-top text-sm text-primary">{{ $publication->quartile ?? 'N/A' }}</td>
                        <td class="px-5 py-4 align-top text-sm text-primary">{{ $publication->impact_factor ? number_format((float) $publication->impact_factor, 3) : 'N/A' }}</td>
                        <td class="px-5 py-4 align-top">
                            <span class="inline-flex rounded-full bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent">{{ $publication->stage_label }}</span>
                        </td>
                        <td class="px-5 py-4 align-top text-sm text-primary">{{ $publication->submission_date?->format('d M Y') ?? 'N/A' }}</td>
                        @for($round = 1; $round <= 3; $round++)
                            <td class="px-5 py-4 align-top text-sm">
                                @if($publication->wasRejectedInRound($round))
                                    <div class="space-y-1">
                                        <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600">
                                            {{ $publication->{'rejected_' . $round . '_date'}?->format('d M Y') }}
                                        </span>
                                        <p class="text-xs {{ $publication->hasReviewerInputForRound($round) ? 'text-primary' : 'text-secondary' }}">
                                            {{ $publication->hasReviewerInputForRound($round) ? 'Reviewer input saved' : 'No reviewer input' }}
                                        </p>
                                    </div>
                                @else
                                    <span class="text-secondary">No</span>
                                @endif
                            </td>
                        @endfor
                        <td class="px-5 py-4 align-top">
                            <div class="flex items-center justify-end gap-2">
                                <x-button href="{{ route('supervisor.publications.edit', $publication) }}" variant="secondary" size="sm">Edit</x-button>
                                <form method="POST" action="{{ route('supervisor.publications.destroy', $publication) }}" onsubmit="return confirm('Delete this publication record?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">Delete</x-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            @if($publications->hasPages())
                <div>{{ $publications->withQueryString()->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
