@php
    $statusLabels = [
        'planned' => 'Planned',
        'in_progress' => 'In Progress',
        'waiting_review' => 'Review',
        'revision' => 'Revision',
        'completed' => 'Completed',
    ];
    $activeTab = request()->get('tab', 'list');
@endphp

<x-layouts.app title="Tasks">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            <span>Tasks</span>
            <span class="text-xs text-secondary">{{ $student->user->name }}</span>
        </div>
    </x-slot:header>

    <!-- Enhanced Toolbar -->
    <div class="bg-card rounded-2xl border border-border p-4 mb-5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left: Filters & Sort -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filter Dropdown -->
                <div x-data="{ open: false, filter: '{{ request()->get('status', 'all') }}' }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-secondary hover:text-primary border border-border hover:bg-surface transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V5l4 4v11.586a1 1 0 01-.293.707l-1 1a1 1 0 01-1.414 0l-1-1a1 1 0 01-.293-.707V8l-4-4H4a1 1 0 01-1-1V4z"/>
                        </svg>
                        Filter
                        <span x-show="filter !== 'all'" class="flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-accent/10 text-accent text-[10px] font-semibold" x-text="filter === 'all' ? '' : KANBAN_COLUMNS[filter]?.label ?? filter"></span>
                        <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute left-0 mt-2 w-44 bg-card rounded-xl border border-border shadow-lg py-1 z-50">
                        <a href="{{ Request::fullUrl() }}" :class="filter === 'all' ? 'bg-accent/10 text-accent' : 'text-secondary hover:bg-surface'" class="flex items-center gap-2 px-3 py-2 text-xs rounded-lg transition-colors">
                            <span class="w-2 h-2 rounded-full bg-secondary"></span>
                            All Tasks
                        </a>
                        <a href="{{ Request::fullUrlWithQuery(['status' => 'planned']) }}" :class="filter === 'planned' ? 'bg-accent/10 text-accent' : 'text-secondary hover:bg-surface'" class="flex items-center gap-2 px-3 py-2 text-xs rounded-lg transition-colors">
                            <span class="w-2 h-2 rounded-full bg-info"></span>
                            Planned
                        </a>
                        <a href="{{ Request::fullUrlWithQuery(['status' => 'in_progress']) }}" :class="filter === 'in_progress' ? 'bg-accent/10 text-accent' : 'text-secondary hover:bg-surface'" class="flex items-center gap-2 px-3 py-2 text-xs rounded-lg transition-colors">
                            <span class="w-2 h-2 rounded-full bg-warning"></span>
                            In Progress
                        </a>
                        <a href="{{ Request::fullUrlWithQuery(['status' => 'waiting_review']) }}" :class="filter === 'waiting_review' ? 'bg-accent/10 text-accent' : 'text-secondary hover:bg-surface'" class="flex items-center gap-2 px-3 py-2 text-xs rounded-lg transition-colors">
                            <span class="w-2 h-2 rounded-full bg-accent"></span>
                            Review
                        </a>
                        <a href="{{ Request::fullUrlWithQuery(['status' => 'revision']) }}" :class="filter === 'revision' ? 'bg-accent/10 text-accent' : 'text-secondary hover:bg-surface'" class="flex items-center gap-2 px-3 py-2 text-xs rounded-lg transition-colors">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            Revision
                        </a>
                        <a href="{{ Request::fullUrlWithQuery(['status' => 'completed']) }}" :class="filter === 'completed' ? 'bg-accent/10 text-accent' : 'text-secondary hover:bg-surface'" class="flex items-center gap-2 px-3 py-2 text-xs rounded-lg transition-colors">
                            <span class="w-2 h-2 rounded-full bg-success"></span>
                            Completed
                        </a>
                    </div>
                </div>

                <!-- Sort Dropdown -->
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg text-secondary hover:text-primary border border-border hover:bg-surface transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m0 0l4-4m0 0l4 4"/>
                        </svg>
                        Sort
                        <svg class="w-3 h-3 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute left-0 mt-2 w-40 bg-card rounded-xl border border-border shadow-lg py-1 z-50">
                        <a href="{{ Request::fullUrlWithQuery(['sort' => 'due_date', 'order' => 'asc']) }}" class="block px-3 py-2 text-xs text-secondary hover:bg-surface">Due Date: Earliest</a>
                        <a href="{{ Request::fullUrlWithQuery(['sort' => 'due_date', 'order' => 'desc']) }}" class="block px-3 py-2 text-xs text-secondary hover:bg-surface">Due Date: Latest</a>
                        <a href="{{ Request::fullUrlWithQuery(['sort' => 'priority', 'order' => 'desc']) }}" class="block px-3 py-2 text-xs text-secondary hover:bg-surface">Priority: High to Low</a>
                        <a href="{{ Request::fullUrlWithQuery(['sort' => 'priority', 'order' => 'asc']) }}" class="block px-3 py-2 text-xs text-secondary hover:bg-surface">Priority: Low to High</a>
                        <a href="{{ Request::fullUrlWithQuery(['sort' => 'created_at', 'order' => 'desc']) }}" class="block px-3 py-2 text-xs text-secondary hover:bg-surface">Newest First</a>
                        <a href="{{ Request::fullUrlWithQuery(['sort' => 'created_at', 'order' => 'asc']) }}" class="block px-3 py-2 text-xs text-secondary hover:bg-surface">Oldest First</a>
                    </div>
                </div>
            </div>

            <!-- Right: Search & Actions -->
            <div class="flex items-center gap-2">
                <!-- Search -->
                <div class="relative">
                    <svg class="w-4 h-4 text-secondary absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" placeholder="Search tasks..." class="pl-9 pr-4 py-2 text-xs border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-accent/20 focus:border-accent w-48" x-data="{ search: '{{ request()->get('search', '') }}' }" x-init="$el.value = search" @keyup.enter="window.location.href = '{{ Request::fullUrlWithQuery(['search' => '']) }}'.replace('search=', 'search=' + $el.value)">
                </div>

                <!-- New Task Button -->
                <x-button href="{{ route('tasks.create', $student) }}" variant="accent" size="sm" class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Task
                </x-button>
            </div>
        </div>

        <!-- Active Filters Display -->
        @if(request()->has('status') || request()->has('search'))
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-border">
            <span class="text-xs text-secondary">Active:</span>
            @if(request()->has('status'))
                <a href="{{ Request::fullUrlWithoutQuery('status') }}" class="flex items-center gap-1 px-2 py-1 rounded-full bg-accent/10 text-accent text-xs font-medium">
                    {{ $statusLabels[request()->get('status')] ?? ucfirst(request()->get('status')) }}
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endif
            @if(request()->has('search'))
                <a href="{{ Request::fullUrlWithoutQuery('search') }}" class="flex items-center gap-1 px-2 py-1 rounded-full bg-info/10 text-info text-xs font-medium">
                    "{{ request()->get('search') }}"
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </a>
            @endif
        </div>
        @endif
    </div>

    <!-- View Tabs -->
    <div x-data="{ activeTab: '{{ $activeTab }}' }" class="mb-5">
        <div class="flex items-center gap-1 bg-surface rounded-xl p-1 border border-border w-fit">
            <button @click="activeTab = 'list'" :class="activeTab === 'list' ? 'bg-card text-accent shadow-sm' : 'text-secondary hover:text-primary'" class="px-4 py-2 text-xs font-medium rounded-lg transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                List
            </button>
            <button @click="activeTab = 'kanban'" :class="activeTab === 'kanban' ? 'bg-card text-accent shadow-sm' : 'text-secondary hover:text-primary'" class="px-4 py-2 text-xs font-medium rounded-lg transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Kanban
            </button>
            <button @click="activeTab = 'gantt'" :class="activeTab === 'gantt' ? 'bg-card text-accent shadow-sm' : 'text-secondary hover:text-primary'" class="px-4 py-2 text-xs font-medium rounded-lg transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Gantt Chart
            </button>
        </div>

        <!-- List View -->
        <div x-show="activeTab === 'list'" class="bg-card rounded-2xl border border-border overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-medium text-secondary uppercase tracking-wider border-b border-border bg-surface">
                        <th class="px-5 py-3">Task</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Due Date</th>
                        <th class="px-5 py-3">Progress</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($tasks as $task)
                        <tr class="hover:bg-surface group transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-1 h-8 rounded-full mt-1 bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : ($task->priority === 'medium' ? 'info' : 'tertiary')) }}"></div>
                                    <div>
                                        <a href="{{ route('tasks.show', [$student, $task]) }}" class="font-medium text-primary hover:text-accent block">{{ $task->title }}</a>
                                        @if($task->description)
                                            <p class="text-xs text-secondary mt-0.5 line-clamp-1 max-w-xs">{{ \Illuminate\Support\Str::limit($task->description, 60) }}</p>
                                        @endif
                                        @if($task->subtasks->count())
                                            <span class="inline-flex items-center gap-1 mt-1 text-xs text-secondary">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                                                </svg>
                                                {{ $task->subtasks->count() }} subtask{{ $task->subtasks->count() > 1 ? 's' : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3"><x-status-badge :status="$task->status" size="sm" /></td>
                            <td class="px-5 py-3">
                                <x-badge :color="match($task->priority) { 'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', default => 'gray' }" size="sm">{{ ucfirst($task->priority) }}</x-badge>
                            </td>
                            <td class="px-5 py-3">
                                @if($task->due_date)
                                    @if($task->due_date->isPast() && !$task->completed_at)
                                        <span class="text-danger flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ $task->due_date->format('M d') }}
                                        </span>
                                    @elseif($task->due_date->isTomorrow())
                                        <span class="text-warning flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            {{ $task->due_date->format('M d') }}
                                        </span>
                                    @else
                                        <span class="text-secondary">{{ $task->due_date->format('M d') }}</span>
                                    @endif
                                @else
                                    <span class="text-tertiary">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-border-light rounded-full h-1.5 overflow-hidden">
                                        <div class="bg-accent h-1.5 rounded-full transition-all duration-300" style="width: {{ $task->progress }}%"></div>
                                    </div>
                                    <span class="text-xs text-secondary">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <!-- Quick Actions Dropdown -->
                                    <div x-data="{ open: false }" class="relative">
                                        <button @click.stop="open = !open" @click.outside="open = false" class="p-1.5 text-secondary hover:text-primary hover:bg-surface rounded-lg transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                            </svg>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-1 w-48 bg-card rounded-xl border border-border shadow-lg py-1 z-50">
                                            <a href="{{ route('tasks.show', [$student, $task]) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-secondary hover:bg-surface hover:text-primary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7"/>
                                                </svg>
                                                View Details
                                            </a>
                                            <a href="{{ route('tasks.edit', [$student, $task]) }}" class="flex items-center gap-2 px-3 py-2 text-xs text-secondary hover:bg-surface hover:text-primary">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2h2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit Task
                                            </a>
                                            <hr class="border-border my-1">
                                            <form action="{{ route('tasks.destroy', [$student, $task]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-danger hover:bg-danger/5 text-left">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 011-1h2a1 1 0 011 1v3M4 7h16"/>
                                                    </svg>
                                                    Delete Task
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-accent/5 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-accent/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-semibold text-primary mb-1">No tasks yet</h3>
                                    <p class="text-xs text-secondary max-w-xs">Create your first task to get started with tracking your research progress.</p>
                                    <x-button href="{{ route('tasks.create', $student) }}" variant="accent" size="sm" class="mt-4">Create Task</x-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Pagination -->
            @if($tasks->hasPages())
            <div class="px-5 py-4 border-t border-border flex items-center justify-between">
                <span class="text-xs text-secondary">
                    Showing {{ $tasks->firstItem() }} to {{ $tasks->lastItem() }} of {{ $tasks->total() }} tasks
                </span>
                <div class="flex items-center gap-1">
                    @if($tasks->onFirstPage())
                        <span class="px-3 py-1.5 text-xs text-secondary rounded-lg border border-border">Previous</span>
                    @else
                        <a href="{{ $tasks->appends(['status' => request()->get('status')])->previousPageUrl() }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-border text-secondary hover:text-primary hover:bg-surface transition-all">Previous</a>
                    @endif
                    @if($tasks->hasMorePages())
                        <a href="{{ $tasks->appends(['status' => request()->get('status')])->nextPageUrl() }}" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-border text-secondary hover:text-primary hover:bg-surface transition-all">Next</a>
                    @else
                        <span class="px-3 py-1.5 text-xs text-secondary rounded-lg border border-border">Next</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Kanban View -->
        <div x-show="activeTab === 'kanban'" class="bg-card rounded-2xl border border-border p-4">
            @php
                $allTasks = $student->tasks()->whereNull('parent_id')->with('subtasks')->orderBy('sort_order')->get()->groupBy('status');
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
                $nextStatus = [
                    'planned' => 'in_progress',
                    'in_progress' => 'waiting_review',
                    'waiting_review' => 'completed',
                    'revision' => 'waiting_review',
                    'completed' => null,
                ];
            @endphp
            <div x-data="initKanbanBoard({ studentId: {{ $student->id }} })" x-init="init()" x-cloak class="flex gap-4 overflow-x-auto pb-4">
                @foreach($columns as $status => $label)
                    <div class="flex-shrink-0 w-72">
                        <div class="flex items-center gap-2 mb-3 sticky top-0 bg-card py-2 z-10">
                            <div class="w-2 h-2 rounded-full {{ $columnColors[$status] }}"></div>
                            <h3 class="text-xs font-semibold text-secondary uppercase tracking-wider">{{ $label }}</h3>
                            <span class="column-count text-[10px] text-secondary/60 bg-surface px-1.5 rounded">{{ ($allTasks[$status] ?? collect())->count() }}</span>
                        </div>
                        <div
                            data-kanban-column="{{ $status }}"
                            class="kanban-column space-y-2 min-h-[400px] p-2 rounded-lg bg-surface border border-border transition-colors"
                        >
                            @foreach(($allTasks[$status] ?? []) as $task)
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
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .kanban-card.animate-in {
                    animation: slideIn 0.2s ease-out;
                }
            </style>
            @endpush
        </div>

        <!-- Gantt Chart View -->
        <div x-show="activeTab === 'gantt'" class="bg-card rounded-2xl border border-border overflow-hidden">
            <div x-data="ganttChartApp({{ $student->id }})" x-init="init()" x-cloak>
                <div class="flex items-center justify-between p-4 border-b border-border">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1 bg-surface rounded-lg p-0.5 border border-border">
                            <button @click="setView('Day')" :class="viewMode === 'Day' ? 'bg-card text-accent shadow-sm' : 'text-secondary hover:text-primary'" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all">Day</button>
                            <button @click="setView('Week')" :class="viewMode === 'Week' ? 'bg-card text-accent shadow-sm' : 'text-secondary hover:text-primary'" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all">Week</button>
                            <button @click="setView('Month')" :class="viewMode === 'Month' ? 'bg-card text-accent shadow-sm' : 'text-secondary hover:text-primary'" class="px-2.5 py-1 text-xs font-medium rounded-md transition-all">Month</button>
                        </div>

                        <div class="hidden xl:flex items-center gap-3 text-xs text-secondary">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                                Planned
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                                In Progress
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                                Review
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-violet-500"></span>
                                Revision
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                Completed
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <!-- New Task Button -->
                        <button @click="openTaskModal()" class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-accent text-white hover:bg-amber-700 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New Task
                        </button>
                        <button @click="refresh()" class="p-2 text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all" title="Refresh">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="loading" class="flex flex-col items-center justify-center py-20">
                    <div class="relative">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent/20 to-accent/5 flex items-center justify-center">
                            <svg class="w-6 h-6 text-accent animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-secondary mt-4">Loading Gantt Chart...</p>
                </div>

                <div x-show="!loading" class="gantt-split-layout">
                    <div class="flex border-b border-border bg-surface/50">
                        <div class="flex-shrink-0 w-[260px] px-4 py-3 border-r border-border">
                            <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Task Name</span>
                        </div>
                        <div class="flex-1 px-4 py-3">
                            <span class="text-xs font-semibold text-secondary uppercase tracking-wider">Timeline</span>
                        </div>
                    </div>

                    <div class="flex" style="max-height: 560px;">
                        <div class="gantt-task-list flex-shrink-0 w-[260px] border-r border-border bg-card overflow-y-auto">
                            <template x-if="tasks.length === 0">
                                <div class="flex flex-col items-center justify-center py-16 text-center px-4">
                                    <svg class="w-12 h-12 text-tertiary mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                    <h3 class="text-sm font-medium text-primary mb-1">No tasks yet</h3>
                                    <p class="text-xs text-secondary mb-4">Click "New Task" or click on the timeline to create tasks</p>
                                    <button @click="openTaskModal()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium rounded-lg bg-accent text-white hover:bg-amber-700 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Create First Task
                                    </button>
                                </div>
                            </template>

                            <template x-for="task in tasks" :key="task.id">
                                <a :href="`/students/${studentId}/tasks/${task.task_id}`"
                                   class="gantt-task-row flex items-center gap-3 px-4 py-3 hover:bg-surface transition-colors border-b border-border-light"
                                   style="min-height: 56px;">
                                    <span class="flex-shrink-0 w-2.5 h-2.5 rounded-full"
                                          :style="`background-color: ${statusColors[task.status] || '#9CA3AF'}`"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-primary truncate" x-text="task.name"></p>
                                        <p class="text-xs text-secondary" x-text="formatDateRange(task.start, task.end)"></p>
                                    </div>
                                    <span class="text-xs text-tertiary" x-text="`${task.progress || 0}%`"></span>
                                </a>
                            </template>
                        </div>

                        <div class="gantt-timeline flex-1 overflow-auto bg-card">
                            <div id="gantt-chart-container" class="min-w-[1100px]" style="min-height: 400px; cursor: crosshair;" @click="handleTimelineClick"></div>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 border-t border-border text-center text-xs text-secondary">
                    <p>Colors: blue = planned, amber = in progress, orange = review, violet = revision, green = completed</p>
                    <p class="mt-1">Drag task ends to adjust dates • Click on timeline to create task • Click task to view details</p>
                </div>
            </div>

            <!-- Task Creation Modal -->
            <div x-show="showTaskModal" style="display: none;" x-cloak>
                <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <!-- Backdrop -->
                    <div x-show="showTaskModal" x-transition:enter="transition-opacity ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute inset-0 bg-primary/20 backdrop-blur-sm" @click="closeTaskModal()"></div>

                    <!-- Modal Content -->
                    <div x-show="showTaskModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="relative bg-card rounded-2xl border border-border shadow-xl w-full max-w-lg">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-border">
                            <div>
                                <h3 class="text-base font-semibold text-primary">Create New Task</h3>
                                <p class="text-xs text-secondary mt-0.5">Fill in the task details below</p>
                            </div>
                            <button @click="closeTaskModal()" class="p-2 text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <!-- Form -->
                        <form @submit.prevent="createTask()" class="p-6 space-y-4">
                            @csrf
                            <input type="hidden" name="student_id" :value="studentId">

                            <!-- Task Name -->
                            <div>
                                <label class="block text-xs font-medium text-secondary mb-1.5">Task Name *</label>
                                <input type="text" name="name" x-model="newTask.name" required
                                    placeholder="Enter task name..."
                                    class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                            </div>

                            <!-- Dates Row -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Start Date *</label>
                                    <input type="date" name="start_date" x-model="newTask.start_date" required
                                        class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Due Date *</label>
                                    <input type="date" name="due_date" x-model="newTask.due_date" required
                                        class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                </div>
                            </div>

                            <!-- Priority & Status -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Priority</label>
                                    <select name="priority" x-model="newTask.priority" class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-secondary mb-1.5">Status</label>
                                    <select name="status" x-model="newTask.status" class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                        <option value="planned" selected>Planned</option>
                                        <option value="in_progress">In Progress</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-xs font-medium text-secondary mb-1.5">Description</label>
                                <textarea name="description" x-model="newTask.description" rows="3"
                                    placeholder="Task description..."
                                    class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all resize-none"></textarea>
                            </div>

                            <!-- Milestone Selection (Optional) -->
                            <div>
                                <label class="block text-xs font-medium text-secondary mb-1.5">Milestone (Optional)</label>
                                <select name="milestone_id" x-model="newTask.milestone_id" class="w-full px-3 py-2 text-sm border border-border rounded-lg focus:ring-2 focus:ring-accent/20 focus:border-accent transition-all">
                                    <option value="">No milestone</option>
                                    @foreach($milestones ?? [] as $milestone)
                                    <option value="{{ $milestone->id }}">{{ $milestone->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Error Message -->
                            <div x-show="taskError" x-cloak class="p-3 rounded-lg bg-danger/10 text-danger text-xs">
                                <span x-text="taskError"></span>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" @click="closeTaskModal()" class="px-4 py-2 text-sm font-medium text-secondary hover:text-primary border border-border hover:bg-surface rounded-lg transition-all">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="taskSubmitting" class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-accent text-white hover:bg-amber-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg x-show="!taskSubmitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <svg x-show="taskSubmitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Create Task
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

            @push('styles')
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.css">
            <style>
                .gantt-task-list::-webkit-scrollbar { width: 6px; }
                .gantt-task-list::-webkit-scrollbar-track { background: transparent; }
                .gantt-task-list::-webkit-scrollbar-thumb { background-color: #E5E5E4; border-radius: 9999px; }
                .gantt-timeline::-webkit-scrollbar { width: 8px; height: 8px; }
                .gantt-timeline::-webkit-scrollbar-track { background: #FAFAF9; }
                .gantt-timeline::-webkit-scrollbar-thumb { background-color: #D6D3D1; border-radius: 9999px; }
                .gantt-task-row.active { background: rgba(217, 119, 6, 0.08); }
            </style>
            @endpush
        </div>
    </div>

    @push('scripts')
    <script>
        const KANBAN_COLUMNS = {
            planned: { label: 'Planned', color: 'bg-info' },
            in_progress: { label: 'In Progress', color: 'bg-warning' },
            waiting_review: { label: 'Review', color: 'bg-accent' },
            revision: { label: 'Revision', color: 'bg-purple-500' },
            completed: { label: 'Completed', color: 'bg-success' }
        };

        // Gantt Chart Component for index page
        document.addEventListener('alpine:init', () => {
            window.ganttChartApp = function(studentId) {
                return {
                    loading: true,
                    tasks: [],
                    ganttInstance: null,
                    studentId: studentId,
                    viewMode: 'Month',
                    taskStats: { total: 0, completed: 0, inProgress: 0 },
                    statusColors: {
                        'planned': '#3B82F6',
                        'in_progress': '#F59E0B',
                        'waiting_review': '#F97316',
                        'revision': '#8B5CF6',
                        'completed': '#10B981'
                    },
                    showTaskModal: false,
                    taskSubmitting: false,
                    taskError: '',
                    newTask: {
                        name: '',
                        start_date: '',
                        due_date: '',
                        priority: 'medium',
                        status: 'planned',
                        description: '',
                        milestone_id: ''
                    },

                    init() {
                        this.loadGanttLibrary().then(() => {
                            return this.loadTasks();
                        }).then(() => {
                            this.loading = false;
                            this.$nextTick(() => {
                                this.renderGantt();
                            });
                        }).catch((error) => {
                            console.error('Gantt init failed:', error);
                            this.loading = false;
                        });
                    },

                    loadGanttLibrary() {
                        return new Promise((resolve, reject) => {
                            if (typeof Gantt !== 'undefined') {
                                resolve();
                                return;
                            }
                            const script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js';
                            script.onload = resolve;
                            script.onerror = reject;
                            document.head.appendChild(script);
                        });
                    },

                    async loadTasks() {
                        const response = await axios.get('/api/students/' + this.studentId + '/tasks/gantt');
                        this.tasks = response.data.sort((a, b) => {
                            if (a.start !== b.start) return new Date(a.start) - new Date(b.start);
                            return a.name.localeCompare(b.name);
                        });
                        this.taskStats = {
                            total: this.tasks.length,
                            completed: this.tasks.filter(t => t.progress === 100).length,
                            inProgress: this.tasks.filter(t => t.progress > 0 && t.progress < 100).length
                        };
                    },

                    renderGantt() {
                        const container = document.getElementById('gantt-chart-container');
                        if (!container) return;

                        container.innerHTML = '';

                        if (this.tasks.length === 0) return;

                        this.ganttInstance = new Gantt(container, this.tasks, {
                            view_mode: this.viewMode,
                            date_format: 'YYYY-MM-DD',
                            header_height: 48,
                            bar_height: 28,
                            bar_corner_radius: 4,
                            padding: 14,
                            on_date_change: (task, start, end) => this.handleDateChange(task, start, end),
                            on_progress_change: (task, progress) => this.handleProgressChange(task, progress),
                            on_click: (task) => {
                                const taskId = task.id ? task.id.replace('task-', '') : task.task_id;
                                if (taskId) {
                                    window.location.href = '/students/' + this.studentId + '/tasks/' + taskId;
                                }
                            }
                        });

                        this.applyCustomStyles();
                        this.$nextTick(() => this.initScrollSync());
                    },

                    handleDateChange(task, start, end) {
                        const startDate = start.toISOString().split('T')[0];
                        const dueDate = end.toISOString().split('T')[0];
                        const taskId = task.id.replace('task-', '');
                        axios.put('/api/tasks/' + taskId + '/dates', { start_date: startDate, due_date: dueDate })
                            .then(() => console.log('Dates updated'));
                    },

                    handleProgressChange(task, progress) {
                        const taskId = task.id.replace('task-', '');
                        axios.put('/api/tasks/' + taskId + '/progress', { progress })
                            .then(() => console.log('Progress updated'));
                    },

                    setView(mode) {
                        this.viewMode = mode;
                        if (this.ganttInstance) {
                            this.ganttInstance.change_view_mode(mode);
                        }
                    },

                    initScrollSync() {
                        const taskList = document.querySelector('.gantt-task-list');
                        const timeline = document.querySelector('.gantt-timeline');

                        if (!taskList || !timeline || taskList.dataset.scrollBound === 'true') {
                            return;
                        }

                        let syncing = false;

                        taskList.addEventListener('scroll', () => {
                            if (syncing) return;
                            syncing = true;
                            timeline.scrollTop = taskList.scrollTop;
                            requestAnimationFrame(() => { syncing = false; });
                        });

                        timeline.addEventListener('scroll', () => {
                            if (syncing) return;
                            syncing = true;
                            taskList.scrollTop = timeline.scrollTop;
                            requestAnimationFrame(() => { syncing = false; });
                        });

                        taskList.dataset.scrollBound = 'true';
                    },

                    applyCustomStyles() {
                        const styleId = 'gantt-custom-styles';
                        let styleEl = document.getElementById(styleId);

                        if (!styleEl) {
                            styleEl = document.createElement('style');
                            styleEl.id = styleId;
                            document.head.appendChild(styleEl);
                        }

                        styleEl.textContent =
                            '.gantt .bar { fill: #D97706; cursor: pointer; transition: fill 0.2s, filter 0.2s; }' +
                            '.gantt .bar:hover { filter: brightness(1.08); }' +
                            '.gantt-completed .bar { fill: #10B981; }' +
                            '.gantt-in-progress .bar { fill: #F59E0B; }' +
                            '.gantt-waiting-review .bar { fill: #F97316; }' +
                            '.gantt-revision .bar { fill: #8B5CF6; }' +
                            '.gantt-planned .bar { fill: #3B82F6; }' +
                            '.gantt .bar-label { display: none; }' +
                            '.gantt .grid-header { fill: #FAFAF9; stroke: #E5E5E4; }' +
                            '.gantt .grid-row { fill: transparent; }' +
                            '.gantt .grid-text { fill: #57534E; font-size: 11px; font-weight: 600; }' +
                            '.gantt .tick { stroke: #E7E5E4; }';
                    },

                    formatDateRange(start, end) {
                        const startDate = new Date(start);
                        const endDate = new Date(end);

                        if (startDate.getMonth() === endDate.getMonth() && startDate.getFullYear() === endDate.getFullYear()) {
                            return startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' + endDate.getDate();
                        }

                        return startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' +
                            endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    },

                    refresh() {
                        this.loadTasks().then(() => this.$nextTick(() => this.renderGantt()));
                    },

                    // Task Modal Functions
                    openTaskModal(preselectedDate = null) {
                        this.showTaskModal = true;
                        this.taskError = '';

                        if (preselectedDate) {
                            this.newTask.start_date = preselectedDate;
                            this.newTask.due_date = preselectedDate;
                        } else {
                            // Default to today and 7 days from now
                            const today = new Date().toISOString().split('T')[0];
                            const nextWeek = new Date();
                            nextWeek.setDate(nextWeek.getDate() + 7);
                            this.newTask.start_date = today;
                            this.newTask.due_date = nextWeek.toISOString().split('T')[0];
                        }

                        // Focus on name field after modal opens
                        this.$nextTick(() => {
                            document.querySelector('input[name="name"]')?.focus();
                        });
                    },

                    closeTaskModal() {
                        this.showTaskModal = false;
                        this.taskError = '';
                        this.resetTaskForm();
                    },

                    resetTaskForm() {
                        this.newTask = {
                            name: '',
                            start_date: '',
                            due_date: '',
                            priority: 'medium',
                            status: 'planned',
                            description: '',
                            milestone_id: ''
                        };
                    },

                    async createTask() {
                        this.taskSubmitting = true;
                        this.taskError = '';

                        try {
                            const response = await axios.post('/students/' + this.studentId + '/tasks', this.newTask);

                            // Show notification
                            const notification = document.createElement('div');
                            notification.className = 'fixed bottom-4 right-4 px-4 py-3 rounded-xl shadow-lg text-sm font-medium z-50 flex items-center gap-2 bg-success text-white';
                            notification.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Task created successfully!';
                            document.body.appendChild(notification);
                            setTimeout(() => notification.remove(), 3000);

                            this.closeTaskModal();

                            // Reload tasks and re-render
                            await this.loadTasks();
                            this.renderGantt();
                        } catch (error) {
                            console.error('Task creation failed:', error);
                            this.taskError = error.response?.data?.message || 'Failed to create task. Please try again.';
                        } finally {
                            this.taskSubmitting = false;
                        }
                    },

                    // Handle click on timeline to create task
                    handleTimelineClick(event) {
                        // Check if click was on a task bar or empty space
                        const target = event.target;
                        if (target.closest('.bar-wrapper') || target.closest('.bar')) {
                            return; // Don't trigger if clicking on a task
                        }

                        // Get the clicked date from the Gantt chart
                        const container = document.getElementById('gantt-chart-container');
                        if (!container || !this.ganttInstance) return;

                        // Calculate the date from click position
                        const rect = container.getBoundingClientRect();
                        const clickX = event.clientX - rect.left + document.querySelector('.gantt-timeline')?.scrollLeft || 0;

                        // Estimate date from position (this is approximate)
                        const daysInView = this.getDaysInView();
                        const startDate = new Date(this.ganttInstance.gantt_start);
                        const dayWidth = rect.width / daysInView;
                        const dayOffset = Math.floor(clickX / dayWidth);

                        const clickedDate = new Date(startDate);
                        clickedDate.setDate(startDate.getDate() + dayOffset);

                        const dateStr = clickedDate.toISOString().split('T')[0];

                        // Open modal with pre-selected date
                        this.openTaskModal(dateStr);
                    },

                    getDaysInView() {
                        const start = new Date(this.ganttInstance.gantt_start);
                        const end = new Date(this.ganttInstance.gantt_end);
                        const diffTime = Math.abs(end - start);
                        return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    }
                };
            };
        });
    </script>
    @endpush
</x-layouts.app>
