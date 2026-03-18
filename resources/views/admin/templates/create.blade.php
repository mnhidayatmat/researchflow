<x-layouts.app title="New Template">
    <x-slot:header>Journey Templates</x-slot:header>

    <div class="max-w-3xl">
        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-secondary mb-5">
            <a href="{{ route('admin.templates.index') }}" class="hover:text-primary transition-colors">Templates</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-primary">New Template</span>
        </nav>

        <form method="POST" action="{{ route('admin.templates.store') }}" x-data="templateBuilder()">
            @csrf

            {{-- Template basics --}}
            <x-card title="Template Details" class="mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input label="Template Name" name="name" required placeholder="e.g. Standard PhD Journey" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-select
                            label="Programme (Optional)"
                            name="programme_id"
                            :options="$programmes->pluck('name', 'id')->toArray()"
                        />
                    </div>
                    <div class="sm:col-span-2">
                        <x-textarea
                            label="Description"
                            name="description"
                            :rows="2"
                            placeholder="Brief description of this template..."
                        />
                    </div>
                </div>
            </x-card>

            {{-- Dynamic stages builder --}}
            <x-card title="Stages & Milestones" :padding='false' class="mb-6">
                <div class="p-5 border-b border-border bg-surface/50">
                    <p class="text-xs text-secondary">Define the research journey stages in order. Each stage can have multiple milestones.</p>
                </div>

                {{-- Stages list --}}
                <div class="divide-y divide-border">
                    <template x-for="(stage, stageIndex) in stages" :key="stage.id">
                        <div class="p-5">
                            {{-- Stage header --}}
                            <div class="flex items-start gap-3 mb-4">
                                <div class="w-7 h-7 rounded-full bg-surface border border-border text-xs font-medium text-secondary flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span x-text="stageIndex + 1"></span>
                                </div>
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-medium text-secondary mb-1">Stage Name <span class="text-red-500">*</span></label>
                                        <input
                                            type="text"
                                            :name="`stages[${stageIndex}][name]`"
                                            x-model="stage.name"
                                            required
                                            placeholder="e.g. Proposal Stage"
                                            class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                        >
                                        <input type="hidden" :name="`stages[${stageIndex}][order]`" :value="stageIndex + 1">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-secondary mb-1">Duration (weeks)</label>
                                        <input
                                            type="number"
                                            :name="`stages[${stageIndex}][duration_weeks]`"
                                            x-model="stage.duration_weeks"
                                            min="1"
                                            placeholder="e.g. 12"
                                            class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                        >
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removeStage(stageIndex)"
                                    x-show="stages.length > 1"
                                    class="p-1.5 text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors flex-shrink-0 mt-0.5"
                                    title="Remove stage"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            {{-- Milestones --}}
                            <div class="ml-10">
                                <template x-for="(milestone, mIndex) in stage.milestones" :key="milestone.id">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-border flex-shrink-0"></div>
                                        <input
                                            type="text"
                                            :name="`stages[${stageIndex}][milestones][${mIndex}][name]`"
                                            x-model="milestone.name"
                                            required
                                            placeholder="Milestone name..."
                                            class="flex-1 rounded-lg border border-border bg-white px-3 py-1.5 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                        >
                                        <input type="hidden" :name="`stages[${stageIndex}][milestones][${mIndex}][order]`" :value="mIndex + 1">
                                        <input
                                            type="number"
                                            :name="`stages[${stageIndex}][milestones][${mIndex}][weight]`"
                                            x-model="milestone.weight"
                                            min="1"
                                            max="100"
                                            placeholder="Weight"
                                            title="Weight (1-100)"
                                            class="w-20 rounded-lg border border-border bg-white px-3 py-1.5 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                                        >
                                        <button
                                            type="button"
                                            @click="removeMilestone(stageIndex, mIndex)"
                                            class="p-1 text-secondary hover:text-red-600 hover:bg-red-50 rounded transition-colors flex-shrink-0"
                                            title="Remove milestone"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                                <button
                                    type="button"
                                    @click="addMilestone(stageIndex)"
                                    class="mt-1 flex items-center gap-1.5 text-xs text-secondary hover:text-accent transition-colors"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Add milestone
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Add stage button --}}
                <div class="p-4 border-t border-border">
                    <button
                        type="button"
                        @click="addStage()"
                        class="w-full flex items-center justify-center gap-2 py-2.5 text-sm text-secondary hover:text-accent border border-dashed border-border hover:border-accent rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Stage
                    </button>
                </div>
            </x-card>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <x-button href="{{ route('admin.templates.index') }}" variant="secondary">Cancel</x-button>
                <x-button type="submit" variant="primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Create Template
                </x-button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function templateBuilder() {
            return {
                stages: [
                    {
                        id: Date.now(),
                        name: '',
                        duration_weeks: '',
                        milestones: [{ id: Date.now() + 1, name: '', weight: 10 }]
                    }
                ],
                addStage() {
                    this.stages.push({
                        id: Date.now(),
                        name: '',
                        duration_weeks: '',
                        milestones: [{ id: Date.now() + 1, name: '', weight: 10 }]
                    });
                },
                removeStage(index) {
                    if (this.stages.length > 1) {
                        this.stages.splice(index, 1);
                    }
                },
                addMilestone(stageIndex) {
                    this.stages[stageIndex].milestones.push({
                        id: Date.now(),
                        name: '',
                        weight: 10
                    });
                },
                removeMilestone(stageIndex, mIndex) {
                    this.stages[stageIndex].milestones.splice(mIndex, 1);
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
