<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-card>
            <div class="grid gap-4 md:grid-cols-2">
                <x-input name="name" label="Name" :value="old('name', $collaborator->name)" required />
                <x-select
                    name="category"
                    label="Category"
                    :options="$categoryOptions"
                    :value="old('category', $collaborator->category)"
                    :error="$errors->first('category')"
                    required
                />
                <x-input
                    name="category_other"
                    label="Other Category"
                    :value="old('category_other', $collaborator->category_other)"
                    placeholder="Please specify"
                />
                <x-input name="institution_name" label="University / Organization" :value="old('institution_name', $collaborator->institution_name)" required />
                <x-input name="department" label="Department" :value="old('department', $collaborator->department)" />
                <x-input name="faculty" label="Faculty" :value="old('faculty', $collaborator->faculty)" />
                <x-input name="position_title" label="Position / Title" :value="old('position_title', $collaborator->position_title)" placeholder="Professor, R&D Lead, Consultant..." />
                <x-input name="country" label="Country" :value="old('country', $collaborator->country)" />
                <x-input name="expertise_area" label="Expertise" :value="old('expertise_area', $collaborator->expertise_area)" placeholder="Machine Learning, Policy Analysis..." />
                <x-input name="research_field" label="Field" :value="old('research_field', $collaborator->research_field)" placeholder="Computer Science, Biotechnology..." />
                <x-input name="working_email" label="Working Email" type="email" :value="old('working_email', $collaborator->working_email)" required />
                <x-input name="phone_number" label="Phone Number" :value="old('phone_number', $collaborator->phone_number)" />
            </div>

            <div class="mt-4">
                <x-textarea name="notes" label="Notes" rows="5" placeholder="Why this person is relevant, prior collaborations, reviewer concerns, grant fit, publication fit...">{{ old('notes', $collaborator->notes) }}</x-textarea>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card>
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-semibold text-primary">Usage Tags</h3>
                    <p class="mt-0.5 text-xs text-secondary">Mark how this contact can help across grants, papers, and reviewer suggestions.</p>
                </div>

                <label class="flex items-start gap-3 rounded-xl border border-border bg-surface/60 px-3 py-3">
                    <input type="hidden" name="suitable_for_grant" value="0">
                    <input type="checkbox" name="suitable_for_grant" value="1" @checked(old('suitable_for_grant', $collaborator->suitable_for_grant)) class="mt-1 rounded border-gray-300 text-accent focus:ring-accent">
                    <div>
                        <p class="text-sm font-medium text-primary">Grant collaborator</p>
                        <p class="text-xs text-secondary">Use for research grants, panels, and funding applications.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 rounded-xl border border-border bg-surface/60 px-3 py-3">
                    <input type="hidden" name="suitable_for_publication" value="0">
                    <input type="checkbox" name="suitable_for_publication" value="1" @checked(old('suitable_for_publication', $collaborator->suitable_for_publication)) class="mt-1 rounded border-gray-300 text-accent focus:ring-accent">
                    <div>
                        <p class="text-sm font-medium text-primary">Paper co-researcher</p>
                        <p class="text-xs text-secondary">Useful for article collaboration, co-authorship, or domain support.</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 rounded-xl border border-border bg-surface/60 px-3 py-3">
                    <input type="hidden" name="suggested_reviewer" value="0">
                    <input type="checkbox" name="suggested_reviewer" value="1" @checked(old('suggested_reviewer', $collaborator->suggested_reviewer)) class="mt-1 rounded border-gray-300 text-accent focus:ring-accent">
                    <div>
                        <p class="text-sm font-medium text-primary">Suggested reviewer</p>
                        <p class="text-xs text-secondary">Keep this record available when journals or grants request reviewer suggestions.</p>
                    </div>
                </label>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-primary">Guidance</h3>
                <p class="text-xs leading-5 text-secondary">Use one record per person. Category helps filtering, while expertise and field make it easier to shortlist collaborators and reviewers later.</p>
                <div class="rounded-xl bg-surface px-3 py-3 text-xs text-secondary">
                    For non-academic contacts, use `University / Organization` for company, agency, or institute name.
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <x-button href="{{ route('supervisor.collaborators.index') }}" variant="secondary">Cancel</x-button>
    <x-button type="submit" variant="primary">{{ $submitLabel }}</x-button>
</div>
