<x-layouts.app title="Students">
    <x-slot:header>Students</x-slot:header>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-base font-semibold text-primary">All Students</h2>
            <p class="text-xs text-secondary mt-0.5">Manage and monitor student registrations</p>
        </div>
        <x-button href="{{ route('admin.students.create') }}" variant="primary" size="sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Student
        </x-button>
    </div>

    {{-- Filters --}}
    <x-card class="mb-4">
        <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-secondary mb-1">Search</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Name, matric, email..."
                    class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary placeholder-secondary/50 focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors"
                >
            </div>
            <div class="min-w-[160px]">
                <label class="block text-xs font-medium text-secondary mb-1">Programme</label>
                <select name="programme_id" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors">
                    <option value="">All Programmes</option>
                    @foreach($programmes as $programme)
                        <option value="{{ $programme->id }}" {{ request('programme_id') == $programme->id ? 'selected' : '' }}>
                            {{ $programme->code }} — {{ $programme->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[140px]">
                <label class="block text-xs font-medium text-secondary mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border border-border bg-white px-3 py-2 text-sm text-primary focus:border-accent focus:ring-1 focus:ring-accent/30 outline-none transition-colors">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="withdrawn" {{ request('status') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                </select>
            </div>
            <div class="flex gap-2">
                <x-button type="submit" variant="primary" size="sm">Filter</x-button>
                @if(request()->hasAny(['search', 'programme_id', 'status']))
                    <x-button href="{{ route('admin.students.index') }}" variant="secondary" size="sm">Clear</x-button>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Table --}}
    <x-card :padding='false'>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-surface">
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Student</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Matric</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Programme</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Supervisor</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Progress</th>
                        <th class="text-right text-xs font-medium text-secondary uppercase tracking-wider px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($students as $student)
                        <tr class="hover:bg-surface/60 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-accent/10 text-accent flex items-center justify-center text-xs font-semibold flex-shrink-0">
                                        {{ substr($student->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.students.show', $student) }}" class="font-medium text-primary hover:text-accent transition-colors">
                                            {{ $student->user->name }}
                                        </a>
                                        <p class="text-xs text-secondary truncate max-w-[200px]">{{ $student->research_title ?? 'No title set' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-secondary font-mono text-xs">{{ $student->user->matric_number ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="text-secondary">{{ $student->programme->code ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3 text-secondary">{{ $student->supervisor?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <x-status-badge :status="$student->status" />
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-accent h-1.5 rounded-full transition-all" style="width: {{ $student->overall_progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-secondary w-8">{{ $student->overall_progress ?? 0 }}%</span>
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
                                    <a href="{{ route('admin.students.show', $student) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 rounded transition-colors" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 rounded transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="text-secondary">
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
            <div class="px-5 py-3 border-t border-border">
                {{ $students->withQueryString()->links() }}
            </div>
        @endif
    </x-card>
</x-layouts.app>
