<x-layouts.app title="Publications">
    <x-slot:header>Publications</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Publication Tracker</h2>
                <p class="mt-0.5 text-xs text-secondary dark:text-dark-secondary">Track submissions, rejections, and reviewer feedback.</p>
            </div>
            <x-button href="{{ route('supervisor.publications.create') }}" variant="primary" class="w-full justify-center sm:w-auto">New Publication</x-button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Total Records</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $stats['total'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Published</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $stats['published'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Under Review</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $stats['under_review'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Revision Required</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $stats['revision_required'] }}</p>
            </x-card>
        </div>

        <x-card>
            <form method="GET" action="{{ route('supervisor.publications.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="flex-1 min-w-0 sm:min-w-[220px]">
                    <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Title or journal..." class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <div class="grid grid-cols-3 gap-3 sm:contents">
                    <div class="sm:min-w-[150px]">
                        <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Stage</label>
                        <select name="stage" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                            <option value="">All</option>
                            @foreach($stages as $value => $label)
                                <option value="{{ $value }}" @selected(request('stage') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:min-w-[140px]">
                        <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Index</label>
                        <select name="journal_index" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                            <option value="">All</option>
                            @foreach($journalIndexes as $value => $label)
                                <option value="{{ $value }}" @selected(request('journal_index') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:min-w-[130px]">
                        <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Quartile</label>
                        <select name="quartile" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                            <option value="">All</option>
                            @foreach($quartiles as $value => $label)
                                <option value="{{ $value }}" @selected(request('quartile') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <x-button type="submit" variant="primary" size="sm" class="flex-1 justify-center sm:flex-none">Filter</x-button>
                    @if(request()->hasAny(['search', 'stage', 'journal_index', 'quartile']))
                        <x-button href="{{ route('supervisor.publications.index') }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Clear</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        @if($publications->isEmpty())
            <x-card>
                <div class="py-12 text-center">
                    <p class="text-base font-semibold text-primary dark:text-dark-primary">No publication records yet</p>
                    <p class="mt-1 text-sm text-secondary dark:text-dark-secondary">Create your first publication record to track submission and rejection history.</p>
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
                        <td class="px-5 py-4 text-sm text-secondary dark:text-dark-secondary">{{ $publications->firstItem() + $index }}</td>
                        <td class="px-5 py-4 align-top">
                            <p class="font-medium text-primary dark:text-dark-primary">{{ $publication->title }}</p>
                            @if($publication->user_id !== auth()->id())
                                <span class="inline-flex items-center gap-1 mt-1 rounded-full bg-info/10 px-2 py-0.5 text-[10px] font-medium text-info">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    Co-Author · by {{ $publication->user->name }}
                                </span>
                            @endif
                            @if($publication->authors->count() > 0)
                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @foreach($publication->authors as $author)
                                        <span title="{{ collect([$author->department, $author->institution])->filter()->implode(', ') ?: 'No affiliation' }}"
                                              class="inline-flex items-center gap-1 rounded-full bg-surface dark:bg-dark-surface border border-border dark:border-dark-border px-2 py-0.5 text-[10px] text-secondary dark:text-dark-secondary">
                                            <svg class="w-2.5 h-2.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            {{ $author->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publication->journal }}</td>
                        <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publication->journal_index_label }}</td>
                        <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publication->quartile ?? 'N/A' }}</td>
                        <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publication->impact_factor ? number_format((float) $publication->impact_factor, 3) : 'N/A' }}</td>
                        <td class="px-5 py-4 align-top">
                            <span class="inline-flex rounded-full bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent">{{ $publication->stage_label }}</span>
                        </td>
                        <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publication->submission_date?->format('d M Y') ?? 'N/A' }}</td>
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
                                    <span class="text-secondary dark:text-dark-secondary">No</span>
                                @endif
                            </td>
                        @endfor
                        <td class="px-5 py-4 align-top">
                            @if($publication->user_id === auth()->id())
                            <div class="flex items-center justify-end gap-2">
                                <x-button href="{{ route('supervisor.publications.edit', $publication) }}" variant="secondary" size="sm">Edit</x-button>
                                <form method="POST" action="{{ route('supervisor.publications.destroy', $publication) }}" onsubmit="return confirm('Delete this publication record?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">Delete</x-button>
                                </form>
                            </div>
                            @else
                            <span class="text-xs text-tertiary dark:text-dark-tertiary">View only</span>
                            @endif
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
