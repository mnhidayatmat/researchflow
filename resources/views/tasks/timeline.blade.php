<x-layouts.app title="Timeline View">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <span>Timeline View</span>
            <span class="text-xs text-secondary">{{ $student->user->name }}</span>
        </div>
    </x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.index', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">List</a>
            <a href="{{ route('tasks.kanban', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Kanban</a>
            <a href="{{ route('tasks.gantt', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Timeline</a>
            <a href="{{ route('tasks.timeline', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md bg-primary text-white">Timeline View</a>
        </div>
        <x-button href="{{ route('tasks.create', $student) }}" variant="accent" size="sm">+ New Task</x-button>
    </div>

    <div x-data="initTaskFlowTimeline({ studentId: {{ $student->id }} })" x-init="init()" class="space-y-4">
        <!-- Controls -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-1">
                <button @click="move(-0.3)" class="p-2 rounded-md text-secondary hover:bg-gray-100" title="Pan left">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button @click="fit()" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Fit</button>
                <button @click="move(0.3)" class="p-2 rounded-md text-secondary hover:bg-gray-100" title="Pan right">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div class="w-px h-4 bg-gray-300 mx-1"></div>
                <button @click="zoom(0.7)" class="p-2 rounded-md text-secondary hover:bg-gray-100" title="Zoom out">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                    </svg>
                </button>
                <button @click="zoom(1.3)" class="p-2 rounded-md text-secondary hover:bg-gray-100" title="Zoom in">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                    </svg>
                </button>
            </div>

            <!-- Legend -->
            <div class="flex items-center gap-3 text-xs">
                <span class="text-secondary">Status:</span>
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-gray-400"></span> Backlog</span>
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-blue-500"></span> Planned</span>
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-yellow-500"></span> In Progress</span>
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-orange-500"></span> Review</span>
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-purple-500"></span> Revision</span>
                <span class="flex items-center gap-1"><span class="w-3 h-2 rounded bg-green-500"></span> Completed</span>
            </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="flex items-center justify-center py-16">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-accent"></div>
        </div>

        <!-- Timeline Container -->
        <x-card :padding='false' x-show="!loading">
            <div id="timeline-container" class="min-h-[450px]"></div>
        </x-card>

        <!-- Instructions -->
        <div class="text-center text-xs text-secondary/60">
            <p>Drag tasks horizontally to reschedule. Double-click to view details. Use controls above to navigate.</p>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/vis-timeline@7.7.3/styles/vis-timeline-graph2d.min.css">
    <style>
        /* Custom scrollbar for timeline */
        .vis-timeline {
            font-family: system-ui, -apple-system, sans-serif;
        }
        .vis-custom-time {
            background-color: #D97706 !important;
            width: 2px !important;
        }
    </style>
    @endpush
</x-layouts.app>
