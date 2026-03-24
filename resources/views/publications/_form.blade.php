@php
    $isEdit = $publicationTrack->exists;
@endphp

<div class="grid gap-5 lg:grid-cols-2">
    <div class="lg:col-span-2">
        <x-input
            name="title"
            label="Title"
            :value="old('title', $publicationTrack->title)"
            required
            maxlength="500"
        />
    </div>

    <x-input
        name="journal"
        label="Journal"
        :value="old('journal', $publicationTrack->journal)"
        required
    />

    <x-select
        name="stage"
        label="Stage"
        :options="$stages"
        :value="old('stage', $publicationTrack->stage ?: 'draft')"
        :error="$errors->first('stage')"
        required
        placeholder="Select stage"
    />

    <x-select
        name="quartile"
        label="Quartile"
        :options="$quartiles"
        :value="old('quartile', $publicationTrack->quartile)"
        :error="$errors->first('quartile')"
        placeholder="Select quartile"
    />

    <x-input
        name="impact_factor"
        label="Impact Factor (IF)"
        type="number"
        step="0.001"
        min="0"
        :value="old('impact_factor', $publicationTrack->impact_factor)"
    />

    <x-input
        name="submission_date"
        label="Submission Date"
        type="date"
        :value="old('submission_date', optional($publicationTrack->submission_date)->format('Y-m-d'))"
    />
</div>

<div class="mt-8">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Rejection Tracking</h3>
            <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Store rejection dates and reviewer input for manuscript improvement.</p>
        </div>
    </div>

    <div class="space-y-5">
        @for($round = 1; $round <= 3; $round++)
            <div class="rounded-xl border border-border dark:border-dark-border bg-surface/50 p-4">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-input
                        name="rejected_{{ $round }}_date"
                        label="Rejected {{ $round }} Date"
                        type="date"
                        :value="old('rejected_' . $round . '_date', optional($publicationTrack->{'rejected_' . $round . '_date'})->format('Y-m-d'))"
                    />

                    <div class="lg:col-span-2">
                        <x-textarea
                            name="rejected_{{ $round }}_reviewer_input"
                            label="Reviewer Input {{ $round }}"
                            rows="4"
                        >{{ old('rejected_' . $round . '_reviewer_input', $publicationTrack->{'rejected_' . $round . '_reviewer_input'}) }}</x-textarea>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-button type="submit" variant="accent">{{ $isEdit ? 'Update Record' : 'Create Record' }}</x-button>
    <x-button :href="route('publications.index', $student)" variant="secondary">Cancel</x-button>
</div>
