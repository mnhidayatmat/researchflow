<div
    x-data="{
        journalIndex: '{{ old('journal_index', $publication->journal_index) }}',
        authors: @js(
            collect(old('authors', $publication->authors?->map(fn($a) => [
                'name'        => $a->name,
                'email'       => $a->email,
                'department'  => $a->department,
                'institution' => $a->institution,
            ])->all()) ?? [])
                ->values()
                ->all()
        ),
        addAuthor() {
            this.authors.push({ name: '', email: '', department: '', institution: '' });
        },
        removeAuthor(index) {
            this.authors.splice(index, 1);
        },
        rejections: @js(
            collect(range(1, 3))
                ->filter(fn($r) => old("rejected_{$r}_date") || optional($publication->{"rejected_{$r}_date"})->format('Y-m-d') || old("rejected_{$r}_reviewer_input") || $publication->{"rejected_{$r}_reviewer_input"})
                ->values()
                ->map(fn($r) => (int) $r)
                ->all()
        ) ?: [],
        get maxRounds() { return 3; },
        get canAdd() { return this.rejections.length < this.maxRounds; },
        addRound() {
            if (!this.canAdd) return;
            const next = this.rejections.length === 0 ? 1 : Math.max(...this.rejections) + 1;
            this.rejections.push(next);
        },
        removeRound(round) {
            this.rejections = this.rejections.filter(r => r !== round);
        },
    }"
    class="grid gap-5 lg:grid-cols-2"
>
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

    <div>
        <x-select
            name="journal_index"
            label="Journal Index"
            :options="$journalIndexes"
            :value="old('journal_index', $publication->journal_index)"
            :error="$errors->first('journal_index')"
            required
            placeholder="Select journal index"
            x-model="journalIndex"
        />
    </div>

    <div x-show="journalIndex === 'others'" x-transition class="lg:col-span-2">
        <x-input
            name="journal_index_other"
            label="Others, Please Specify"
            :value="old('journal_index_other', $publication->journal_index_other)"
            :required="old('journal_index', $publication->journal_index) === 'others'"
            placeholder="Enter index name"
        />
    </div>

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

    {{-- Authors --}}
    <div class="lg:col-span-2 mt-4">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Authors</h3>
                <p class="mt-0.5 text-xs text-secondary dark:text-dark-secondary">Add co-authors with their affiliation details. Authors with matching emails will see this publication in their list.</p>
            </div>
            <button
                type="button"
                @click="addAuthor()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-info/10 text-info hover:bg-info/20 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Author
            </button>
        </div>

        <div class="space-y-3">
            <template x-if="authors.length === 0">
                <div class="rounded-xl border border-dashed border-border dark:border-dark-border py-8 text-center">
                    <svg class="w-8 h-8 text-tertiary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-xs text-tertiary">No authors added. Click <strong>Add Author</strong> to add co-authors.</p>
                </div>
            </template>

            <template x-for="(author, index) in authors" :key="index">
                <div class="rounded-xl border border-border dark:border-dark-border bg-surface/50 dark:bg-dark-surface/50 p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-info/10 text-info text-xs font-bold" x-text="index + 1"></span>
                            <span class="text-sm font-medium text-primary dark:text-dark-primary">Author</span>
                        </div>
                        <button
                            type="button"
                            @click="removeAuthor(index)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-danger hover:bg-danger/10 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Remove
                        </button>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Full Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                :name="`authors[${index}][name]`"
                                x-model="author.name"
                                placeholder="e.g. Dr. Ahmad bin Ali"
                                class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none"
                                required
                            >
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Email</label>
                            <input
                                type="email"
                                :name="`authors[${index}][email]`"
                                x-model="author.email"
                                placeholder="author@university.edu"
                                class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none"
                            >
                            <p class="mt-0.5 text-[10px] text-tertiary">Matching users will see this publication automatically.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Department</label>
                            <input
                                type="text"
                                :name="`authors[${index}][department]`"
                                x-model="author.department"
                                placeholder="e.g. Computer Science"
                                class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none"
                            >
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Institution / Affiliation</label>
                            <input
                                type="text"
                                :name="`authors[${index}][institution]`"
                                x-model="author.institution"
                                placeholder="e.g. Universiti Teknologi Malaysia"
                                class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none"
                            >
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Rejection Tracking --}}
    <div class="lg:col-span-2 mt-4">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Rejection Tracking</h3>
                <p class="mt-0.5 text-xs text-secondary dark:text-dark-secondary">Store rejection dates and reviewer comments for each submission round.</p>
            </div>
            <button
                type="button"
                x-show="canAdd"
                @click="addRound()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium
                       bg-accent/10 text-accent hover:bg-accent/20 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Rejection Round
            </button>
        </div>

        <div class="space-y-4">
            <template x-if="rejections.length === 0">
                <div class="rounded-xl border border-dashed border-border dark:border-dark-border py-8 text-center">
                    <svg class="w-8 h-8 text-tertiary mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-xs text-tertiary">No rejection rounds added. Click <strong>Add Rejection Round</strong> to track rejections.</p>
                </div>
            </template>

            @for($round = 1; $round <= 3; $round++)
            <fieldset
                x-show="rejections.includes({{ $round }})"
                :disabled="!rejections.includes({{ $round }})"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="rounded-xl border border-border dark:border-dark-border bg-surface/50 dark:bg-dark-surface/50 p-4"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-danger/10 text-danger text-xs font-bold">{{ $round }}</span>
                        <span class="text-sm font-medium text-primary dark:text-dark-primary">Rejection Round {{ $round }}</span>
                    </div>
                    <button
                        type="button"
                        @click="removeRound({{ $round }})"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium
                               text-danger hover:bg-danger/10 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Remove
                    </button>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <x-input
                        name="rejected_{{ $round }}_date"
                        label="Rejection Date"
                        type="date"
                        :value="old('rejected_' . $round . '_date', optional($publication->{'rejected_' . $round . '_date'})->format('Y-m-d'))"
                    />

                    <div class="lg:col-span-2">
                        <x-textarea
                            name="rejected_{{ $round }}_reviewer_input"
                            label="Reviewer Feedback"
                            rows="4"
                            placeholder="Paste reviewer comments or notes here…"
                        >{{ old('rejected_' . $round . '_reviewer_input', $publication->{'rejected_' . $round . '_reviewer_input'}) }}</x-textarea>
                    </div>
                </div>
            </fieldset>
            @endfor
        </div>
    </div>

    <div class="lg:col-span-2 mt-2 flex items-center justify-end gap-3">
        <x-button href="{{ route('supervisor.publications.index') }}" variant="secondary">Cancel</x-button>
        <x-button type="submit" variant="primary">{{ $submitLabel }}</x-button>
    </div>
</div>
