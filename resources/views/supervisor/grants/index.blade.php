<x-layouts.app title="Grants">
    <x-slot:header>Grants</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-primary">Grant Pipeline</h2>
                <p class="mt-0.5 text-xs text-secondary">Track funding applications, timelines, and submission readiness.</p>
            </div>
            <x-button href="{{ route('supervisor.grants.create') }}" variant="primary">New Grant</x-button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-card>
                <p class="text-xs text-secondary">Total Grants</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['total'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Submitted</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['submitted'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Open Pipeline</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['open'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Total Amount</p>
                <p class="mt-2 text-2xl font-semibold text-primary">RM {{ number_format($stats['total_amount'], 2) }}</p>
            </x-card>
        </div>

        <x-card>
            <form method="GET" action="{{ route('supervisor.grants.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1 block text-xs font-medium text-secondary">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Proposal title, grant name, type..." class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <div class="min-w-[180px]">
                    <label class="mb-1 block text-xs font-medium text-secondary">Stage</label>
                    <input type="text" name="stage" value="{{ request('stage') }}" placeholder="Submitted, Rejected..." class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <div class="min-w-[160px]">
                    <label class="mb-1 block text-xs font-medium text-secondary">Scope</label>
                    <select name="scope" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                        <option value="">All</option>
                        <option value="international" @selected(request('scope') === 'international')>International</option>
                        <option value="national" @selected(request('scope') === 'national')>National</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" size="sm">Filter</x-button>
                    @if(request()->hasAny(['search', 'stage', 'scope']))
                        <x-button href="{{ route('supervisor.grants.index') }}" variant="secondary" size="sm">Clear</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        @if($grants->isEmpty())
            <x-card>
                <div class="py-12 text-center">
                    <p class="text-base font-semibold text-primary">No grant records yet</p>
                    <p class="mt-1 text-sm text-secondary">Create your first grant to track application stages and submission checklist progress.</p>
                </div>
            </x-card>
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach($grants as $grant)
                    <a href="{{ route('supervisor.grants.show', $grant) }}" class="group block">
                        <x-card class="h-full border border-border/80 transition-all hover:border-accent/30 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-primary transition-colors group-hover:text-accent">{{ $grant->proposal_title }}</p>
                                    <p class="mt-1 text-xs text-secondary">{{ $grant->grant_name }} | {{ $grant->grant_type }}</p>
                                </div>
                                <span class="rounded-full bg-surface px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-secondary">{{ $grant->stage }}</span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Amount</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ $grant->formatted_amount }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Scope</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ ucfirst($grant->scope) }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Deadline</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ $grant->deadline?->format('j M Y') ?? 'TBA' }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Rejected</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ $grant->rejection_count }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-[10px] uppercase tracking-wide text-tertiary">Checklist Completion</span>
                                    <span class="text-xs font-medium text-primary">{{ $grant->checklist_completion }}%</span>
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
