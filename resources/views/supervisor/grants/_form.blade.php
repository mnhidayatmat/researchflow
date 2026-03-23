@php
    $initialChecklistItems = old('checklist_items', $checklistItems ?? []);
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-card>
            <div class="grid gap-4 md:grid-cols-2">
                <x-input name="proposal_title" label="Proposal Title" :value="old('proposal_title', $grant->proposal_title)" required />
                <x-input name="grant_name" label="Grant Name" :value="old('grant_name', $grant->grant_name)" required />
                <x-input name="grant_type" label="Grant Type" :value="old('grant_type', $grant->grant_type)" placeholder="Fundamental, Applied, etc." required />
                <x-input name="duration" label="Duration" :value="old('duration', $grant->duration)" placeholder="36 months" />
                <x-select
                    name="scope"
                    label="International/National"
                    :options="['international' => 'International', 'national' => 'National']"
                    :value="old('scope', $grant->scope)"
                />
                <x-input name="amount" label="Amount (RM)" type="number" step="0.01" min="0" :value="old('amount', $grant->amount)" placeholder="300000" />
                <x-input name="stage" label="Stage" :value="old('stage', $grant->stage)" placeholder="Draft, Submitted, Rejected, Waiting to open" required />
                <x-input name="rejection_count" label="Rejected Count" type="number" min="0" max="4" :value="old('rejection_count', $grant->rejection_count ?? 0)" />
                <x-input name="submission_date" label="Submission Date" type="date" :value="old('submission_date', $grant->submission_date?->format('Y-m-d'))" />
                <x-input name="deadline" label="Dateline" type="date" :value="old('deadline', $grant->deadline?->format('Y-m-d'))" />
                <x-input name="announcement_date" label="Announcement Date" type="date" :value="old('announcement_date', $grant->announcement_date?->format('Y-m-d'))" />
            </div>

            <div class="mt-4">
                <x-textarea name="notes" label="Notes" rows="5" placeholder="Internal remarks, call details, assessor feedback, revision strategy...">{{ old('notes', $grant->notes) }}</x-textarea>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card>
            <div x-data="{
                items: @js($initialChecklistItems),
                addItem() { this.items.push({ title: '', is_completed: false, notes: '' }); },
                removeItem(index) { this.items.splice(index, 1); }
            }">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-primary">Submission Checklist</h3>
                        <p class="mt-0.5 text-xs text-secondary">Track what is ready before funding submission.</p>
                    </div>
                    <button type="button" @click="addItem()" class="rounded-lg border border-border px-3 py-1.5 text-xs font-medium text-primary hover:bg-surface">Add Item</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-2xl border border-border bg-surface/60 p-3">
                            <div class="flex items-start gap-3">
                                <input type="hidden" :name="`checklist_items[${index}][is_completed]`" value="0">
                                <input :name="`checklist_items[${index}][is_completed]`" x-model="item.is_completed" value="1" type="checkbox" class="mt-1 rounded border-gray-300 text-accent focus:ring-accent">
                                <div class="min-w-0 flex-1 space-y-2">
                                    <input :name="`checklist_items[${index}][title]`" x-model="item.title" type="text" placeholder="Checklist item" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                                    <textarea :name="`checklist_items[${index}][notes]`" x-model="item.notes" rows="2" placeholder="Optional notes" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none"></textarea>
                                </div>
                                <button type="button" @click="removeItem(index)" class="text-xs text-secondary hover:text-red-600">Remove</button>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="items.length === 0">
                    <p class="rounded-xl border border-dashed border-border px-3 py-6 text-center text-xs text-secondary">No checklist items yet.</p>
                </template>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-primary">Guidance</h3>
                <p class="text-xs leading-5 text-secondary">Use this module to track each grant call, its submission timeline, status, and readiness checklist in one place.</p>
                <div class="rounded-xl bg-surface px-3 py-3 text-xs text-secondary">
                    Recommended stages: `Draft`, `Waiting to open`, `Preparing`, `Submitted`, `Under Review`, `Awarded`, `Rejected`.
                </div>
            </div>
        </x-card>
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <x-button href="{{ route('supervisor.grants.index') }}" variant="secondary">Cancel</x-button>
    <x-button type="submit" variant="primary">{{ $submitLabel }}</x-button>
</div>
