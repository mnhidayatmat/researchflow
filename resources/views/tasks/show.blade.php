<x-layouts.app title="{{ $task->title }}">
    <x-slot:header>Task Detail</x-slot:header>

    <div class="max-w-3xl">
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-5 sm:mb-6">
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-semibold text-primary dark:text-dark-primary">{{ $task->title }}</h2>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Created {{ $task->created_at->diffForHumans() }} @if($task->assignedBy) by {{ $task->assignedBy->name }} @endif</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-button href="{{ route('tasks.edit', [$student, $task]) }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Edit</x-button>
                <form method="POST" action="{{ route('tasks.destroy', [$student, $task]) }}" onsubmit="return confirm('Delete this task?')" class="flex-1 sm:flex-none">
                    @csrf @method('DELETE')
                    <x-button type="submit" variant="ghost" size="sm" class="text-red-500 hover:text-red-700 w-full justify-center sm:w-auto">Delete</x-button>
                </form>
            </div>
        </div>

        {{-- Mobile: Status bar --}}
        <div class="flex items-center gap-3 mb-4 sm:hidden bg-card dark:bg-dark-card rounded-xl border border-border dark:border-dark-border p-3">
            <div class="flex items-center gap-2 flex-1">
                <x-status-badge :status="$task->status" />
                <x-badge :color="match($task->priority) { 'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', default => 'gray' }" size="sm">{{ ucfirst($task->priority) }}</x-badge>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-16 bg-gray-100 dark:bg-dark-border rounded-full h-1.5">
                    <div class="bg-accent h-1.5 rounded-full" style="width: {{ $task->progress }}%"></div>
                </div>
                <span class="text-xs font-medium text-secondary dark:text-dark-secondary">{{ $task->progress }}%</span>
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-4 sm:gap-6">
            <div class="lg:col-span-2 space-y-4">
                <x-card title="Description">
                    <div class="text-sm text-secondary dark:text-dark-secondary leading-relaxed">
                        {!! nl2br(e($task->description ?? 'No description.')) !!}
                    </div>
                </x-card>

                @if($task->subtasks->count())
                    <x-card title="Subtasks">
                        @foreach($task->subtasks as $sub)
                            <div class="flex items-center justify-between gap-3 py-2.5 {{ !$loop->last ? 'border-b border-border dark:border-dark-border' : '' }}">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-2 h-2 rounded-full shrink-0 {{ $sub->status === 'completed' ? 'bg-green-500' : 'bg-gray-300 dark:bg-dark-border' }}"></div>
                                    <span class="text-sm truncate {{ $sub->status === 'completed' ? 'line-through text-secondary dark:text-dark-secondary' : 'text-primary dark:text-dark-primary' }}">{{ $sub->title }}</span>
                                </div>
                                <x-status-badge :status="$sub->status" size="sm" />
                            </div>
                        @endforeach
                    </x-card>
                @endif

                @if($task->revisions->count())
                    <x-card title="Revisions">
                        @foreach($task->revisions as $rev)
                            <div class="py-3 {{ !$loop->last ? 'border-b border-border dark:border-dark-border' : '' }}">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <p class="text-sm font-medium text-primary dark:text-dark-primary truncate">{{ $rev->requestedBy->name }}</p>
                                    <x-status-badge :status="$rev->status" size="sm" />
                                </div>
                                <p class="text-sm text-secondary dark:text-dark-secondary">{{ $rev->description }}</p>
                                @foreach($rev->comments as $comment)
                                    <div class="ml-3 sm:ml-4 mt-2 pl-3 border-l-2 border-border dark:border-dark-border">
                                        <p class="text-xs text-secondary dark:text-dark-secondary"><strong>{{ $comment->user->name }}</strong> · {{ $comment->created_at->diffForHumans() }}</p>
                                        <p class="text-sm text-primary dark:text-dark-primary">{{ $comment->content }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </x-card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4 hidden sm:block">
                <x-card>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Status</p>
                            <x-status-badge :status="$task->status" />
                        </div>
                        <div>
                            <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Priority</p>
                            <x-badge :color="match($task->priority) { 'urgent' => 'red', 'high' => 'orange', 'medium' => 'yellow', default => 'gray' }">{{ ucfirst($task->priority) }}</x-badge>
                        </div>
                        <div>
                            <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Progress</p>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="flex-1 bg-gray-100 dark:bg-dark-border rounded-full h-2">
                                    <div class="bg-accent h-2 rounded-full" style="width: {{ $task->progress }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-primary dark:text-dark-primary">{{ $task->progress }}%</span>
                            </div>
                        </div>
                        @if($task->milestone)
                            <div>
                                <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Milestone</p>
                                <p class="text-sm text-primary dark:text-dark-primary">{{ $task->milestone->name }}</p>
                            </div>
                        @endif
                        @if($task->start_date)
                            <div>
                                <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Start</p>
                                <p class="text-sm text-primary dark:text-dark-primary">{{ $task->start_date->format('d M Y') }}</p>
                            </div>
                        @endif
                        @if($task->due_date)
                            <div>
                                <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Due</p>
                                <p class="text-sm text-primary dark:text-dark-primary">{{ $task->due_date->format('d M Y') }}</p>
                            </div>
                        @endif
                        @if($task->dependencies->count())
                            <div>
                                <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Dependencies</p>
                                @foreach($task->dependencies as $dep)
                                    <a href="{{ route('tasks.show', [$student, $dep]) }}" class="text-xs text-accent hover:underline block">{{ $dep->title }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
