<x-layouts.app title="Select Student">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Select Student</h2>
            <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Choose a student to view as {{ ucfirst($role) }}</p>
        </div>

        <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border overflow-hidden">
            @if($students->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-surface dark:bg-dark-surface flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-tertiary dark:text-dark-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-primary dark:text-dark-primary mb-1">No Students Found</h3>
                    <p class="text-sm text-secondary dark:text-dark-secondary">No active students available for {{ ucfirst($role) }} view.</p>
                </div>
            @else
                <form method="POST" action="{{ route('admin.role-switch-student') }}">
                    @csrf
                    <div class="divide-y divide-border">
                        @foreach($students as $student)
                            <label class="flex items-center gap-4 p-5 hover:bg-surface dark:hover:bg-dark-surface dark:bg-dark-surface cursor-pointer transition-colors group">
                                <input type="radio" name="student_id" value="{{ $student->id }}"
                                       class="w-4 h-4 text-accent border-border dark:border-dark-border focus:ring-accent/20"
                                       required>

                                <x-avatar :name="$student->user->name" size="md" />

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-primary dark:text-dark-primary group-hover:text-accent transition-colors truncate">
                                        {{ $student->user->name }}
                                    </p>
                                    <p class="text-xs text-secondary dark:text-dark-secondary">{{ $student->programme->name ?? 'No Programme' }}</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    @if($student->user->email)
                                        <span class="text-xs text-secondary dark:text-dark-secondary truncate max-w-[150px]">
                                            {{ $student->user->email }}
                                        </span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="px-6 py-4 border-t border-border dark:border-dark-border bg-surface/50 flex items-center justify-between">
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary dark:text-dark-primary">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-accent text-white hover:bg-amber-700 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                            View as {{ ucfirst($role) }}
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-layouts.app>
