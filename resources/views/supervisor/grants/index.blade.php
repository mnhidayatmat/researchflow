<x-layouts.app title="Grants">
    <x-slot:header>Grants</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Grant Pipeline</h2>
                <p class="mt-0.5 text-xs text-secondary dark:text-dark-secondary">Track funding applications and submission readiness.</p>
            </div>
            <x-button href="{{ route('supervisor.grants.create') }}" variant="primary" class="w-full justify-center sm:w-auto">New Grant</x-button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Total Grants</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $stats['total'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Submitted</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $stats['submitted'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Open Pipeline</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">{{ $stats['open'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary dark:text-dark-secondary">Total Amount</p>
                <p class="mt-2 text-2xl font-semibold text-primary dark:text-dark-primary">RM {{ number_format($stats['total_amount'], 2) }}</p>
            </x-card>
        </div>

        <x-card>
            <form method="GET" action="{{ route('supervisor.grants.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
                <div class="flex-1 min-w-0 sm:min-w-[220px]">
                    <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Proposal title, grant name..." class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-3 sm:contents">
                    <div class="sm:min-w-[160px]">
                        <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Stage</label>
                        <input type="text" name="stage" value="{{ request('stage') }}" placeholder="Submitted..." class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                    </div>
                    <div class="sm:min-w-[140px]">
                        <label class="mb-1 block text-xs font-medium text-secondary dark:text-dark-secondary">Scope</label>
                        <select name="scope" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                            <option value="">All</option>
                            <option value="international" @selected(request('scope') === 'international')>International</option>
                            <option value="national" @selected(request('scope') === 'national')>National</option>
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <x-button type="submit" variant="primary" size="sm" class="flex-1 justify-center sm:flex-none">Filter</x-button>
                    @if(request()->hasAny(['search', 'stage', 'scope']))
                        <x-button href="{{ route('supervisor.grants.index') }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Clear</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        @if($grants->isEmpty())
            <x-card>
                <div class="py-12 text-center">
                    <p class="text-base font-semibold text-primary dark:text-dark-primary">No grant records yet</p>
                    <p class="mt-1 text-sm text-secondary dark:text-dark-secondary">Create your first grant to track application stages and submission checklist progress.</p>
                </div>
            </x-card>
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach($grants as $grant)
                    <a href="{{ route('supervisor.grants.show', $grant) }}" class="group block">
                        <x-card class="h-full border border-border dark:border-dark-border/80 transition-all hover:border-accent/30 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-primary dark:text-dark-primary transition-colors group-hover:text-accent">{{ $grant->proposal_title }}</p>
                                    <p class="mt-1 text-xs text-secondary dark:text-dark-secondary">{{ $grant->grant_name }} | {{ $grant->grant_type }}</p>
                                </div>
                                <span class="rounded-full bg-surface dark:bg-dark-surface px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-secondary dark:text-dark-secondary">{{ $grant->stage }}</span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Amount</p>
                                    <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->formatted_amount }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Scope</p>
                                    <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ ucfirst($grant->scope) }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Deadline</p>
                                    <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->deadline?->format('j M Y') ?? 'TBA' }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Rejected</p>
                                    <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $grant->rejection_count }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Checklist Completion</span>
                                    <span class="text-xs font-medium text-primary dark:text-dark-primary">{{ $grant->checklist_completion }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-border">
                                    <div class="h-2 rounded-full bg-accent" style="width: {{ $grant->checklist_completion }}%"></div>
                                </div>
                            </div>
                        </x-card>
                    </a>
                @endforeach
            </div>

            @if($grants->hasPages())
                <div>{{ $grants->withQueryString()->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
