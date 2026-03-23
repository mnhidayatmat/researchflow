<div class="grid gap-5 lg:grid-cols-2">
    <div class="lg:col-span-2">
        <x-input
            name="title"
            label="Title"
            :value="old('title', $publication->title)"
            required
            maxlength="500"
        />
    </div>

    <x-input
        name="journal"
        label="Journal"
        :value="old('journal', $publication->journal)"
        required
    />

    <x-select
        name="journal_index"
        label="Journal Index"
        :options="$journalIndexes"
        :value="old('journal_index', $publication->journal_index)"
        :error="$errors->first('journal_index')"
        required
        placeholder="Select journal index"
    />

    <x-input
        name="journal_index_other"
        label="Others, Please Specify"
        :value="old('journal_index_other', $publication->journal_index_other)"
    />

    <x-select
        name="stage"
        label="Stage"
        :options="$stages"
        :value="old('stage', $publication->stage ?: 'draft')"
        :error="$errors->first('stage')"
        required
        placeholder="Select stage"
    />

    <x-select
        name="quartile"
        label="Quartile"
        :options="$quartiles"
        :value="old('quartile', $publication->quartile)"
        :error="$errors->first('quartile')"
        placeholder="Select quartile"
    />

    <x-input
        name="impact_factor"
        label="Impact Factor (IF)"
        type="number"
        step="0.001"
        min="0"
        :value="old('impact_factor', $publication->impact_factor)"
    />

    <x-input
        name="submission_date"
        label="Submission Date"
        type="date"
        :value="old('submission_date', optional($publication->submission_date)->format('Y-m-d'))"
    />
</div>

<div class="mt-8">
    <div class="mb-4">
        <h3 class="text-sm font-semibold text-primary">Rejection Tracking</h3>
        <p class="mt-1 text-xs text-secondary">Store rejection dates and reviewer comments for each submission round.</p>
    </div>

    <div class="space-y-5">
        @for($round = 1; $round <= 3; $round++)
            <div class="rounded-xl border border-border bg-surface/50 p-4">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-input
                        name="rejected_{{ $round }}_date"
                        label="Rejected {{ $round }} Date"
                        type="date"
                        :value="old('rejected_' . $round . '_date', optional($publication->{'rejected_' . $round . '_date'})->format('Y-m-d'))"
                    />

                    <div class="lg:col-span-2">
                        <x-textarea
                            name="rejected_{{ $round }}_reviewer_input"
                            label="Reviewer Input {{ $round }}"
                            rows="4"
                        >{{ old('rejected_' . $round . '_reviewer_input', $publication->{'rejected_' . $round . '_reviewer_input'}) }}</x-textarea>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <x-button href="{{ route('supervisor.publications.index') }}" variant="secondary">Cancel</x-button>
    <x-button type="submit" variant="primary">{{ $submitLabel }}</x-button>
</div>
