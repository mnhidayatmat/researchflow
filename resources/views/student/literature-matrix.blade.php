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
        <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Literature Matrix</h2>
        <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">
            {{ $student->user->name }} &mdash; Systematic literature review table
        </p>
    </div>
    <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2">
        <button @click="showColumnManager = !showColumnManager"
                :class="showColumnManager ? 'bg-accent/10 text-accent border-accent/30' : 'text-secondary border-border dark:border-dark-border hover:bg-surface dark:hover:bg-dark-surface hover:text-primary dark:hover:text-dark-primary'"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium border transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Columns
        </button>

        <button @click="openAddModal()"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 transition-all shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Entry
        </button>

        <button @click="showImportModal = true"
                class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-secondary border border-border dark:border-dark-border hover:bg-surface dark:hover:bg-dark-surface hover:text-primary dark:hover:text-dark-primary transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Import
        </button>

        <a href="{{ route('literature.export', $student) }}"
           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-xs font-medium text-secondary border border-border dark:border-dark-border hover:bg-surface dark:hover:bg-dark-surface hover:text-primary dark:hover:text-dark-primary transition-all">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/>
            </svg>
            Export
        </a>
    </div>
</div>

{{-- ── Summary Stats ─────────────────────────────────────────────────────── --}}
<div x-show="entries.length > 0" x-cloak class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
    <div class="bg-card dark:bg-dark-card rounded-2xl p-4 border border-border dark:border-dark-border hover:border-accent/30 transition-all">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-lg font-bold text-primary dark:text-dark-primary" x-text="stats?.total || 0"></p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">Total Entries</p>
            </div>
        </div>
    </div>
    <div class="bg-card dark:bg-dark-card rounded-2xl p-4 border border-border dark:border-dark-border hover:border-info/30 transition-all">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-primary dark:text-dark-primary" x-text="stats?.yearRange || 'N/A'"></p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">Year Range</p>
            </div>
        </div>
    </div>
    <div class="bg-card dark:bg-dark-card rounded-2xl p-4 border border-border dark:border-dark-border hover:border-success/30 transition-all">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-success/20 to-success/10 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-bold text-primary dark:text-dark-primary truncate max-w-[100px]"
                   x-text="stats?.topJournal || 'N/A'" :title="stats?.topJournal"></p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">Top Journal</p>
            </div>
        </div>
    </div>
    <div class="bg-card dark:bg-dark-card rounded-2xl p-4 border border-border dark:border-dark-border hover:border-warning/30 transition-all">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10 flex items-center justify-center">
                <svg class="w-4.5 h-4.5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <div>
                <p class="text-lg font-bold text-primary dark:text-dark-primary" x-text="stats?.methodologyCount || 0"></p>
                <p class="text-[10px] text-secondary dark:text-dark-secondary">Method Types</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Body layout ───────────────────────────────────────────────────────── --}}
<div class="flex gap-4 flex-1 min-h-0">

    {{-- ── Column manager panel (desktop) ────────────────────────────────── --}}
    <div x-show="showColumnManager"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-x-2"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 -translate-x-2"
         class="hidden sm:flex w-56 shrink-0 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-2xl overflow-hidden flex-col">

        <div class="px-4 py-3 border-b border-border dark:border-dark-border">
            <p class="text-xs font-semibold text-primary dark:text-dark-primary">Manage Columns</p>
            <p class="text-[10px] text-secondary dark:text-dark-secondary mt-0.5">Drag to reorder, toggle visibility</p>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-0.5">
            <template x-for="(col, idx) in columns" :key="col.key">
                <div class="flex items-center gap-2 p-2 rounded-xl hover:bg-surface dark:hover:bg-dark-surface group cursor-grab active:cursor-grabbing"
                     draggable="true"
                     @dragstart="colDragStart($event, idx)"
                     @dragover.prevent="colDragOver($event, idx)"
                     @drop.prevent="colDrop($event, idx)"
                     @dragend="colDragEnd()">
                    <svg class="w-3 h-3 text-tertiary dark:text-dark-tertiary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                    </svg>
                    <button @click="col.visible = !col.visible; saveColumnConfig()"
                            :class="col.visible ? 'bg-accent' : 'bg-border dark:bg-dark-border'"
                            class="relative inline-flex h-4 w-7 shrink-0 items-center rounded-full transition-colors">
                        <span :class="col.visible ? 'translate-x-3.5' : 'translate-x-0.5'"
                              class="inline-block h-3 w-3 transform rounded-full bg-white transition-transform shadow-sm"></span>
                    </button>
                    <span class="text-xs text-primary dark:text-dark-primary truncate flex-1" x-text="col.label"></span>
                    <button @click="deleteColumn(col, idx)"
                            class="opacity-0 group-hover:opacity-100 p-0.5 rounded text-tertiary hover:text-danger transition-all"
                            :title="col.custom ? 'Delete column' : 'Hide column'">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        <div class="p-2 border-t border-border dark:border-dark-border space-y-1">
            <div x-show="!addingColumn">
                <button @click="addingColumn = true; $nextTick(() => $refs.newColInput?.focus())"
                        class="w-full inline-flex items-center justify-center gap-1.5 text-xs text-accent hover:text-amber-700 py-1.5 rounded-lg hover:bg-accent/10 transition-colors font-medium">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Custom Column
                </button>
            </div>
            <div x-show="addingColumn" class="flex gap-1">
                <input x-ref="newColInput" x-model="newColumnLabel"
                       @keydown.enter="confirmAddColumn()" @keydown.escape="addingColumn = false; newColumnLabel = ''"
                       placeholder="Column name"
                       class="flex-1 min-w-0 px-2 py-1.5 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50">
                <button @click="confirmAddColumn()" class="px-2 py-1.5 rounded-lg bg-accent text-white text-xs hover:bg-amber-700 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
                <button @click="addingColumn = false; newColumnLabel = ''" class="px-2 py-1.5 rounded-lg text-secondary text-xs hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <button @click="resetColumns()"
                    class="w-full text-xs text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary py-1.5 rounded-lg hover:bg-surface dark:hover:bg-dark-surface transition-colors">
                Reset to default
            </button>
        </div>
    </div>

    {{-- ── Column manager (mobile bottom sheet) ──────────────────────────── --}}
    <div x-show="showColumnManager"
         x-cloak
         class="fixed inset-0 z-40 sm:hidden"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/30" @click="showColumnManager = false"></div>
        <div class="absolute bottom-0 left-0 right-0 max-h-[70vh] bg-card dark:bg-dark-card border-t border-border dark:border-dark-border rounded-t-2xl overflow-hidden flex flex-col"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
            <div class="px-4 py-3 border-b border-border dark:border-dark-border flex items-center justify-between">
                <p class="text-xs font-semibold text-primary dark:text-dark-primary">Manage Columns</p>
                <button @click="showColumnManager = false" class="p-1 rounded-lg text-secondary hover:text-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-2 space-y-0.5">
                <template x-for="(col, idx) in columns" :key="'m-' + col.key">
                    <div class="flex items-center gap-2 p-2.5 rounded-xl">
                        <button @click="col.visible = !col.visible; saveColumnConfig()"
                                :class="col.visible ? 'bg-accent' : 'bg-border dark:bg-dark-border'"
                                class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors">
                            <span :class="col.visible ? 'translate-x-4.5' : 'translate-x-0.5'" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm"></span>
                        </button>
                        <span class="text-sm text-primary dark:text-dark-primary truncate flex-1" x-text="col.label"></span>
                        <button @click="deleteColumn(col, idx)" class="p-1 rounded text-tertiary hover:text-danger">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
            <div class="p-3 border-t border-border dark:border-dark-border">
                <button @click="addingColumn = true" x-show="!addingColumn"
                        class="w-full py-2.5 rounded-xl bg-accent/10 text-accent text-sm font-medium">+ Add Custom Column</button>
                <div x-show="addingColumn" class="flex gap-2">
                    <input x-model="newColumnLabel" @keydown.enter="confirmAddColumn()" placeholder="Column name"
                           class="flex-1 px-3 py-2.5 text-sm bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-xl text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50">
                    <button @click="confirmAddColumn()" class="px-4 py-2.5 rounded-xl bg-accent text-white text-sm">Add</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Table area ──────────────────────────────────────────────────────── --}}
    <div class="flex-1 min-w-0 bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-2xl overflow-hidden flex flex-col">

        {{-- Search bar --}}
        <div x-show="entries.length > 0" class="px-4 py-2.5 border-b border-border dark:border-dark-border">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-tertiary dark:text-dark-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" x-model.debounce.200ms="searchQuery"
                       placeholder="Search entries by title, author, keywords..."
                       class="w-full pl-9 pr-8 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-xl text-primary dark:text-dark-primary placeholder-tertiary dark:placeholder-dark-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                <button x-show="searchQuery" @click="searchQuery = ''"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-tertiary hover:text-primary transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Empty state --}}
        <template x-if="entries.length === 0">
            <div class="flex flex-col items-center justify-center flex-1 py-16 px-6 text-center">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary mb-1">No literature entries yet</h3>
                <p class="text-xs text-secondary dark:text-dark-secondary max-w-xs mb-4">Start building your literature matrix by adding your first paper or importing from Excel.</p>
                <div class="flex items-center gap-2">
                    <button @click="openAddModal()"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Entry
                    </button>
                    <button @click="showImportModal = true"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium text-secondary border border-border dark:border-dark-border hover:bg-surface transition-all">
                        Import Excel
                    </button>
                </div>
            </div>
        </template>

        {{-- No results state --}}
        <template x-if="entries.length > 0 && filteredEntries.length === 0">
            <div class="flex flex-col items-center justify-center flex-1 py-12 text-center">
                <svg class="w-10 h-10 text-tertiary mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-sm font-medium text-primary dark:text-dark-primary">No matching entries</p>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Try adjusting your search query</p>
            </div>
        </template>

        {{-- Scrollable table --}}
        <template x-if="filteredEntries.length > 0">
            <div class="overflow-auto flex-1 -webkit-overflow-scrolling-touch">
                <table class="w-full text-xs border-collapse" style="min-width: 600px">
                    <thead class="sticky top-0 z-10 bg-surface dark:bg-dark-surface">
                        <tr>
                            <th class="text-left px-3 py-2.5 border-b border-border dark:border-dark-border font-semibold text-secondary dark:text-dark-secondary w-10 text-center">#</th>
                            <template x-for="col in visibleColumns" :key="col.key">
                                <th class="text-left px-3 py-2.5 border-b border-border dark:border-dark-border font-semibold text-secondary dark:text-dark-secondary whitespace-nowrap select-none"
                                    x-text="col.label"></th>
                            </template>
                            <th class="text-right px-3 py-2.5 border-b border-border dark:border-dark-border font-semibold text-secondary dark:text-dark-secondary w-16">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(entry, rowIdx) in filteredEntries" :key="entry.id">
                            <tr class="group border-b border-border dark:border-dark-border last:border-0 hover:bg-surface/60 dark:hover:bg-dark-surface/60 transition-colors"
                                :draggable="!searchQuery.trim() ? 'true' : 'false'"
                                @dragstart="rowDragStart($event, rowIdx)"
                                @dragover.prevent="rowDragOver($event, rowIdx)"
                                @drop.prevent="rowDrop($event, rowIdx)"
                                @dragend="rowDragEnd()"
                                :class="draggingRowIdx === rowIdx ? 'opacity-40' : ''">

                                <td class="px-3 py-2 text-center text-tertiary dark:text-dark-tertiary"
                                    :class="!searchQuery.trim() ? 'cursor-grab active:cursor-grabbing' : ''">
                                    <span x-text="rowIdx + 1" class="text-[10px]"></span>
                                </td>

                                <template x-for="col in visibleColumns" :key="col.key">
                                    <td class="px-3 py-2 max-w-xs align-top cursor-pointer"
                                        @click="openEditModal(entry, col.key)">
                                        <div class="line-clamp-3 text-primary dark:text-dark-primary leading-relaxed"
                                             x-text="getCellValue(entry, col) || '\u2014'"
                                             :class="!getCellValue(entry, col) ? 'text-tertiary dark:text-dark-tertiary italic' : ''"></div>
                                    </td>
                                </template>

                                <td class="px-3 py-2 text-right whitespace-nowrap">
                                    <button @click.stop="openEditModal(entry, null)"
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-lg text-secondary hover:text-primary hover:bg-surface dark:hover:bg-dark-surface transition-all opacity-0 group-hover:opacity-100"
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
        <div x-show="entries.length > 0" class="px-4 py-2.5 border-t border-border dark:border-dark-border flex items-center justify-between bg-surface/40 dark:bg-dark-surface/40">
            <span class="text-[10px] text-tertiary dark:text-dark-tertiary"
                  x-text="searchQuery ? `${filteredEntries.length} of ${entries.length} entries` : `${entries.length} entr${entries.length === 1 ? 'y' : 'ies'}`"></span>
            <span class="text-[10px] text-tertiary dark:text-dark-tertiary hidden sm:inline">Click a cell to edit &bull; Drag rows to reorder</span>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════
     Entry form modal (add / edit)
══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="showModal" x-cloak
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @keydown.escape.window="showModal = false"
     class="fixed inset-0 z-50 flex items-end sm:items-center sm:justify-center sm:p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>

    <div class="relative bg-card dark:bg-dark-card rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-2xl h-[92vh] sm:h-auto sm:max-h-[90vh] flex flex-col overflow-hidden"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

        <div class="px-6 py-4 border-b border-border dark:border-dark-border flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary" x-text="modalMode === 'add' ? 'Add Literature Entry' : 'Edit Literature Entry'"></h3>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Fill in the fields for this paper or article</p>
            </div>
            <button @click="showModal = false" class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface dark:hover:bg-dark-surface transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 p-6 space-y-4">

            {{-- Jump to field (quick-nav tabs) --}}
            <div class="flex flex-wrap gap-1.5 pb-3 border-b border-border dark:border-dark-border">
                <template x-for="col in allColumns" :key="col.key">
                    <button @click="focusField(col.key)"
                            :class="activeField === col.key ? 'bg-accent text-white' : 'bg-surface dark:bg-dark-surface text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary'"
                            class="px-2.5 py-1 rounded-lg text-[10px] font-medium transition-all"
                            x-text="col.label"></button>
                </template>
            </div>

            {{-- Author + Year row --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Author(s)</label>
                    <input type="text" x-model="form.author" id="field-author" @focus="activeField = 'author'"
                           placeholder="e.g. Smith, J., Jones, A."
                           class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Year</label>
                    <input type="number" x-model="form.year" id="field-year" @focus="activeField = 'year'"
                           placeholder="2024" min="1900" max="2100"
                           class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Title <span class="text-danger">*</span></label>
                <input type="text" x-model="form.title" id="field-title" @focus="activeField = 'title'"
                       placeholder="Full paper title"
                       class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                <p x-show="formErrors.title" class="text-[10px] text-danger mt-1" x-text="formErrors.title"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Journal / Source</label>
                    <input type="text" x-model="form.journal" id="field-journal" @focus="activeField = 'journal'" placeholder="Journal name"
                           class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">DOI / URL</label>
                    <input type="text" x-model="form.doi_url" id="field-doi_url" @focus="activeField = 'doi_url'" placeholder="https://doi.org/..."
                           class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
            </div>

            @foreach(['research_objective' => 'Research Objective', 'methodology' => 'Methodology', 'dataset' => 'Dataset', 'findings' => 'Findings / Results', 'limitations' => 'Limitations', 'relevance' => 'Relevance to Study'] as $field => $label)
            <div>
                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">{{ $label }}</label>
                <textarea x-model="form.{{ $field }}" id="field-{{ $field }}" rows="2" @focus="activeField = '{{ $field }}'"
                          placeholder="{{ $label }}"
                          class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
            </div>
            @endforeach

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Keywords</label>
                    <input type="text" x-model="form.keywords" id="field-keywords" @focus="activeField = 'keywords'" placeholder="Comma-separated keywords"
                           class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Notes</label>
                    <input type="text" x-model="form.notes" id="field-notes" @focus="activeField = 'notes'" placeholder="Any additional notes"
                           class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all">
                </div>
            </div>

            {{-- Custom fields --}}
            <template x-for="col in columns.filter(c => c.custom)" :key="col.key">
                <div>
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1" x-text="col.label"></label>
                    <textarea x-model="formCustom[col.key]" :id="'field-' + col.key" rows="2"
                              @focus="activeField = col.key"
                              class="w-full px-3 py-2 text-xs bg-surface dark:bg-dark-surface border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary placeholder-tertiary focus:outline-none focus:border-accent/50 focus:ring-1 focus:ring-accent/20 transition-all resize-none"></textarea>
                </div>
            </template>
        </div>

        <div class="px-6 py-4 border-t border-border dark:border-dark-border flex items-center justify-between shrink-0 bg-surface/40 dark:bg-dark-surface/40">
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
                        class="px-4 py-2 rounded-xl text-xs font-medium text-secondary border border-border dark:border-dark-border hover:bg-surface dark:hover:bg-dark-surface transition-all">Cancel</button>
                <button @click="saveEntry()" :disabled="saving"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 disabled:opacity-60 transition-all">
                    <svg x-show="saving" class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving\u2026' : (modalMode === 'add' ? 'Add Entry' : 'Save Changes')"></span>
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════
     Import modal
══════════════════════════════════════════════════════════════════════════ --}}
<div x-show="showImportModal" x-cloak
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @keydown.escape.window="showImportModal && closeImportModal()"
     class="fixed inset-0 z-50 flex items-end sm:items-center sm:justify-center sm:p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeImportModal()"></div>

    <div class="relative bg-card dark:bg-dark-card rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-2xl h-[92vh] sm:h-auto sm:max-h-[90vh] flex flex-col overflow-hidden">

        <div class="px-6 py-4 border-b border-border dark:border-dark-border flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Import Literature</h3>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5"
                   x-text="importStep === 1 ? 'Upload an Excel or CSV file' : importStep === 2 ? 'Map file columns to fields' : 'Import complete'"></p>
            </div>
            <button @click="closeImportModal()" class="p-1.5 rounded-lg text-secondary hover:text-primary hover:bg-surface dark:hover:bg-dark-surface transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 p-6">

            {{-- Step 1: Upload --}}
            <div x-show="importStep === 1">
                <div class="border-2 border-dashed border-border dark:border-dark-border rounded-2xl p-10 text-center hover:border-accent/50 transition-colors"
                     @dragover.prevent="$el.classList.add('border-accent', 'bg-accent/5')"
                     @dragleave.prevent="$el.classList.remove('border-accent', 'bg-accent/5')"
                     @drop.prevent="$el.classList.remove('border-accent', 'bg-accent/5'); handleImportFile($event.dataTransfer.files[0])">
                    <svg class="w-10 h-10 text-tertiary mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm font-medium text-primary dark:text-dark-primary mb-1">Drop file here or click to browse</p>
                    <p class="text-xs text-secondary dark:text-dark-secondary mb-4">Supports .xlsx, .xls, .csv (max 5MB)</p>
                    <div class="flex items-center justify-center gap-2">
                        <label class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 cursor-pointer transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Choose File
                            <input type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="handleImportFile($event.target.files[0])">
                        </label>
                    </div>
                </div>
                <div class="mt-4 p-4 rounded-xl bg-surface dark:bg-dark-surface border border-border dark:border-dark-border">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-info/10 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-primary dark:text-dark-primary mb-1">Need a template?</p>
                            <p class="text-xs text-secondary dark:text-dark-secondary mb-2">Download a sample Excel file with the correct column headers and example data. Fill it with your literature entries and upload it here.</p>
                            <a href="{{ route('literature.template', $student) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-info hover:bg-info/10 border border-info/30 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a2 2 0 002 2h14a2 2 0 002-2v-3"/>
                                </svg>
                                Download Sample Template
                            </a>
                            <p class="text-[10px] text-tertiary dark:text-dark-tertiary mt-2">Columns not in your current setup will be auto-created as custom columns on import.</p>
                        </div>
                    </div>
                </div>
                <div x-show="importError" class="mt-3 p-3 rounded-xl bg-danger/10 text-danger text-xs" x-text="importError"></div>
                <div x-show="importUploading" class="mt-4 text-center">
                    <svg class="w-6 h-6 animate-spin text-accent mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-2">Reading file...</p>
                </div>
            </div>

            {{-- Step 2: Mapping --}}
            <div x-show="importStep === 2">
                <p class="text-xs text-secondary dark:text-dark-secondary mb-3" x-text="`Found ${importTotalRows} rows. Map each file column to a field:`"></p>
                <div x-show="importNewColumns.length > 0" class="mb-3 p-3 rounded-xl bg-info/10 border border-info/20">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 text-info shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-medium text-info" x-text="`${importNewColumns.length} new column(s) detected`"></p>
                            <p class="text-[10px] text-info/80 mt-0.5">These will be auto-created as custom columns when you import.</p>
                        </div>
                    </div>
                </div>
                <div class="space-y-2 max-h-[50vh] overflow-y-auto">
                    <template x-for="(header, colKey) in importHeaders" :key="colKey">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-surface dark:bg-dark-surface">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-primary dark:text-dark-primary truncate" x-text="header || '(empty)'"></p>
                                <p class="text-[10px] text-tertiary dark:text-dark-tertiary truncate" x-text="importPreview[0]?.[colKey] || ''"></p>
                            </div>
                            <svg class="w-4 h-4 text-tertiary shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            <select x-model="importMapping[colKey]"
                                    class="w-40 px-2 py-1.5 text-xs bg-card dark:bg-dark-card border border-border dark:border-dark-border rounded-lg text-primary dark:text-dark-primary focus:outline-none focus:border-accent/50">
                                <option value="skip">-- Skip --</option>
                                <option value="author">Author(s)</option>
                                <option value="year">Year</option>
                                <option value="title">Title</option>
                                <option value="journal">Journal / Source</option>
                                <option value="doi_url">DOI / URL</option>
                                <option value="research_objective">Research Objective</option>
                                <option value="methodology">Methodology</option>
                                <option value="dataset">Dataset</option>
                                <option value="findings">Findings / Results</option>
                                <option value="limitations">Limitations</option>
                                <option value="relevance">Relevance to Study</option>
                                <option value="keywords">Keywords</option>
                                <option value="notes">Notes</option>
                                <template x-for="cc in columns.filter(c => c.custom)" :key="cc.key">
                                    <option :value="cc.key" x-text="cc.label"></option>
                                </template>
                                <template x-for="nc in importNewColumns" :key="nc.key">
                                    <option :value="nc.key" x-text="nc.label + ' (new)'"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Step 3: Result --}}
            <div x-show="importStep === 3" class="text-center py-8">
                <template x-if="importing">
                    <div>
                        <svg class="w-10 h-10 animate-spin text-accent mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <p class="text-sm text-primary dark:text-dark-primary">Importing entries...</p>
                    </div>
                </template>
                <template x-if="!importing && importResult !== null">
                    <div>
                        <div class="w-14 h-14 rounded-2xl bg-success/10 flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-primary dark:text-dark-primary" x-text="`${importResult} entries imported!`"></p>
                        <p class="text-xs text-secondary dark:text-dark-secondary mt-1">They have been added to your literature matrix.</p>
                    </div>
                </template>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-border dark:border-dark-border flex items-center justify-end gap-2 shrink-0 bg-surface/40 dark:bg-dark-surface/40">
            <button @click="closeImportModal()"
                    class="px-4 py-2 rounded-xl text-xs font-medium text-secondary border border-border dark:border-dark-border hover:bg-surface dark:hover:bg-dark-surface transition-all"
                    x-text="importStep === 3 && !importing ? 'Close' : 'Cancel'"></button>
            <button x-show="importStep === 2" @click="executeImport()" :disabled="importing"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium bg-accent text-white hover:bg-amber-700 disabled:opacity-60 transition-all">
                Import
            </button>
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
        modalMode: 'add',
        editingEntry: null,
        activeField: null,
        saving: false,
        formErrors: {},
        searchQuery: '',

        // Custom column add
        addingColumn: false,
        newColumnLabel: '',

        form: {
            author: '', year: '', title: '', journal: '', doi_url: '',
            research_objective: '', methodology: '', dataset: '',
            findings: '', limitations: '', relevance: '', keywords: '', notes: '',
        },
        formCustom: {},

        // Import
        showImportModal: false,
        importStep: 1,
        importHeaders: {},
        importPreview: [],
        importTotalRows: 0,
        importFilePath: '',
        importMapping: {},
        importNewColumns: [],
        importUploading: false,
        importError: '',
        importing: false,
        importResult: null,

        // Drag state
        draggingRowIdx: null,
        draggingRowOver: null,
        draggingColIdx: null,
        draggingColOver: null,

        // ── Computed ────────────────────────────────────────────────────────

        get visibleColumns() {
            return this.columns.filter(c => c.visible);
        },

        get allColumns() {
            return this.columns;
        },

        get filteredEntries() {
            if (!this.searchQuery.trim()) return this.entries;
            const q = this.searchQuery.toLowerCase().trim();
            return this.entries.filter(entry => {
                for (const col of this.visibleColumns) {
                    const val = this.getCellValue(entry, col);
                    if (String(val).toLowerCase().includes(q)) return true;
                }
                return false;
            });
        },

        get stats() {
            const e = this.entries;
            if (!e.length) return null;
            const years = e.map(x => x.year).filter(Boolean);
            const yearRange = years.length ? `${Math.min(...years)}\u2013${Math.max(...years)}` : 'N/A';

            const jc = {};
            e.forEach(x => { if (x.journal) { const j = x.journal.trim(); jc[j] = (jc[j]||0)+1; } });
            const topJournal = Object.entries(jc).sort((a,b) => b[1]-a[1])[0]?.[0] || null;

            const mc = new Set();
            e.forEach(x => { if (x.methodology) mc.add(x.methodology.trim().split('\n')[0].substring(0,50)); });

            return { total: e.length, yearRange, topJournal, methodologyCount: mc.size };
        },

        // ── Helpers ─────────────────────────────────────────────────────────

        getCellValue(entry, col) {
            if (col.custom) return entry.custom_fields?.[col.key] || '';
            return entry[col.key] || '';
        },

        // ── Init ────────────────────────────────────────────────────────────

        init() {
            this.columns.forEach((c, i) => c.sort_order = i);
        },

        // ── Column manager ──────────────────────────────────────────────────

        colDragStart(e, idx) { this.draggingColIdx = idx; e.dataTransfer.effectAllowed = 'move'; },
        colDragOver(e, idx) { this.draggingColOver = idx; },
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

        confirmAddColumn() {
            const label = this.newColumnLabel.trim();
            if (!label) return;
            const existing = this.columns.filter(c => c.custom).map(c => parseInt(c.key.replace('custom_', '')) || 0);
            const next = existing.length ? Math.max(...existing) + 1 : 1;
            this.columns.push({ key: `custom_${next}`, label, visible: true, sort_order: this.columns.length, custom: true });
            this.saveColumnConfig();
            this.newColumnLabel = '';
            this.addingColumn = false;
        },

        deleteColumn(col, idx) {
            if (col.custom) {
                if (!confirm(`Delete custom column "${col.label}"? Data in this column will be hidden.`)) return;
                this.columns.splice(idx, 1);
            } else {
                col.visible = false;
            }
            this.columns.forEach((c, i) => c.sort_order = i);
            this.saveColumnConfig();
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

        // ── Row drag-to-reorder ─────────────────────────────────────────────

        rowDragStart(e, idx) { this.draggingRowIdx = idx; e.dataTransfer.effectAllowed = 'move'; },
        rowDragOver(e, idx) { this.draggingRowOver = idx; },
        rowDrop(e, idx) {
            if (this.draggingRowIdx === null || this.draggingRowIdx === idx) return;
            const moved = this.entries.splice(this.draggingRowIdx, 1)[0];
            this.entries.splice(idx, 0, moved);
            this.draggingRowIdx = null;
            this.draggingRowOver = null;
            const order = this.entries.map(e => e.id);
            fetch(`/students/${this.studentId}/literature/reorder`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                body: JSON.stringify({ order }),
            });
        },
        rowDragEnd() { this.draggingRowIdx = null; this.draggingRowOver = null; },

        // ── Modal ────────────────────────────────────────────────────────────

        _initFormCustom(data) {
            this.formCustom = {};
            this.columns.filter(c => c.custom).forEach(c => {
                this.formCustom[c.key] = data?.[c.key] || '';
            });
        },

        openAddModal() {
            this.modalMode = 'add';
            this.editingEntry = null;
            this.formErrors = {};
            this.activeField = 'author';
            this.form = { author:'',year:'',title:'',journal:'',doi_url:'',research_objective:'',methodology:'',dataset:'',findings:'',limitations:'',relevance:'',keywords:'',notes:'' };
            this._initFormCustom({});
            this.showModal = true;
            this.$nextTick(() => document.getElementById('field-author')?.focus());
        },

        openEditModal(entry, focusKey) {
            this.modalMode = 'edit';
            this.editingEntry = entry;
            this.formErrors = {};
            this.activeField = focusKey || 'author';
            this.form = {
                author: entry.author || '', year: entry.year || '', title: entry.title || '',
                journal: entry.journal || '', doi_url: entry.doi_url || '',
                research_objective: entry.research_objective || '', methodology: entry.methodology || '',
                dataset: entry.dataset || '', findings: entry.findings || '',
                limitations: entry.limitations || '', relevance: entry.relevance || '',
                keywords: entry.keywords || '', notes: entry.notes || '',
            };
            this._initFormCustom(entry.custom_fields || {});
            this.showModal = true;
            this.$nextTick(() => {
                const el = focusKey ? document.getElementById(`field-${focusKey}`) : document.getElementById('field-author');
                el?.focus(); el?.select();
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

                const body = { ...this.form };
                const customFields = {};
                let hasCustom = false;
                for (const [k, v] of Object.entries(this.formCustom)) {
                    if (v) { customFields[k] = v; hasCustom = true; }
                }
                if (hasCustom) body.custom_fields = customFields;

                const res = await fetch(url, {
                    method: isEdit ? 'PUT' : 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify(body),
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

        // ── Import ───────────────────────────────────────────────────────────

        async handleImportFile(file) {
            if (!file) return;
            this.importError = '';
            const valid = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                           'application/vnd.ms-excel', 'text/csv', 'application/csv'];
            if (!valid.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
                this.importError = 'Please select an Excel (.xlsx, .xls) or CSV file.';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.importError = 'File size must be under 5MB.';
                return;
            }

            this.importUploading = true;
            try {
                const fd = new FormData();
                fd.append('file', file);
                const res = await fetch(`/students/${this.studentId}/literature/import/preview`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken },
                    body: fd,
                });
                if (!res.ok) {
                    this.importError = 'Failed to read file. Please check the format.';
                    return;
                }
                const data = await res.json();
                this.importHeaders = data.headers;
                this.importPreview = data.preview;
                this.importTotalRows = data.totalRows;
                this.importFilePath = data.filePath;

                // Auto-map columns
                this.importMapping = {};
                this.importNewColumns = [];
                const fieldMap = {
                    'author': 'author', 'authors': 'author', 'year': 'year', 'title': 'title',
                    'journal': 'journal', 'source': 'journal', 'doi': 'doi_url', 'url': 'doi_url', 'doi/url': 'doi_url',
                    'research objective': 'research_objective', 'objective': 'research_objective',
                    'methodology': 'methodology', 'method': 'methodology',
                    'dataset': 'dataset', 'data': 'dataset',
                    'findings': 'findings', 'results': 'findings',
                    'limitations': 'limitations', 'limitation': 'limitations',
                    'relevance': 'relevance', 'keywords': 'keywords', 'keyword': 'keywords',
                    'notes': 'notes', 'note': 'notes',
                };
                // Also map existing custom columns by label
                const customMap = {};
                this.columns.filter(c => c.custom).forEach(c => {
                    customMap[c.label.toLowerCase().trim()] = c.key;
                });

                // Find next custom column number
                const existingNums = this.columns.filter(c => c.custom).map(c => parseInt(c.key.replace('custom_', '')) || 0);
                let nextCustom = existingNums.length ? Math.max(...existingNums) + 1 : 1;

                for (const [colKey, header] of Object.entries(this.importHeaders)) {
                    const h = (header || '').toLowerCase().trim();
                    if (!h) { this.importMapping[colKey] = 'skip'; continue; }

                    if (fieldMap[h]) {
                        this.importMapping[colKey] = fieldMap[h];
                    } else if (customMap[h]) {
                        this.importMapping[colKey] = customMap[h];
                    } else {
                        // Auto-create a new custom column for unrecognized headers
                        const newKey = `custom_${nextCustom++}`;
                        this.importNewColumns.push({ key: newKey, label: header.trim() });
                        this.importMapping[colKey] = newKey;
                    }
                }

                this.importStep = 2;
            } finally {
                this.importUploading = false;
            }
        },

        async executeImport() {
            this.importStep = 3;
            this.importing = true;
            this.importResult = null;
            try {
                const payload = {
                    filePath: this.importFilePath,
                    mapping: this.importMapping,
                };
                // Pass new columns to backend for auto-creation
                if (this.importNewColumns && this.importNewColumns.length > 0) {
                    payload.newColumns = this.importNewColumns;
                }
                const res = await fetch(`/students/${this.studentId}/literature/import`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (data.entries) {
                    this.entries.push(...data.entries);
                }
                // Sync columns from backend (includes any auto-created custom columns)
                if (data.columns) {
                    this.columns = data.columns;
                }
                this.importResult = data.count || 0;
            } catch {
                this.importResult = 0;
            } finally {
                this.importing = false;
            }
        },

        closeImportModal() {
            this.showImportModal = false;
            this.importStep = 1;
            this.importHeaders = {};
            this.importPreview = [];
            this.importMapping = {};
            this.importNewColumns = [];
            this.importFilePath = '';
            this.importError = '';
            this.importResult = null;
        },
    };
}
</script>
@endpush

</x-layouts.app>
