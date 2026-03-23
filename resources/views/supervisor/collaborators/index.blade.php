<x-layouts.app title="Collaborators">
    <x-slot:header>Collaborators</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-semibold text-primary">Collaborator Directory</h2>
                <p class="mt-0.5 text-xs text-secondary">Store academic, industry, government, and other contacts for grants, papers, and reviewer suggestions.</p>
            </div>
            <x-button href="{{ route('supervisor.collaborators.create') }}" variant="primary">New Collaborator</x-button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-card>
                <p class="text-xs text-secondary">Total Contacts</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['total'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Academic</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['academic'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Industry</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['industry'] }}</p>
            </x-card>
            <x-card>
                <p class="text-xs text-secondary">Suggested Reviewers</p>
                <p class="mt-2 text-2xl font-semibold text-primary">{{ $stats['reviewers'] }}</p>
            </x-card>
        </div>

        <x-card>
            <form method="GET" action="{{ route('supervisor.collaborators.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1 block text-xs font-medium text-secondary">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, organization, field, expertise..." class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <div class="min-w-[180px]">
                    <label class="mb-1 block text-xs font-medium text-secondary">Category</label>
                    <select name="category" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                        <option value="">All</option>
                        <option value="academic" @selected(request('category') === 'academic')>Academic</option>
                        <option value="industry" @selected(request('category') === 'industry')>Industry</option>
                        <option value="government" @selected(request('category') === 'government')>Government</option>
                        <option value="ngo" @selected(request('category') === 'ngo')>NGO</option>
                        <option value="other" @selected(request('category') === 'other')>Other</option>
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <label class="mb-1 block text-xs font-medium text-secondary">Country</label>
                    <input type="text" name="country" value="{{ request('country') }}" placeholder="Malaysia, Japan..." class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm focus:border-accent focus:ring-1 focus:ring-accent/20 outline-none">
                </div>
                <label class="flex items-center gap-2 rounded-lg border border-border px-3 py-2">
                    <input type="checkbox" name="reviewer_only" value="1" @checked(request()->boolean('reviewer_only')) class="rounded border-gray-300 text-accent focus:ring-accent">
                    <span class="text-xs font-medium text-primary">Reviewers only</span>
                </label>
                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" size="sm">Filter</x-button>
                    @if(request()->hasAny(['search', 'category', 'country', 'reviewer_only']))
                        <x-button href="{{ route('supervisor.collaborators.index') }}" variant="secondary" size="sm">Clear</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        @if($collaborators->isEmpty())
            <x-card>
                <div class="py-12 text-center">
                    <p class="text-base font-semibold text-primary">No collaborators yet</p>
                    <p class="mt-1 text-sm text-secondary">Create a contact record to keep collaborator and reviewer details in one place.</p>
                </div>
            </x-card>
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach($collaborators as $collaborator)
                    <a href="{{ route('supervisor.collaborators.show', $collaborator) }}" class="group block">
                        <x-card class="h-full border border-border/80 transition-all hover:border-accent/30 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-primary transition-colors group-hover:text-accent">{{ $collaborator->name }}</p>
                                    <p class="mt-1 text-xs text-secondary">{{ $collaborator->institution_name }}</p>
                                </div>
                                <span class="rounded-full bg-surface px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-secondary">{{ $collaborator->category_label }}</span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Department</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->department ?: 'Not set' }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Country</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->country ?: 'Not set' }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Expertise</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->expertise_area ?: 'Not set' }}</p>
                                </div>
                                <div class="rounded-xl bg-surface/80 px-3 py-2">
                                    <p class="text-[10px] uppercase tracking-wide text-tertiary">Field</p>
                                    <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->research_field ?: 'Not set' }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @if($collaborator->suitable_for_grant)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-700">Grant</span>
                                @endif
                                @if($collaborator->suitable_for_publication)
                                    <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-blue-700">Publication</span>
                                @endif
                                @if($collaborator->suggested_reviewer)
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">Reviewer</span>
                                @endif
                            </div>
                        </x-card>
                    </a>
                @endforeach
            </div>

            @if($collaborators->hasPages())
                <div>{{ $collaborators->withQueryString()->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
