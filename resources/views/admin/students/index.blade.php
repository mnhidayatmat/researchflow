<x-layouts.app title="Students">
    <x-slot:header>Students</x-slot:header>

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5 sm:mb-6">
        <div>
            <h2 class="text-base font-semibold text-primary dark:text-dark-primary">All Students</h2>
            <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Manage and monitor student registrations</p>
        </div>
        <x-button href="{{ route('admin.students.create') }}" variant="primary" size="sm" class="w-full justify-center sm:w-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Student
        </x-button>
    </div>

    {{-- Filters --}}
    <x-card class="mb-4">
        <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            <div class="flex-1 min-w-0 sm:min-w-[180px]">
                <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Name, matric, email..."
                    class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                >
            </div>
            <div class="grid grid-cols-2 gap-3 sm:contents">
                <div class="sm:min-w-[160px]">
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Programme</label>
                    <select name="programme_id" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors">
                        <option value="">All</option>
                        @foreach($programmes as $programme)
                            <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                                {{ $programme->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:min-w-[140px]">
                    <label class="block text-xs font-medium text-secondary dark:text-dark-secondary mb-1">Status</label>
                    <select name="status" class="w-full rounded-lg border border-border dark:border-dark-border bg-white dark:bg-dark-card px-3 py-2.5 sm:py-2 text-sm text-primary dark:text-dark-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <x-button type="submit" variant="primary" size="sm" class="flex-1 justify-center sm:flex-none">Filter</x-button>
                @if(request()->hasAny(['search', 'programme_id', 'status']))
                    <x-button href="{{ route('admin.students.index') }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Clear</x-button>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Mobile Card List (shown on small screens) --}}
    <div class="sm:hidden space-y-3">
        @forelse($students as $student)
            <a href="{{ route('admin.students.show', $student) }}" class="block bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4 active:bg-surface dark:active:bg-dark-surface transition-colors">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-accent/10 text-accent flex items-center justify-center text-sm font-semibold shrink-0">
                        {{ substr($student->user?->name ?? '?', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="text-sm font-semibold text-primary dark:text-dark-primary truncate">{{ $student->user?->name ?? 'Deleted User' }}</p>
                            <x-status-badge :status="$student->status" size="sm" />
                        </div>
                        <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $student->programme?->code ?? '—' }} · {{ $student->user?->matric_number ?? 'No matric' }}</p>
                        @if($student->research_title)
                        <p class="text-xs text-tertiary dark:text-dark-tertiary mt-1 line-clamp-1">{{ $student->research_title }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border dark:border-dark-border">
                    <div class="flex items-center gap-2 flex-1">
                        <div class="w-full max-w-[120px] h-1.5 bg-border-light dark:bg-dark-border rounded-full overflow-hidden">
                            <div class="bg-accent h-1.5 rounded-full" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                        </div>
                        <span class="text-xs font-medium text-secondary dark:text-dark-secondary">{{ $student->overall_progress ?? 0 }}%</span>
                    </div>
                    <div class="flex items-center gap-1">
                        @if($student->status === 'pending')
                            <span class="text-xs font-medium text-accent">Needs approval</span>
                        @else
                            <span class="text-xs text-secondary dark:text-dark-secondary">{{ $student->supervisor?->name ?? '—' }}</span>
                        @endif
                        <svg class="w-4 h-4 text-tertiary dark:text-dark-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-8 text-center">
                <svg class="w-10 h-10 text-tertiary dark:text-dark-tertiary mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-sm font-medium text-primary dark:text-dark-primary">No students found</p>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Try adjusting your filters or add a new student</p>
            </div>
        @endforelse

        @if($students->hasPages())
            <div class="pt-2">{{ $students->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Desktop Table (hidden on mobile) --}}
    <x-card :padding='false' class="hidden sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border dark:border-dark-border bg-surface dark:bg-dark-surface">
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Student</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Matric</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Programme</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Supervisor</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Progress</th>
                        <th class="text-right text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border dark:divide-dark-border">
                    @forelse($students as $student)
                        <tr class="hover:bg-surface/60 dark:hover:bg-dark-surface/60 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold flex-shrink-0">
                                        {{ substr($student->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.students.show', $student) }}" class="font-medium text-primary dark:text-dark-primary hover:text-accent transition-colors">
                                            {{ $student->user?->name ?? 'Deleted User' }}
                                        </a>
                                        <p class="text-xs text-secondary dark:text-dark-secondary truncate max-w-[200px]">{{ $student->research_title ?? 'No title set' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-secondary dark:text-dark-secondary font-mono text-xs">{{ $student->user?->matric_number ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-secondary dark:text-dark-secondary">{{ $student->programme?->code ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-secondary dark:text-dark-secondary">{{ $student->supervisor?->name ?? '—' }}</td>
                            <td class="px-5 py-3"><x-status-badge :status="$student->status" /></td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-100 dark:bg-dark-border rounded-full h-1.5">
                                        <div class="bg-accent h-1.5 rounded-full transition-all" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-secondary dark:text-dark-secondary w-8">{{ $student->overall_progress ?? 0 }}%</span>
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @if($student->status === 'pending')
                                        <form method="POST" action="{{ route('admin.students.approve', $student) }}">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-green-50 text-green-700 hover:bg-green-100 transition-colors">
                                                Approve
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.students.show', $student) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 dark:hover:bg-dark-surface rounded transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 dark:hover:bg-dark-surface rounded transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="text-secondary dark:text-dark-secondary">
                                    <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <p class="text-sm">No students found</p>
                                    <p class="text-xs mt-0.5">Try adjusting your filters or add a new student</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="px-5 py-3 border-t border-border dark:border-dark-border">
                {{ $students->withQueryString()->links() }}
            </div>
        @endif
    </x-card>
</x-layouts.app>
