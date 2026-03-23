<x-layouts.app title="Kanban Board">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <span>Kanban Board</span>
            <span class="text-xs text-secondary">{{ $student->user->name }}</span>
        </div>
    </x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.index', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-surface">List</a>
            <a href="{{ route('tasks.kanban', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md bg-accent text-white">Kanban</a>
            <a href="{{ route('tasks.gantt', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-surface">Gantt</a>
        </div>
        <x-button href="{{ route('tasks.create', $student) }}" variant="accent" size="sm">+ New Task</x-button>
    </div>

    <div x-data="initKanbanBoard({ studentId: {{ $student->id }} })" x-init="init()" x-cloak class="flex gap-4 overflow-x-auto pb-4">
        @php
            // Define columns
            $columns = [
                'planned' => 'Planned',
                'in_progress' => 'In Progress',
                'waiting_review' => 'Waiting Review',
                'revision' => 'Revision',
                'completed' => 'Completed',
            ];
            $columnColors = [
                'planned' => 'bg-blue-400',
                'in_progress' => 'bg-yellow-400',
                'waiting_review' => 'bg-orange-400',
                'revision' => 'bg-purple-400',
                'completed' => 'bg-green-400',
            ];
            // Define next status for each column
            $nextStatus = [
                'planned' => 'in_progress',
                'in_progress' => 'waiting_review',
                'waiting_review' => 'completed',
                'revision' => 'waiting_review',
                'completed' => null,
            ];
        @endphp

        @foreach($columns as $status => $label)
            <div class="flex-shrink-0 w-72">
                <div class="flex items-center gap-2 mb-3 sticky top-0 bg-surface py-2 z-10">
                    <div class="w-2 h-2 rounded-full {{ $columnColors[$status] }}"></div>
                    <h3 class="text-xs font-semibold text-secondary uppercase tracking-wider">{{ $label }}</h3>
                    <span class="column-count text-[10px] text-secondary/60 bg-surface px-1.5 rounded">{{ ($tasks[$status] ?? collect())->count() }}</span>
                </div>
                <div
                    data-kanban-column="{{ $status }}"
                    class="kanban-column space-y-2 min-h-[400px] p-2 rounded-lg bg-surface border border-border transition-colors"
                >
                    @foreach(($tasks[$status] ?? []) as $task)
                        <div
                            class="kanban-card bg-card border border-border rounded-xl p-3 hover:shadow-md hover:border-accent/30 transition-all cursor-grab active:cursor-grabbing"
                            data-task-id="{{ $task->id }}"
                            data-task-status="{{ $task->status }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <a href="{{ route('tasks.show', [$student, $task]) }}" class="text-sm font-medium text-primary hover:text-accent flex-1">{{ $task->title }}</a>
                                <span class="flex-shrink-0 w-2 h-2 rounded-full {{ $columnColors[$status] }}" title="{{ $label }}"></span>
                            </div>
                            @if($task->description)
                                <p class="text-xs text-secondary mt-1 line-clamp-2">{{ \Illuminate\Support\Str::limit($task->description, 80) }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-[10px] px-2 py-1 rounded-full font-medium
                                    @if($task->priority === 'urgent') bg-danger/10 text-danger
                                    @elseif($task->priority === 'high') bg-warning/10 text-warning
                                    @elseif($task->priority === 'medium') bg-info/10 text-info
                                    @else bg-tertiary/10 text-secondary @endif">
                                    {{ ucfirst($task->priority) }}
                                </span>
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
                                <div class="mt-2 w-full bg-border-light rounded-full h-1.5">
                                    <div class="bg-accent h-1.5 rounded-full transition-all duration-300" style="width: {{ $task->progress }}%"></div>
                                </div>
                            @endif
                            @if($nextStatus[$status])
                                <button
                                    @click="moveToNext({{ $task->id }}, '{{ $nextStatus[$status] }}')"
                                    :disabled="loading"
                                    class="mt-3 w-full flex items-center justify-center gap-1.5 px-2 py-1.5 text-xs font-medium rounded-lg bg-accent/10 text-accent hover:bg-accent/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                    <span>Move to {{ $columns[$nextStatus[$status]] }}</span>
                                </button>
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
        .kanban-card {
            user-select: none;
            touch-action: none;
        }
        .kanban-card:hover {
            transform: translateY(-1px);
        }
        .kanban-ghost {
            opacity: 0.4;
            background: #FAFAF9;
            border-style: dashed;
        }
        .kanban-dragging {
            cursor: grabbing !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
            transform: rotate(2deg) scale(1.02);
            opacity: 0.9;
        }
        .kanban-loading {
            pointer-events: none;
            opacity: 0.6;
        }
        .kanban-column.drag-over {
            background: rgba(217, 119, 6, 0.08);
            border-color: #D97706 !important;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .kanban-card.animate-in {
            animation: slideIn 0.2s ease-out;
        }
    </style>
    @endpush
</x-layouts.app>
