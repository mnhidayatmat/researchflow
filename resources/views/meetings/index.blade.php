<x-layouts.app title="Meetings">
    <x-slot:header>Meetings</x-slot:header>

    <div class="mb-5 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-secondary dark:text-dark-secondary">{{ $student->user->name }}'s meetings</p>
        <x-button href="{{ route('meetings.create', $student) }}" variant="accent" size="sm" class="w-full justify-center sm:w-auto">+ Schedule Meeting</x-button>
    </div>

    <div class="space-y-3">
        @forelse($meetings as $meeting)
            <a href="{{ route('meetings.show', [$student, $meeting]) }}" class="block group">
                <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4 sm:p-5 hover:border-accent/20 hover:shadow-soft transition-all active:bg-surface/80 dark:active:bg-dark-surface/80">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center shrink-0
                            {{ $meeting->status === 'completed' ? 'bg-gradient-to-br from-success/20 to-success/10' :
                               ($meeting->status === 'cancelled' ? 'bg-gradient-to-br from-tertiary/10 to-tertiary/5' :
                               'bg-gradient-to-br from-info/20 to-info/10') }}">
                            <svg class="w-5 h-5 {{ $meeting->status === 'completed' ? 'text-success' : ($meeting->status === 'cancelled' ? 'text-tertiary' : 'text-info') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary group-hover:text-accent transition-colors truncate">{{ $meeting->title }}</h3>
                                <x-status-badge :status="$meeting->status" size="sm" />
                            </div>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-1 text-xs text-secondary dark:text-dark-secondary">
                                <span>{{ $meeting->scheduled_at->format('d M Y, h:i A') }}</span>
                                <span class="text-tertiary dark:text-dark-tertiary hidden sm:inline">&middot;</span>
                                <span class="hidden sm:inline">{{ ucfirst(str_replace('_', ' ', $meeting->type)) }}</span>
                                <span class="text-tertiary dark:text-dark-tertiary hidden sm:inline">&middot;</span>
                                <span class="hidden sm:inline">{{ ucfirst(str_replace('_', ' ', $meeting->mode)) }}</span>
                                @if($meeting->duration_minutes)
                                <span class="text-tertiary dark:text-dark-tertiary hidden sm:inline">&middot;</span>
                                <span class="hidden sm:inline">{{ $meeting->duration_minutes }}min</span>
                                @endif
                            </div>
                            {{-- Mobile-only: compact meta --}}
                            <p class="text-xs text-tertiary dark:text-dark-tertiary mt-0.5 sm:hidden">
                                {{ ucfirst(str_replace('_', ' ', $meeting->type)) }} · {{ ucfirst(str_replace('_', ' ', $meeting->mode)) }}
                                @if($meeting->duration_minutes) · {{ $meeting->duration_minutes }}min @endif
                            </p>
                            @if($meeting->actionItems->count())
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <div class="flex-1 max-w-[100px] h-1.5 bg-border-light dark:bg-dark-border rounded-full overflow-hidden">
                                        @php $donePercent = $meeting->actionItems->count() > 0 ? ($meeting->actionItems->where('is_completed', true)->count() / $meeting->actionItems->count()) * 100 : 0; @endphp
                                        <div class="h-full bg-success rounded-full" style="width: {{ $donePercent }}%"></div>
                                    </div>
                                    <span class="text-[10px] text-secondary dark:text-dark-secondary">
                                        {{ $meeting->actionItems->where('is_completed', true)->count() }}/{{ $meeting->actionItems->count() }} actions
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-8 sm:p-12">
                <div class="flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-info/15 to-info/5 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-primary dark:text-dark-primary">No meetings yet</p>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Schedule your first meeting to get started.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($meetings->hasPages())
    <div class="mt-4">{{ $meetings->links() }}</div>
    @endif
</x-layouts.app>
