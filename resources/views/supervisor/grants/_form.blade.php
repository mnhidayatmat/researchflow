@php
    $initialChecklistItems = old('checklist_items', $checklistItems ?? []);
@endphp

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <x-card>
            <div class="grid gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <x-input name="proposal_title" label="Proposal Title" :value="old('proposal_title', $grant->proposal_title)" required />
                </div>
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
                <x-select
                    name="stage"
                    label="Stage"
                    :options="$stages"
                    :value="old('stage', $grant->stage ?: 'draft')"
                    :error="$errors->first('stage')"
                    required
                    placeholder="Select stage"
                />
                <x-input name="rejection_count" label="Rejected Count" type="number" min="0" max="4" :value="old('rejection_count', $grant->rejection_count ?? 0)" />
                <x-input name="submission_date" label="Submission Date" type="date" :value="old('submission_date', $grant->submission_date?->format('Y-m-d'))" />
                <x-input name="deadline" label="Dateline" type="date" :value="old('deadline', $grant->deadline?->format('Y-m-d'))" />
                <x-input name="announcement_date" label="Announcement Date" type="date" :value="old('announcement_date', $grant->announcement_date?->format('Y-m-d'))" />
            </div>

            <div class="mt-4">
                <x-textarea name="notes" label="Notes" rows="5" placeholder="Internal remarks, call details, assessor feedback, revision strategy...">{{ old('notes', $grant->notes) }}</x-textarea>
            </div>
        </x-card>

        {{-- Documents (only available when editing an existing grant) --}}
        @if($grant->exists)
        <x-card>
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Grant Documents</h3>
                    <p class="mt-0.5 text-xs text-secondary dark:text-dark-secondary">Attach supporting documents such as proposals, call letters, or award letters.</p>
                </div>
            </div>

            {{-- Upload form --}}
            <form
                method="POST"
                action="{{ route('supervisor.grants.documents.store', $grant) }}"
                enctype="multipart/form-data"
                x-data="{ fileName: '' }"
                class="mb-5"
            >
                @csrf
                <div class="flex items-center gap-3">
                    <label
                        class="flex flex-1 cursor-pointer items-center gap-3 rounded-xl border border-dashed border-border dark:border-dark-border
                               bg-surface/60 dark:bg-dark-surface/60 px-4 py-3 hover:border-accent/50 transition-colors"
                    >
                        <svg class="w-5 h-5 text-accent shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span class="text-sm text-secondary dark:text-dark-secondary" x-text="fileName || 'Choose file to upload…'"></span>
                        <input
                            type="file"
                            name="document"
                            class="sr-only"
                            accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip"
                            @change="fileName = $event.target.files[0]?.name || ''"
                            required
                        >
                    </label>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-accent px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-700 transition-colors shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload
                    </button>
                </div>
                @error('document')
                    <p class="mt-1.5 text-xs text-danger">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-[10px] text-tertiary dark:text-dark-tertiary">Accepted: PDF, Word, Excel, PowerPoint, images, ZIP — max 20 MB</p>
            </form>

            {{-- Uploaded files list --}}
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
                        <a
                            href="{{ route('supervisor.grants.documents.download', [$grant, $doc]) }}"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface transition-colors"
                        >
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
                <p class="text-xs text-tertiary dark:text-dark-tertiary">No documents uploaded yet.</p>
            </div>
            @endif
        </x-card>
        @else
        <div class="rounded-xl border border-dashed border-border dark:border-dark-border px-5 py-4">
            <p class="text-xs text-secondary dark:text-dark-secondary">
                <svg class="w-4 h-4 inline-block mr-1 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Document uploads are available after saving the grant record.
            </p>
        </div>
        @endif
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
                        <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Submission Checklist</h3>
                        <p class="mt-0.5 text-xs text-secondary dark:text-dark-secondary">Track what is ready before funding submission.</p>
                    </div>
                    <button type="button" @click="addItem()" class="rounded-lg border border-border dark:border-dark-border px-3 py-1.5 text-xs font-medium text-primary dark:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface">Add Item</button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="rounded-2xl border border-border dark:border-dark-border bg-surface/60 p-3">
                            <div class="flex items-start gap-3">
                                <input type="hidden" :name="`checklist_items[${index}][is_completed]`" value="0">
                                <input :name="`checklist_items[${index}][is_completed]`" x-model="item.is_completed" value="1" type="checkbox" class="mt-1 rounded border-gray-300 dark:border-dark-border text-accent focus:ring-accent">
                                <div class="min-w-0 flex-1 space-y-2">
                                    <input :name="`checklist_items[${index}][title]`" x-model="item.title" type="text" placeholder="Checklist item" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                                    <textarea :name="`checklist_items[${index}][notes]`" x-model="item.notes" rows="2" placeholder="Optional notes" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2 text-xs focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none"></textarea>
                                </div>
                                <button type="button" @click="removeItem(index)" class="text-xs text-secondary dark:text-dark-secondary hover:text-red-600">Remove</button>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="items.length === 0">
                    <p class="rounded-xl border border-dashed border-border dark:border-dark-border px-3 py-6 text-center text-xs text-secondary dark:text-dark-secondary">No checklist items yet.</p>
                </template>
            </div>
        </x-card>

        <x-card>
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Guidance</h3>
                <p class="text-xs leading-5 text-secondary dark:text-dark-secondary">Use this module to track each grant call, its submission timeline, status, and readiness checklist in one place.</p>
            </div>
        </x-card>
    </div>
</div>

<div class="mt-6 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
    <x-button href="{{ route('supervisor.grants.index') }}" variant="secondary" class="w-full justify-center sm:w-auto">Cancel</x-button>
    <x-button type="submit" variant="primary" class="w-full justify-center sm:w-auto">{{ $submitLabel }}</x-button>
</div>
