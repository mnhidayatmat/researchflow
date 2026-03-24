<x-layouts.app title="Publication Track">
    <x-slot:header>Publication Track</x-slot:header>

    @php
        $canManage = auth()->user()->can('update', $student);
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-secondary dark:text-dark-secondary">{{ $student->user->name }}'s journal submission and rejection tracker</p>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Reviewer input can be stored for each rejection round to improve the manuscript.</p>
            </div>

            @if($canManage)
                <x-button href="{{ route('publications.create', $student) }}" variant="accent" size="sm">+ Add Publication</x-button>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <x-card>
                <p class="text-xs uppercase tracking-wide text-secondary dark:text-dark-secondary">Total Records</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $publicationTracks->total() }}</p>
            </x-card>
            <x-card>
                <p class="text-xs uppercase tracking-wide text-secondary dark:text-dark-secondary">Published</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $student->publicationTracks()->where('stage', 'published')->count() }}</p>
            </x-card>
            <x-card>
                <p class="text-xs uppercase tracking-wide text-secondary dark:text-dark-secondary">Under Review</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $student->publicationTracks()->where('stage', 'under_review')->count() }}</p>
            </x-card>
            <x-card>
                <p class="text-xs uppercase tracking-wide text-secondary dark:text-dark-secondary">Revision Required</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $student->publicationTracks()->where('stage', 'revision_required')->count() }}</p>
            </x-card>
        </div>

        <x-table
            :headers="[
                ['label' => 'No'],
                ['label' => 'Title'],
                ['label' => 'Journal'],
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
            @forelse($publicationTracks as $index => $publicationTrack)
                <tr class="hover:bg-surface/60 transition-colors">
                    <td class="px-5 py-4 text-sm text-secondary dark:text-dark-secondary">{{ $publicationTracks->firstItem() + $index }}</td>
                    <td class="px-5 py-4 align-top">
                        <p class="font-medium text-primary dark:text-dark-primary">{{ $publicationTrack->title }}</p>
                    </td>
                    <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publicationTrack->journal }}</td>
                    <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publicationTrack->quartile ?? 'N/A' }}</td>
                    <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publicationTrack->impact_factor ? number_format((float) $publicationTrack->impact_factor, 3) : 'N/A' }}</td>
                    <td class="px-5 py-4 align-top">
                        <span class="inline-flex rounded-full bg-accent/10 px-2.5 py-1 text-xs font-medium text-accent">{{ $publicationTrack->stage_label }}</span>
                    </td>
                    <td class="px-5 py-4 align-top text-sm text-primary dark:text-dark-primary">{{ $publicationTrack->submission_date?->format('d M Y') ?? 'N/A' }}</td>
                    @for($round = 1; $round <= 3; $round++)
                        <td class="px-5 py-4 align-top text-sm">
                            @if($publicationTrack->wasRejectedInRound($round))
                                <div class="space-y-1">
                                    <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-600">
                                        {{ $publicationTrack->{'rejected_' . $round . '_date'}?->format('d M Y') }}
                                    </span>
                                    <p class="text-xs {{ $publicationTrack->hasReviewerInputForRound($round) ? 'text-primary' : 'text-secondary' }}">
                                        {{ $publicationTrack->hasReviewerInputForRound($round) ? 'Reviewer input saved' : 'No reviewer input' }}
                                    </p>
                                </div>
                            @else
                                <span class="text-secondary dark:text-dark-secondary">No</span>
                            @endif
                        </td>
                    @endfor
                    <td class="px-5 py-4 align-top">
                        <div class="flex items-center justify-end gap-2">
                            @if($canManage)
                                <x-button href="{{ route('publications.edit', [$student, $publicationTrack]) }}" variant="secondary" size="sm">Edit</x-button>
                                <form method="POST" action="{{ route('publications.destroy', [$student, $publicationTrack]) }}" onsubmit="return confirm('Delete this publication record?');">
                                    @csrf
                                    @method('DELETE')
                                    <x-button type="submit" variant="danger" size="sm">Delete</x-button>
                                </form>
                            @else
                                <span class="text-xs text-secondary dark:text-dark-secondary">View only</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </x-table>

        <div>{{ $publicationTracks->links() }}</div>
    </div>
</x-layouts.app>
