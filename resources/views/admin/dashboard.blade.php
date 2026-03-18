<x-layouts.app title="Admin Dashboard" :header="'Dashboard'">
    {{-- Welcome section --}}
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-xl font-semibold text-primary">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-sm text-secondary mt-1">Here's what's happening with your supervision program today.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-accent hover:bg-amber-700 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Student
            </a>
            <a href="{{ route('admin.programmes.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-primary bg-white border border-border hover:bg-surface rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Programme
            </a>
        </div>
    </div>

    {{-- Primary stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stat-card
            title="Total Students"
            :value="$stats['total_students']"
            :change="($stats['total_students'] > 0 ? '+12%' : '0%')"
            icon="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            variant="accent"
            :href="route('admin.students.index')"
        />
        <x-stat-card
            title="Pending Approval"
            :value="$stats['pending_students']"
            icon="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
            variant="warning"
        />
        <x-stat-card
            title="Active Supervisors"
            :value="$stats['total_supervisors']"
            icon="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
            variant="success"
        />
        <x-stat-card
            title="Total Tasks"
            :value="$stats['total_tasks']"
            :change="$stats['pending_reports'] . ' pending'"
            icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
            variant="info"
        />
    </div>

    {{-- Content grid --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Pending approvals --}}
        <x-card title="Pending Approvals" :action="'Students'">
            @forelse($pendingApprovals as $s)
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-border' : '' }}">
                    <div class="flex items-center gap-3">
                        <x-avatar :name="$s->user->name" size="sm" />
                        <div>
                            <p class="text-sm font-medium text-primary">{{ $s->user->name }}</p>
                            <p class="text-xs text-secondary">{{ $s->programme->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <a href="{{ route('admin.students.show', $s) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-surface rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.students.approve', $s) }}" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-success hover:text-success/70 hover:bg-success-light rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-success-light text-success flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-sm text-secondary">No pending approvals</p>
                </div>
            @endforelse
        </x-card>

        {{-- Task distribution --}}
        <x-card title="Tasks by Status" :padding='false'>
            <div class="p-5">
                <canvas id="taskChart" height="180"></canvas>
            </div>
            <div class="grid grid-cols-3 gap-px bg-border border-t border-border">
                <div class="bg-white p-3 text-center">
                    <p class="text-lg font-bold text-primary">{{ $tasksByStatus['in_progress'] ?? 0 }}</p>
                    <p class="text-xs text-secondary">Active</p>
                </div>
                <div class="bg-white p-3 text-center">
                    <p class="text-lg font-bold text-warning">{{ $tasksByStatus['waiting_review'] ?? 0 }}</p>
                    <p class="text-xs text-secondary">Review</p>
                </div>
                <div class="bg-white p-3 text-center">
                    <p class="text-lg font-bold text-success">{{ $tasksByStatus['completed'] ?? 0 }}</p>
                    <p class="text-xs text-secondary">Done</p>
                </div>
            </div>
        </x-card>

        {{-- Quick overview --}}
        <x-card title="Quick Actions">
            <div class="space-y-1">
                <a href="{{ route('admin.students.index') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-info/10 text-info flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-primary">All Students</span>
                    </div>
                    <svg class="w-4 h-4 text-secondary group-hover:text-primary group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('admin.templates.index') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-accent/10 text-accent flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-primary">Journey Templates</span>
                    </div>
                    <svg class="w-4 h-4 text-secondary group-hover:text-primary group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('admin.settings.users') }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-surface transition-colors group">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-success/10 text-success flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-primary">Manage Users</span>
                    </div>
                    <svg class="w-4 h-4 text-secondary group-hover:text-primary group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </x-card>
    </div>

    {{-- Recent students table --}}
    <x-card title="Recent Students" class="mt-6" :padding='false'>
        <x-table :headers="['Student', 'Programme', 'Supervisor', 'Status', 'Progress', ['label' => 'Actions', 'align' => 'right']]">
            @foreach($recentStudents as $s)
                <tr class="hover:bg-surface/50 cursor-pointer" @click="window.location='{{ route('admin.students.show', $s) }}'">
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$s->user->name" size="sm" />
                            <div>
                                <p class="text-sm font-medium text-primary">{{ $s->user->name }}</p>
                                <p class="text-xs text-secondary">{{ $s->user->matric_number }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-surface text-secondary">{{ $s->programme->code }}</span>
                    </td>
                    <td class="text-sm text-secondary">{{ $s->supervisor?->name ?? '—' }}</td>
                    <td><x-status-badge :status="$s->status" size="sm" /></td>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-progress :value="$s->overall_progress" size="sm" variant="default" />
                            <span class="text-xs font-medium text-secondary w-8">{{ $s->overall_progress }}%</span>
                        </div>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('admin.students.show', $s) }}" class="inline-flex items-center gap-1 text-xs font-medium text-accent hover:text-amber-700 transition-colors" @click.stop>
                            View
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
            @endforeach
        </x-table>
        @if($recentStudents->count() > 0)
        <div class="px-5 py-3 bg-surface border-t border-border text-center">
            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-accent hover:text-amber-700 transition-colors">
                View all students
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
        @endif
    </x-card>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const taskData = @json($tasksByStatus);
        const labels = ['Backlog', 'Planned', 'Active', 'Review', 'Revision', 'Done'];
        const keys = ['backlog', 'planned', 'in_progress', 'waiting_review', 'revision', 'completed'];
        const colors = ['#E5E7E4', '#93C5FD', '#3B82F6', '#F59E0B', '#A78BFA', '#10B981'];

        new Chart(document.getElementById('taskChart'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: keys.map(k => taskData[k] || 0),
                    backgroundColor: colors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11 },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                },
                cutout: '70%',
            }
        });
    </script>
    @endpush
</x-layouts.app>
