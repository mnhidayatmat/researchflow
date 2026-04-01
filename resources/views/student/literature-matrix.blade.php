<x-layouts.app title="Literature Matrix">

<div
    x-data="literatureMatrix({
        studentId: {{ $student->id }},
        entries: {{ $entries->toJson() }},
        columns: {{ collect($config->columns)->sortBy('sort_order')->values()->toJson() }},
        csrfToken: '{{ csrf_token() }}'
    })"
    x-init="init()"
    class="flex flex-col h-full"
>

{{-- ── Page header ────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div>
        <h2 class="text-base font-semibold text-primary">Literature Matrix</h2>
        <p class="text-xs text-secondary mt-0.5">
            {{ $student->user->name }} &mdash; Systematic literature review table
        </p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Column manager toggle --}}
        <button @click="showColumnManager = !showColumnManager"
                :class="showColumnManager ? 'bg-accent/10 text-accent border-accent/30' : 'text-secondary border-border hover:bg-surface hover:text-primary'"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium border transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Columns
        </button>

        {{-- Add entry --}}
        <button @click="openAddModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 transition-all shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Entry
        </button>

        {{-- Export --}}
        <a href="{{ route('literature.export', $student) }}"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-secondary border border-border hover:bg-surface hover:text-primary transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/>
            </svg>
            Export Excel
        </a>
    </div>
</div>

{{-- ── Body layout ───────────────────────────────────────────────────────── --}}
<div class="flex gap-4 flex-1 min-h-0">

    {{-- ── Column manager panel ──────────────────────────────────────────── --}}
    <div x-show="showColumnManager"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-2"
         class="w-56 shrink-0 bg-card border border-border rounded-2xl overflow-hidden flex flex-col">

        <div class="px-4 py-3 border-b border-border">
            <p class="text-xs font-semibold text-primary">Manage Columns</p>
            <p class="text-[10px] text-secondary mt-0.5">Drag to reorder, toggle visibility</p>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-0.5" id="column-manager-list">
            <template x-for="(col, idx) in columns" :key="col.key">
                <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-surface group cursor-grab active:cursor-grabbing"
                     :data-key="col.key"
                     draggable="true"
                     @dragstart="colDragStart($event, idx)"
                     @dragover.prevent="colDragOver($event, idx)"
                     @drop.prevent="colDrop($event, idx)"
                     @dragend="colDragEnd()">
                    {{-- Drag handle --}}
                    <svg class="w-3 h-3 text-tertiary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                    {{-- Toggle --}}
                    <button @click="col.visible = !col.visible; saveColumnConfig()"
                            :class="col.visible ? 'bg-accent' : 'bg-border'"
                            class="relative inline-flex h-4 w-7 shrink-0 items-center rounded-full transition-colors">
                        <span :class="col.visible ? 'translate-x-3.5' : 'translate-x-0.5'"
                              class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform shadow-sm"></span>
                    </button>
                    {{-- Label --}}
                    <span class="text-xs text-primary truncate flex-1" x-text="col.label"></span>
                </div>
            </template>
        </div>

        <div class="p-2 border-t border-border">
            <button @click="resetColumns()"
                    class="w-full text-xs text-secondary hover:text-primary py-1.5 rounded-lg hover:bg-surface transition-colors">
                Reset to default
            </button>
        </div>
    </div>

    {{-- ── Table area ──────────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 bg-card border border-border rounded-2xl overflow-hidden flex flex-col">

        {{-- Empty state --}}
        <template x-if="entries.length === 0">
            <div class="flex flex-col items-center justify-center flex-1 py-16 px-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-primary mb-1">No literature entries yet</h3>
                <p class="text-xs text-secondary max-w-xs mb-4">Start building your literature matrix by adding your first paper or article.</p>
                <button @click="openAddModal()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add First Entry
                </button>
            </div>
        </template>

        {{-- Scrollable table --}}
        <template x-if="entries.length > 0">
            <div class="overflow-auto flex-1">
                <table class="w-full text-xs border-collapse" style="min-width: 600px">
                    <thead class="sticky top-0 z-10 bg-surface">
                        <tr>
                            {{-- Row number --}}
                            <th class="text-left px-3 py-2.5 border-b border-border font-semibold text-secondary w-10 text-center">#</th>
                            <template x-for="col in visibleColumns" :key="col.key">
                                <th class="text-left px-3 py-2.5 border-b border-border font-semibold text-secondary whitespace-nowrap select-none"
                                    x-text="col.label"></th>
                            </template>
                            {{-- Actions --}}
                            <th class="text-right px-3 py-2.5 border-b border-border font-semibold text-secondary w-16">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(entry, rowIdx) in entries" :key="entry.id">
                            <tr class="group border-b border-border last:border-0 hover:bg-surface/60 transition-colors"
                                draggable="true"
                                @dragstart="rowDragStart($event, rowIdx)"
                                @dragover.prevent="rowDragOver($event, rowIdx)"
                                @drop.prevent="rowDrop($event, rowIdx)"
                                @dragend="rowDragEnd()"
                                :class="draggingRowIdx === rowIdx ? 'opacity-40' : ''">

                                {{-- Row number + drag handle --}}
                                <td class="px-3 py-2 text-center text-tertiary cursor-grab active:cursor-grabbing">
                                    <span x-text="rowIdx + 1" class="text-[10px]"></span>
                                </td>

                                <template x-for="col in visibleColumns" :key="col.key">
                                    <td class="px-3 py-2 max-w-xs align-top cursor-pointer"
                                        @click="openEditModal(entry, col.key)">
                                        <div class="line-clamp-3 text-primary leading-relaxed"
                                             x-text="entry[col.key] || '—'"
                                             :class="!entry[col.key] ? 'text-tertiary italic' : ''"></div>
                                    </td>
                                </template>

                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <button @click.stop="openEditModal(entry, null)"
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-lg text-secondary hover:text-primary hover:bg-surface transition-all opacity-0 group-hover:opacity-100"
                                            title="Edit entry">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click.stop="deleteEntry(entry)"
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-lg text-secondary hover:text-danger hover:bg-danger/10 transition-all opacity-0 group-hover:opacity-100"
                                            title="Delete entry">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>

        {{-- Footer --}}
        <div x-show="entries.length > 0" class="px-4 py-2.5 border-t border-border flex items-center justify-between bg-surface/40">
            <span class="text-[10px] text-tertiary" x-text="`${entries.length} entr${entries.length === 1 ? 'y' : 'ies'}`"></span>
            <span class="text-[10px] text-tertiary">Click a cell to edit &bull; Drag rows to reorder</span>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════
     Entry form modal (add / edit)
══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="showModal"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @keydown.escape.window="showModal = false"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>

    <div class="relative bg-card rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">

        {{-- Modal header --}}
        <div class="px-6 py-4 border-b border-border flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-sm font-semibold text-primary" x-text="modalMode === 'add' ? 'Add Literature Entry' : 'Edit Literature Entry'"></h3>
                <p class="text-xs text-secondary mt-0.5">Fill in the fields for this paper or article</p>
            </div>
            <button @click="showModal = false" class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal body --}}
        <div class="overflow-y-auto flex-1 p-6 space-y-4">

            {{-- Jump to field (quick-nav tabs) --}}
            <div class="flex flex-wrap gap-1.5 pb-3 border-b border-border">
                <template x-for="col in allColumns" :key="col.key">
                    <button @click="focusField(col.key)"
                            :class="activeField === col.key ? 'bg-accent text-white' : 'bg-surface text-secondary hover:text-primary'"
                            class="px-2.5 py-1 rounded-lg text-[10px] font-medium transition-all"
                            x-text="col.label"></button>
                </template>
            </div>

            {{-- Author + Year row --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-secondary mb-1">Author(s)</label>
                    <input type="text" x-model="form.author" id="field-author"
                           @focus="activeField = 'author'"
                           placeholder="e.g. Smith, J., Jones, A."
                           class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Year</label>
                    <input type="number" x-model="form.year" id="field-year"
                           @focus="activeField = 'year'"
                           placeholder="2024" min="1900" max="2100"
                           class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
            </div>

            {{-- Title --}}
            <div>
                <label class="block text-xs font-medium text-secondary mb-1">Title <span class="text-danger">*</span></label>
                <input type="text" x-model="form.title" id="field-title"
                       @focus="activeField = 'title'"
                       placeholder="Full paper title"
                       class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                <p x-show="formErrors.title" class="text-[10px] text-danger mt-1" x-text="formErrors.title"></p>
            </div>

            {{-- Journal + DOI --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Journal / Source</label>
                    <input type="text" x-model="form.journal" id="field-journal"
                           @focus="activeField = 'journal'"
                           placeholder="Journal name"
                           class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">DOI / URL</label>
                    <input type="text" x-model="form.doi_url" id="field-doi_url"
                           @focus="activeField = 'doi_url'"
                           placeholder="https://doi.org/..."
                           class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
            </div>

            {{-- Research Objective --}}
            <div>
                <label class="block text-xs font-medium text-secondary mb-1">Research Objective</label>
                <textarea x-model="form.research_objective" id="field-research_objective" rows="2"
                          @focus="activeField = 'research_objective'"
                          placeholder="What is the main objective of this paper?"
                          class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
            </div>

            {{-- Methodology --}}
            <div>
                <label class="block text-xs font-medium text-secondary mb-1">Methodology</label>
                <textarea x-model="form.methodology" id="field-methodology" rows="2"
                          @focus="activeField = 'methodology'"
                          placeholder="Research methods used"
                          class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
            </div>

            {{-- Dataset --}}
            <div>
                <label class="block text-xs font-medium text-secondary mb-1">Dataset</label>
                <textarea x-model="form.dataset" id="field-dataset" rows="2"
                          @focus="activeField = 'dataset'"
                          placeholder="Dataset or data source used"
                          class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
            </div>

            {{-- Findings --}}
            <div>
                <label class="block text-xs font-medium text-secondary mb-1">Findings / Results</label>
                <textarea x-model="form.findings" id="field-findings" rows="2"
                          @focus="activeField = 'findings'"
                          placeholder="Key findings and results"
                          class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
            </div>

            {{-- Limitations --}}
            <div>
                <label class="block text-xs font-medium text-secondary mb-1">Limitations</label>
                <textarea x-model="form.limitations" id="field-limitations" rows="2"
                          @focus="activeField = 'limitations'"
                          placeholder="Limitations of the study"
                          class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
            </div>

            {{-- Relevance --}}
            <div>
                <label class="block text-xs font-medium text-secondary mb-1">Relevance to Study</label>
                <textarea x-model="form.relevance" id="field-relevance" rows="2"
                          @focus="activeField = 'relevance'"
                          placeholder="How does this relate to your research?"
                          class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
            </div>

            {{-- Keywords + Notes row --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Keywords</label>
                    <input type="text" x-model="form.keywords" id="field-keywords"
                           @focus="activeField = 'keywords'"
                           placeholder="Comma-separated keywords"
                           class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary mb-1">Notes</label>
                    <input type="text" x-model="form.notes" id="field-notes"
                           @focus="activeField = 'notes'"
                           placeholder="Any additional notes"
                           class="w-full px-3 py-2 text-xs bg-surface border border-border rounded-lg text-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
            </div>
        </div>

        {{-- Modal footer --}}
        <div class="px-6 py-4 border-t border-border flex items-center justify-between shrink-0 bg-surface/40">
            <button x-show="modalMode === 'edit'" @click="deleteEntry(editingEntry); showModal = false"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-danger hover:bg-danger/10 transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
            </button>
            <div x-show="modalMode === 'add'" class="flex-1"></div>
            <div class="flex items-center gap-2">
                <button @click="showModal = false"
                        class="px-4 py-2 rounded-xl text-xs font-medium text-secondary border border-border hover:bg-surface transition-all">
                    Cancel
                </button>
                <button @click="saveEntry()" :disabled="saving"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 disabled:opacity-60 transition-all">
                    <svg x-show="saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving…' : (modalMode === 'add' ? 'Add Entry' : 'Save Changes')"></span>
                </button>
            </div>
        </div>
    </div>
</div>

</div>{{-- /x-data --}}


@push('scripts')
<script>
function literatureMatrix({ studentId, entries, columns, csrfToken }) {
    return {
        studentId,
        entries,
        columns,
        csrfToken,

        showColumnManager: false,
        showModal: false,
        modalMode: 'add', // 'add' | 'edit'
        editingEntry: null,
        activeField: null,
        saving: false,
        formErrors: {},

        form: {
            author: '', year: '', title: '', journal: '', doi_url: '',
            research_objective: '', methodology: '', dataset: '',
            findings: '', limitations: '', relevance: '', keywords: '', notes: '',
        },

        // Drag state — rows
        draggingRowIdx: null,
        draggingRowOver: null,

        // Drag state — columns
        draggingColIdx: null,
        draggingColOver: null,

        // ── Computed ────────────────────────────────────────────────────────

        get visibleColumns() {
            return this.columns.filter(c => c.visible);
        },

        get allColumns() {
            return this.columns;
        },

        // ── Init ────────────────────────────────────────────────────────────

        init() {
            // Renumber sort_order to match array position
            this.columns.forEach((c, i) => c.sort_order = i);
        },

        // ── Column manager ──────────────────────────────────────────────────

        colDragStart(e, idx) {
            this.draggingColIdx = idx;
            e.dataTransfer.effectAllowed = 'move';
        },
        colDragOver(e, idx) {
            this.draggingColOver = idx;
        },
        colDrop(e, idx) {
            if (this.draggingColIdx === null || this.draggingColIdx === idx) return;
            const moved = this.columns.splice(this.draggingColIdx, 1)[0];
            this.columns.splice(idx, 0, moved);
            this.columns.forEach((c, i) => c.sort_order = i);
            this.saveColumnConfig();
            this.draggingColIdx = null;
        },
        colDragEnd() { this.draggingColIdx = null; this.draggingColOver = null; },

        saveColumnConfig() {
            fetch(`/students/${this.studentId}/literature/config`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ columns: this.columns }),
            });
        },

        resetColumns() {
            const defaults = [
                {key:'author',label:'Author(s)',visible:true,sort_order:0},
                {key:'year',label:'Year',visible:true,sort_order:1},
                {key:'title',label:'Title',visible:true,sort_order:2},
                {key:'journal',label:'Journal/Source',visible:true,sort_order:3},
                {key:'doi_url',label:'DOI / URL',visible:true,sort_order:4},
                {key:'research_objective',label:'Research Objective',visible:true,sort_order:5},
                {key:'methodology',label:'Methodology',visible:true,sort_order:6},
                {key:'dataset',label:'Dataset',visible:false,sort_order:7},
                {key:'findings',label:'Findings / Results',visible:true,sort_order:8},
                {key:'limitations',label:'Limitations',visible:false,sort_order:9},
                {key:'relevance',label:'Relevance to Study',visible:true,sort_order:10},
                {key:'keywords',label:'Keywords',visible:false,sort_order:11},
                {key:'notes',label:'Notes',visible:false,sort_order:12},
            ];
            this.columns = defaults;
            this.saveColumnConfig();
        },

        // ── Row drag-to-reorder ──────────────────────────────────────────────

        rowDragStart(e, idx) {
            this.draggingRowIdx = idx;
            e.dataTransfer.effectAllowed = 'move';
        },
        rowDragOver(e, idx) { this.draggingRowOver = idx; },
        rowDrop(e, idx) {
            if (this.draggingRowIdx === null || this.draggingRowIdx === idx) return;
            const moved = this.entries.splice(this.draggingRowIdx, 1)[0];
            this.entries.splice(idx, 0, moved);
            this.draggingRowIdx = null;
            this.draggingRowOver = null;
            // Persist order
            const order = this.entries.map(e => e.id);
            fetch(`/students/${this.studentId}/literature/reorder`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ order }),
            });
        },
        rowDragEnd() { this.draggingRowIdx = null; this.draggingRowOver = null; },

        // ── Modal ────────────────────────────────────────────────────────────

        openAddModal() {
            this.modalMode = 'add';
            this.editingEntry = null;
            this.formErrors = {};
            this.activeField = 'author';
            this.form = { author:'',year:'',title:'',journal:'',doi_url:'',research_objective:'',methodology:'',dataset:'',findings:'',limitations:'',relevance:'',keywords:'',notes:'' };
            this.showModal = true;
            this.$nextTick(() => document.getElementById('field-author')?.focus());
        },

        openEditModal(entry, focusKey) {
            this.modalMode = 'edit';
            this.editingEntry = entry;
            this.formErrors = {};
            this.activeField = focusKey || 'author';
            this.form = {
                author: entry.author || '',
                year: entry.year || '',
                title: entry.title || '',
                journal: entry.journal || '',
                doi_url: entry.doi_url || '',
                research_objective: entry.research_objective || '',
                methodology: entry.methodology || '',
                dataset: entry.dataset || '',
                findings: entry.findings || '',
                limitations: entry.limitations || '',
                relevance: entry.relevance || '',
                keywords: entry.keywords || '',
                notes: entry.notes || '',
            };
            this.showModal = true;
            this.$nextTick(() => {
                const el = focusKey ? document.getElementById(`field-${focusKey}`) : document.getElementById('field-author');
                el?.focus();
                el?.select();
            });
        },

        focusField(key) {
            this.activeField = key;
            this.$nextTick(() => {
                const el = document.getElementById(`field-${key}`);
                if (el) { el.focus(); el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            });
        },

        async saveEntry() {
            this.formErrors = {};
            if (!this.form.title?.trim()) {
                this.formErrors.title = 'Title is required.';
                document.getElementById('field-title')?.focus();
                return;
            }

            this.saving = true;
            try {
                const isEdit = this.modalMode === 'edit';
                const url = isEdit
                    ? `/students/${this.studentId}/literature/${this.editingEntry.id}`
                    : `/students/${this.studentId}/literature`;
                const method = isEdit ? 'PUT' : 'POST';

                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify(this.form),
                });

                if (!res.ok) {
                    const data = await res.json().catch(() => ({}));
                    if (data.errors) this.formErrors = Object.fromEntries(Object.entries(data.errors).map(([k, v]) => [k, v[0]]));
                    return;
                }

                const saved = await res.json();

                if (isEdit) {
                    const idx = this.entries.findIndex(e => e.id === saved.id);
                    if (idx !== -1) this.entries[idx] = saved;
                } else {
                    this.entries.push(saved);
                }

                this.showModal = false;
            } finally {
                this.saving = false;
            }
        },

        async deleteEntry(entry) {
            if (!confirm('Delete this literature entry? This cannot be undone.')) return;
            await fetch(`/students/${this.studentId}/literature/${entry.id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.csrfToken },
            });
            this.entries = this.entries.filter(e => e.id !== entry.id);
        },
    };
}
</script>
@endpush

</x-layouts.app>
