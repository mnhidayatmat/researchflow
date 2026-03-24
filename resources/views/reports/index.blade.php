<x-layouts.app title="Progress Reports">
    <x-slot:header>Progress Reports</x-slot:header>

    <div class="mb-5 sm:mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-secondary dark:text-dark-secondary">{{ $student->user->name }}'s reports</p>
        <x-button href="{{ route('reports.create', $student) }}" variant="accent" size="sm" class="w-full justify-center sm:w-auto">+ New Report</x-button>
    </div>

    <div class="space-y-3">
        @forelse($reports as $report)
            <a href="{{ route('reports.show', [$student, $report]) }}" class="block group">
                <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4 sm:p-5 hover:border-accent/20 hover:shadow-soft transition-all active:bg-surface/80 dark:active:bg-dark-surface/80">
                    <div class="flex items-start gap-3 sm:gap-4">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="text-sm font-semibold text-primary dark:text-dark-primary group-hover:text-accent transition-colors truncate">{{ $report->title }}</h3>
                                <x-status-badge :status="$report->status" size="sm" />
                            </div>
                            <p class="text-xs text-secondary dark:text-dark-secondary mt-1">
                                {{ $report->type_label }}
                                <span class="text-tertiary dark:text-dark-tertiary">&middot;</span>
                                {{ $report->created_at->format('d M Y') }}
                                @if($report->period_start && $report->period_end)
                                    <span class="hidden sm:inline">
                                        <span class="text-tertiary dark:text-dark-tertiary">&middot;</span>
                                        {{ $report->period_start->format('d M') }} — {{ $report->period_end->format('d M') }}
                                    </span>
                                @endif
                            </p>
                            @if($report->attachment_path)
                                <div class="flex items-center gap-1 mt-1.5 text-[10px] sm:text-[11px] text-secondary dark:text-dark-secondary">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    <span class="truncate">{{ $report->attachment_original_name }}</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-primary dark:text-dark-primary">No reports yet</p>
                    <p class="text-xs text-secondary dark:text-dark-secondary mt-1">Submit your first progress report to get started.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($reports->hasPages())
    <div class="mt-4">{{ $reports->links() }}</div>
    @endif
</x-layouts.app>
