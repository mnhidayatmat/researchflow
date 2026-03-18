<x-layouts.app title="Tasks">
    <x-slot:header>
        <div class="flex items-center gap-2">
            <span>Tasks</span>
            <span class="text-xs text-secondary">{{ $student->user->name }}</span>
        </div>
    </x-slot:header>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('tasks.index', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md {{ request()->routeIs('tasks.index') ? 'bg-primary text-white' : 'text-secondary hover:bg-gray-100' }}">List</a>
            <a href="{{ route('tasks.kanban', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Kanban</a>
            <a href="{{ route('tasks.gantt', $student) }}" class="px-3 py-1.5 text-xs font-medium rounded-md text-secondary hover:bg-gray-100">Timeline</a>
        </div>
        <x-button href="{{ route('tasks.create', $student) }}" variant="accent" size="sm">+ New Task</x-button>
    </div>

    <x-card :padding='false'>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs font-medium text-secondary uppercase tracking-wider border-b border-border">
                    <th class="px-5 py-3">Task</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Priority</th>
                    <th class="px-5 py-3">Due Date</th>
                    <th class="px-5 py-3">Progress</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-3">
                            <a href="{{ route('tasks.show', [$student, $task]) }}" class="font-medium text-primary hover:text-accent">{{ $task->title }}</a>
                            @if($task->subtasks->count())
                                <span class="text-xs text-secondary ml-1">{{ $task->subtasks->count() }} subtasks</span>
                            @endif
                        </td>
                        <td class="px-5 py-3"><x-status-badge :status="$task->status" /></td>
                        <td class="px-5 py-3">
                            <x-badge :color="match($task->priority) { 'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', default => 'gray' }">{{ ucfirst($task->priority) }}</x-badge>
                        </td>
                        <td class="px-5 py-3 text-secondary">{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-accent h-1.5 rounded-full" style="width: {{ $task->progress }}%"></div>
                                </div>
                                <span class="text-xs text-secondary">{{ $task->progress }}%</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('tasks.edit', [$student, $task]) }}" class="text-xs text-secondary hover:text-accent">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-secondary text-sm">No tasks yet. Create your first task.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </x-card>
</x-layouts.app>
