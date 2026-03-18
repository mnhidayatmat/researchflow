{{-- Supervisor Panel Template --}}
<x-layouts.app title="Supervisor Panel" :header="'Supervisor Panel'">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-semibold text-primary">Student Supervision</h1>
            <p class="text-sm text-secondary mt-1">Manage your assigned students and their progress</p>
        </div>
        <div class="flex items-center gap-2">
            <select class="text-sm rounded-lg border border-border bg-white px-3 py-2 focus:ring-2 focus:ring-accent/20 focus:border-accent">
                <option>All Students</option>
                <option>Active Only</option>
                <option>Pending Review</option>
            </select>
        </div>
    </div>

    {{-- Students grid or list --}}
    <x-card :padding='false'>
        {{-- List view --}}
        <x-table :headers="['Student', 'Programme', 'Research Topic', 'Progress', 'Status', ['label' => 'Actions', 'align' => 'right']]">
            @foreach($students as $s)
                <tr class="hover:bg-surface/50" @click="window.location='{{ route('supervisor.students.show', $s) }}'">
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$s->user->name" size="sm" :status="$s->status === 'active' ? 'online' : 'offline'" />
                            <div>
                                <p class="text-sm font-medium text-primary">{{ $s->user->name }}</p>
                                <p class="text-xs text-secondary">{{ $s->matric_number }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-surface text-secondary">{{ $s->programme->code }}</span>
                    </td>
                    <td>
                        <p class="text-sm text-primary max-w-[200px] truncate">{{ $s->research_title ?? '—' }}</p>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <x-progress :value="$s->overall_progress ?? 0" size="sm" :show-label="false" />
                            <span class="text-xs font-medium text-secondary w-8">{{ $s->overall_progress ?? 0 }}%</span>
                        </div>
                    </td>
                    <td><x-status-badge :status="$s->status" size="sm" /></td>
                    <td class="text-right">
                        <a href="{{ route('supervisor.students.show', $s) }}" class="text-xs font-medium text-accent hover:text-amber-700" @click.stop>View</a>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>

    {{-- Pending reviews section --}}
    @if($pendingReviewsCount > 0)
        <div class="mt-6 grid lg:grid-cols-2 gap-6">
            {{-- Tasks awaiting review --}}
            <x-card title="Tasks Awaiting Review" :subtitle="$pendingTasksCount . ' pending'">
                <div class="space-y-2 -mx-2 max-h-[300px] overflow-y-auto">
                    @foreach($pendingTasks as $task)
                        <a href="{{ route('tasks.show', [$task->student_id, $task]) }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-warning-light text-warning flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-primary">{{ $task->title }}</p>
                                <p class="text-xs text-secondary">{{ $task->student->user->name }}</p>
                            </div>
                            <span class="text-xs text-tertiary">{{ $task->due_date?->diffForHumans() ?? '' }}</span>
                        </a>
                    @endforeach
                </div>
            </x-card>

            {{-- Reports awaiting review --}}
            <x-card title="Reports Awaiting Review" :subtitle="$pendingReportsCount . ' pending'">
                <div class="space-y-2 -mx-2 max-h-[300px] overflow-y-auto">
                    @foreach($pendingReports as $report)
                        <a href="{{ route('reports.show', [$report->student_id, $report]) }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-surface transition-colors">
                            <div class="w-10 h-10 rounded-lg bg-info-light text-info flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-primary">{{ $report->title }}</p>
                                <p class="text-xs text-secondary">{{ $report->student->user->name }}</p>
                            </div>
                            <span class="text-xs text-tertiary">{{ $report->submitted_at?->diffForHumans() ?? '' }}</span>
                        </a>
                    @endforeach
                </div>
            </x-card>
        </div>
    @endif
</x-layouts.app>
