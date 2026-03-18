<x-layouts.app title="Kanban Board">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <span>Kanban Board</span>
            <span class="text-xs text-secondary">{{ $student->user->name }}</span>
        </div>
    </x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.index', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">List</a>
            <a href="{{ route('tasks.kanban', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md bg-primary text-white">Kanban</a>
            <a href="{{ route('tasks.gantt', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Timeline</a>
            <a href="{{ route('tasks.timeline', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Timeline View</a>
        </div>
        <x-button href="{{ route('tasks.create', $student) }}" variant="accent" size="sm">+ New Task</x-button>
    </div>

    <div x-data="initTaskFlowKanban({ studentId: {{ $student->id }} })" x-init="init()" class="flex gap-4 overflow-x-auto pb-4">
        @php
            $columns = [
                'backlog' => 'Backlog',
                'planned' => 'Planned',
                'in_progress' => 'In Progress',
                'waiting_review' => 'Waiting Review',
                'revision' => 'Revision',
                'completed' => 'Completed',
            ];
            $columnColors = [
                'backlog' => 'bg-gray-400',
                'planned' => 'bg-blue-400',
                'in_progress' => 'bg-yellow-400',
                'waiting_review' => 'bg-orange-400',
                'revision' => 'bg-purple-400',
                'completed' => 'bg-green-400',
            ];
        @endphp

        @foreach($columns as $status => $label)
            <div class="flex-shrink-0 w-72">
                <div class="flex items-center gap-2 mb-3 sticky top-0 bg-surface py-2 z-10">
                    <div class="w-2 h-2 rounded-full {{ $columnColors[$status] }}"></div>
                    <h3 class="text-xs font-semibold text-secondary uppercase tracking-wider">{{ $label }}</h3>
                    <span class="text-[10px] text-secondary/60 bg-gray-100 px-1.5 rounded" data-column-count>{{ ($tasks[$status] ?? collect())->count() }}</span>
                </div>
                <div
                    data-kanban-column="{{ $status }}"
                    class="kanban-column space-y-2 min-h-[400px] p-2 rounded-lg bg-gray-50/50 border-2 border-dashed border-transparent transition-colors"
                >
                    @foreach(($tasks[$status] ?? []) as $task)
                        <div
                            class="kanban-card bg-white border border-border rounded-lg p-3 cursor-move hover:shadow-md transition-all"
                            data-task-id="{{ $task->id }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('tasks.show', [$student, $task]) }}" class="text-sm font-medium text-primary hover:text-accent flex-1">{{ $task->title }}</a>
                                <span class="flex-shrink-0 w-2 h-2 rounded-full {{ $columnColors[$status] }}" title="{{ $label }}"></span>
                            </div>
                            @if($task->description)
                                <p class="text-xs text-secondary mt-1 line-clamp-2">{{ Str::limit($task->description, 80) }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-2">
                                <x-badge :color="match($task->priority) { 'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', default => 'gray' }">
                                    {{ ucfirst($task->priority) }}
                                </x-badge>
                                @if($task->due_date)
                                    <span class="text-[10px] text-secondary flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $task->due_date->format('M d') }}
                                    </span>
                                @endif
                            </div>
                            @if($task->progress > 0)
                                <div class="mt-2 w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-accent h-1.5 rounded-full transition-all duration-300" style="width: {{ $task->progress }}%"></div>
                                </div>
                            @endif
                            @if($task->dependencies->count() > 0)
                                <div class="mt-2 flex items-center gap-1 text-[10px] text-secondary/60">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    {{ $task->dependencies->count() }} dependenc{{ $task->dependencies->count() > 1 ? 'ies' : 'y' }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    @push('styles')
    <style>
        /* Kanban drag and drop styles */
        .kanban-ghost {
            opacity: 0.3;
            background: #F7F7F5;
        }
        .kanban-dragging {
            cursor: grabbing;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
            transform: rotate(2deg);
        }
        .kanban-loading {
            pointer-events: none;
        }
        .kanban-column:hover {
            border-color: #E5E7EB;
        }
        .kanban-column.drag-over {
            background: rgba(217, 119, 6, 0.05);
            border-color: #D97706;
        }
    </style>
    @endpush
</x-layouts.app>
