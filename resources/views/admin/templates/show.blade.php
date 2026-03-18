<x-layouts.app :title="$template->name">
    <x-slot:header>Journey Templates</x-slot:header>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-xs text-secondary mb-5">
        <a href="{{ route('admin.templates.index') }}" class="hover:text-primary transition-colors">Templates</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary">{{ $template->name }}</span>
    </nav>

    <div class="max-w-3xl">
        {{-- Header --}}
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-primary">{{ $template->name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    @if($template->programme)
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-xs font-mono text-gray-600">{{ $template->programme->code }}</span>
                        <span class="text-xs text-secondary">{{ $template->programme->name }}</span>
                    @else
                        <span class="text-xs text-secondary">No programme assigned</span>
                    @endif
                </div>
                @if($template->description)
                    <p class="mt-2 text-sm text-secondary">{{ $template->description }}</p>
                @endif
            </div>
            <x-button href="{{ route('admin.templates.edit', $template) }}" variant="secondary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </x-button>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-5">
            <x-stat-card label="Total Stages" :value="$template->stages->count()" />
            <x-stat-card label="Total Milestones" :value="$template->stages->sum(fn($s) => $s->milestones->count())" />
            @php
                $totalWeeks = $template->stages->sum('duration_weeks');
            @endphp
            <x-stat-card label="Est. Duration" :value="$totalWeeks ? $totalWeeks . ' wks' : '—'" />
        </div>

        {{-- Stages --}}
        <x-card title="Research Journey Structure" :padding='false'>
            @if($template->stages->isEmpty())
                <div class="p-8 text-center text-secondary">
                    <p class="text-sm">No stages defined</p>
                </div>
            @else
                <div class="divide-y divide-border">
                    @foreach($template->stages->sortBy('order') as $stage)
                        <div class="p-5">
                            {{-- Stage header --}}
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-7 h-7 rounded-full bg-surface border border-border text-xs font-medium text-secondary flex items-center justify-center flex-shrink-0">
                                    {{ $stage->order }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-primary">{{ $stage->name }}</h4>
                                        <div class="flex items-center gap-3 text-xs text-secondary">
                                            @if($stage->duration_weeks)
                                                <span>{{ $stage->duration_weeks }} weeks</span>
                                            @endif
                                            <span>{{ $stage->milestones->count() }} {{ Str::plural('milestone', $stage->milestones->count()) }}</span>
                                        </div>
                                    </div>
                                    @if($stage->description)
                                        <p class="text-xs text-secondary mt-0.5">{{ $stage->description }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Milestones --}}
                            @if($stage->milestones->isNotEmpty())
                                <div class="ml-10 space-y-2">
                                    @foreach($stage->milestones->sortBy('order') as $milestone)
                                        <div class="flex items-center justify-between py-2 px-3 bg-surface rounded-lg">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-1.5 h-1.5 rounded-full bg-accent/40 flex-shrink-0"></div>
                                                <span class="text-sm text-primary">{{ $milestone->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-4 text-xs text-secondary">
                                                @if($milestone->weight)
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="text-secondary">Weight</span>
                                                        <span class="font-medium text-primary">{{ $milestone->weight }}</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="ml-10 text-xs text-secondary italic">No milestones in this stage</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Danger zone --}}
        <div class="mt-6 p-4 border border-red-100 rounded-lg bg-red-50/50">
            <h4 class="text-sm font-medium text-red-700 mb-1">Danger Zone</h4>
            <p class="text-xs text-red-600/80 mb-3">Deleting this template will not affect existing journeys assigned to students.</p>
            <form method="POST" action="{{ route('admin.templates.destroy', $template) }}" onsubmit="return confirm('Delete this template permanently?')">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm">Delete Template</x-button>
            </form>
        </div>
    </div>
</x-layouts.app>
