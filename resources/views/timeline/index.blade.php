<x-layouts.app title="Gantt Chart" :header="'Gantt Chart Overview'">
    <div class="max-w-7xl mx-auto" x-data="timelineIndex()">
        {{-- Toast Notifications --}}
        <div class="fixed top-4 right-4 z-[100] space-y-2">
            <template x-for="notification in notifications" :key="notification.id">
                <div x-show="notification.show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-4"
                     :class="notification.type === 'success' ? 'bg-success text-white' : 'bg-danger text-white'"
                     class="px-5 py-3 rounded-xl shadow-lg text-sm font-medium flex items-center gap-2 min-w-[280px]">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="notification.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path x-show="notification.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="notification.message"></span>
                </div>
            </template>
        </div>

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-primary">Gantt Chart</h1>
                <p class="text-sm text-secondary mt-1">Select a student to view and manage their project timeline</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" x-model="searchQuery" placeholder="Search students..."
                       class="px-4 py-2.5 rounded-xl border border-border focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none text-sm w-64">
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-card rounded-2xl border border-border p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-accent/20 to-accent/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-primary" x-text="stats.totalStudents"></p>
                        <p class="text-xs text-secondary">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="bg-card rounded-2xl border border-border p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-info/20 to-info/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-primary" x-text="stats.totalActivities"></p>
                        <p class="text-xs text-secondary">All Activities</p>
                    </div>
                </div>
            </div>
            <div class="bg-card rounded-2xl border border-border p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-danger/20 to-danger/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-primary" x-text="stats.totalMilestones"></p>
                        <p class="text-xs text-secondary">Milestones</p>
                    </div>
                </div>
            </div>
            <div class="bg-card rounded-2xl border border-border p-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-success/20 to-success/10 flex items-center justify-center">
                        <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-primary" x-text="stats.completedTasks"></p>
                        <p class="text-xs text-secondary">Completed</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Students Grid --}}
        @if($students->isEmpty())
            <x-card class="p-16 text-center">
                <div class="flex flex-col items-center justify-center">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-primary mb-2">No Students Found</h3>
                    <p class="text-sm text-secondary max-w-md">There are no active students available to view timelines for.</p>
                </div>
            </x-card>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($students as $studentItem)
                    <div x-data="studentCard({{ $studentItem->id }}, {{ $studentItem->tasks()->count() }}, {{ $studentItem->tasks()->where('is_milestone', true)->count() }}, {{ $studentItem->tasks()->where('status', 'completed')->count() }})"
                         class="group bg-card rounded-2xl border border-border hover:border-accent/30 hover:shadow-soft
                                transition-all duration-300 overflow-hidden">
                        {{-- Card Header --}}
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div class="relative">
                                    <x-avatar :name="$studentItem->user->name" size="xl" />
                                    <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-card bg-success"></span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-primary group-hover:text-accent transition-colors truncate">
                                        {{ $studentItem->user->name }}
                                    </h3>
                                    <p class="text-xs text-secondary truncate">{{ $studentItem->user->email }}</p>
                                    <p class="text-xs text-tertiary mt-1">{{ $studentItem->programme->name ?? 'No Programme' }}</p>
                                </div>
                            </div>

                            {{-- Quick Stats --}}
                            <div class="mt-5 grid grid-cols-3 gap-3">
                                <div class="text-center p-2 bg-surface rounded-xl">
                                    <p class="text-lg font-bold text-primary" x-text="stats.tasks"></p>
                                    <p class="text-[10px] text-secondary uppercase tracking-wider">Tasks</p>
                                </div>
                                <div class="text-center p-2 bg-accent/5 rounded-xl">
                                    <p class="text-lg font-bold text-accent" x-text="stats.milestones"></p>
                                    <p class="text-[10px] text-secondary uppercase tracking-wider">Milestones</p>
                                </div>
                                <div class="text-center p-2 bg-success/5 rounded-xl">
                                    <p class="text-lg font-bold text-success" x-text="stats.completed"></p>
                                    <p class="text-[10px] text-secondary uppercase tracking-wider">Done</p>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-6 py-4 bg-surface/50 border-t border-border flex items-center justify-between">
                            {{-- Progress Bar --}}
                            <div class="flex-1 mr-4">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[10px] text-secondary">Progress</span>
                                    <span class="text-[10px] font-medium text-primary" x-text="progress + '%'"></span>
                                </div>
                                <div class="h-1.5 bg-border-light rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-accent to-success rounded-full transition-all duration-500"
                                         :style="'width: ' + progress + '%'"></div>
                                </div>
                            </div>
                            {{-- View Button --}}
                            <a href="{{ route('timeline.show', $studentItem) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-medium
                                      bg-accent text-white hover:bg-amber-700 transition-all shadow-sm">
                                View
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <script>
        function timelineIndex() {
            return {
                searchQuery: '',
                notificationCounter: 0,
                notifications: [],
                stats: {
                    totalStudents: {{ $students->count() }},
                    totalActivities: {{ $students->sum(fn($s) => $s->tasks()->count()) }},
                    totalMilestones: {{ $students->sum(fn($s) => $s->tasks()->where('is_milestone', true)->count()) }},
                    completedTasks: {{ $students->sum(fn($s) => $s->tasks()->where('status', 'completed')->count()) }},
                },

                showNotification(message, type = 'success') {
                    const id = ++this.notificationCounter;
                    this.notifications.push({ id, message, type, show: true });
                    setTimeout(() => {
                        this.notifications = this.notifications.filter(n => n.id !== id);
                    }, 3000);
                }
            };
        }

        function studentCard(id, tasks, milestones, completed) {
            return {
                stats: { tasks, milestones, completed },
                get progress() {
                    return this.stats.tasks > 0 ? Math.round((this.stats.completed / this.stats.tasks) * 100) : 0;
                }
            };
        }
    </script>
</x-layouts.app>
