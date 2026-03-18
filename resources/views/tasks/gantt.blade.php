<x-layouts.app title="Timeline">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <span>Timeline</span>
            <span class="text-xs text-secondary">{{ $student->user->name }}</span>
        </div>
    </x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.index', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">List</a>
            <a href="{{ route('tasks.kanban', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Kanban</a>
            <a href="{{ route('tasks.gantt', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md bg-primary text-white">Timeline</a>
            <a href="{{ route('tasks.timeline', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Timeline View</a>
        </div>
        <div class="flex items-center gap-2">
            <x-button href="{{ route('tasks.create', $student) }}" variant="accent" size="sm">+ New Task</x-button>
            <button @click="refresh()" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <div x-data="initTaskFlowGantt({ studentId: {{ $student->id }} })" x-init="init()" class="space-y-4">
        <!-- View Mode Selector -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                <button @click="setView('Day')" :class="viewMode === 'Day' ? 'bg-white shadow-sm text-primary' : 'text-secondary hover:text-primary'" class="px-3 py-1 text-xs font-medium rounded-md transition-all">Day</button>
                <button @click="setView('Week')" :class="viewMode === 'Week' ? 'bg-white shadow-sm text-primary' : 'text-secondary hover:text-primary'" class="px-3 py-1 text-xs font-medium rounded-md transition-all">Week</button>
                <button @click="setView('Month')" :class="viewMode === 'Month' ? 'bg-white shadow-sm text-primary' : 'text-secondary hover:text-primary'" class="px-3 py-1 text-xs font-medium rounded-md transition-all">Month</button>
            </div>

            <!-- Legend -->
            <div class="flex items-center gap-3 text-xs">
                <span class="text-secondary">Status:</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-gray-400"></span> Backlog</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-400"></span> Planned</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-400"></span> In Progress</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-orange-400"></span> Review</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-400"></span> Revision</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-400"></span> Completed</span>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-16">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent"></div>
        </div>

        <!-- Gantt Container -->
        <x-card :padding='false' x-show="!loading">
            <div id="gantt-container" class="min-h-[400px]"></div>
        </x-card>

        <!-- Instructions -->
        <div class="text-center text-xs text-secondary/60">
            <p>Drag the ends of tasks to adjust dates. Click a task to view details.</p>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
    @endpush
</x-layouts.app>
