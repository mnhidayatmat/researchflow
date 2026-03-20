<x-layouts.app title="Timeline - {{ $student->user->name }}" :header="'Gantt Timeline'">
    <div class="max-w-[1600px] mx-auto" x-data="timelineOverview({{ $student->id }})" x-init="init()">
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
                     :class="{
                         'bg-success text-white': notification.type === 'success',
                         'bg-danger text-white': notification.type === 'error',
                         'bg-warning text-white': notification.type === 'warning'
                     }"
                     class="px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 min-w-[300px]">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="notification.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path x-show="notification.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path x-show="notification.type === 'warning'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium" x-text="notification.message"></p>
                        <template x-if="notification.errors && Object.keys(notification.errors).length > 0">
                            <ul class="mt-1 text-xs opacity-90 list-disc list-inside">
                                <template x-for="error in Object.values(notification.errors).flat()" :key="error">
                                    <li x-text="error"></li>
                                </template>
                            </ul>
                        </template>
                    </div>
                    <button @click="dismissNotification(notification.id)" class="opacity-70 hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Student Selector --}}
        <div class="mb-6">
            <div class="relative inline-block w-full sm:w-auto" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-border
                               hover:border-accent/30 hover:bg-surface transition-all w-full sm:w-auto">
                    <x-avatar :name="$student->user->name" size="sm" />
                    <div class="text-left flex-1 sm:flex-none">
                        <p class="text-sm font-medium text-primary">{{ $student->user->name }}</p>
                        <p class="text-xs text-secondary">{{ $student->programme->name ?? 'Research' }}</p>
                    </div>
                    <svg class="w-4 h-4 text-tertiary" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown --}}
                <div x-show="open" @click.outside="open = false" x-transition
                     class="absolute left-0 right-0 sm:right-auto sm:left-auto sm:w-64 mt-2 bg-card border border-border
                            rounded-xl shadow-lg z-50 max-h-80 overflow-y-auto">
                    <div class="p-2">
                        @foreach($students as $studentOption)
                            <a href="{{ route('timeline.show', $studentOption) }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-surface transition-colors
                                      {{ $student->id === $studentOption->id ? 'bg-accent/10' : '' }}">
                                <x-avatar :name="$studentOption->user->name" size="sm" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-primary truncate">{{ $studentOption->user->name }}</p>
                                    <p class="text-xs text-secondary truncate">{{ $studentOption->programme->name ?? 'No Programme' }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Page Header with Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-base font-semibold text-primary">Project Timeline</h2>
                <p class="text-xs text-secondary mt-0.5">
                    <span x-text="stats.total"></span> activities ·
                    <span x-text="stats.milestones"></span> milestones ·
                    <span x-text="stats.overallProgress + '%'"></span> complete
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="showAddForm = !showAddForm"
                        :class="showAddForm ? 'bg-accent text-white' : 'text-secondary hover:text-primary border border-border hover:bg-surface'"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-text="showAddForm ? 'Cancel' : 'Add Activity'"></span>
                </button>
                <button @click="exportImage()" :disabled="loading || !ganttInstance"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium
                               text-secondary hover:text-primary border border-border hover:bg-surface
                               disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Image
                </button>
                <button @click="exportPdf()" :disabled="loading || !ganttInstance"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium
                               text-secondary hover:text-primary border border-border hover:bg-surface
                               disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </button>
            </div>
        </div>

        <div class="grid lg:grid-cols-4 gap-6">
            {{-- Left Panel: Add Activity Form --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- Add Activity Card (Collapsible) --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden"
                     x-show="showAddForm"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-x-4"
                     x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="px-6 py-4 border-b border-border flex items-center justify-between bg-gradient-to-r from-accent/5 to-transparent">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-primary">Add Activity</h3>
                        </div>
                        <button @click="showAddForm = false" class="text-secondary hover:text-primary transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="addActivity()" class="p-6 space-y-4">
                        {{-- Title --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" x-model="form.title" required
                                   :class="errors.title ? 'border-danger focus:ring-danger/20' : 'focus:border-accent focus:ring-accent/20'"
                                   class="w-full px-4 py-2.5 rounded-xl border border-border
                                          focus:ring-2 outline-none
                                          text-sm text-primary placeholder-tertiary transition-all"
                                   placeholder="e.g., Literature Review">
                            <p x-show="errors.title" x-text="errors.title" class="mt-1 text-xs text-danger"></p>
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Description</label>
                            <textarea x-model="form.description" rows="2"
                                      class="w-full px-4 py-2.5 rounded-xl border border-border
                                             focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none
                                             text-sm text-primary placeholder-tertiary resize-none transition-all"
                                      placeholder="Optional details..."></textarea>
                        </div>

                        {{-- Dates Row --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-secondary mb-1.5">
                                    Start Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" x-model="form.start_date" required
                                       :class="errors.start_date ? 'border-danger' : 'focus:border-accent'"
                                       class="w-full px-3 py-2 rounded-lg border border-border
                                              focus:ring-2 focus:ring-accent/20 outline-none
                                              text-sm text-primary transition-all">
                                <p x-show="errors.start_date" x-text="errors.start_date" class="mt-1 text-xs text-danger"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-secondary mb-1.5">
                                    Duration <span class="text-danger">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input type="number" x-model="form.duration_days" min="1" max="365"
                                           :class="errors.duration_days ? 'border-danger' : 'focus:border-accent'"
                                           class="w-full px-3 py-2 rounded-lg border border-border
                                                  focus:ring-2 focus:ring-accent/20 outline-none
                                                  text-sm text-primary transition-all">
                                    <span class="text-xs text-secondary whitespace-nowrap">days</span>
                                </div>
                                <p x-show="errors.duration_days" x-text="errors.duration_days" class="mt-1 text-xs text-danger"></p>
                            </div>
                        </div>

                        {{-- Parent Task --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">
                                Link to Parent Task
                            </label>
                            <select x-model="form.parent_task_id"
                                    class="w-full px-3 py-2 rounded-lg border border-border
                                           focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none
                                           text-sm text-primary bg-card transition-all">
                                <option value="">None (standalone activity)</option>
                                @foreach($student->tasks->where('is_milestone', true) as $milestone)
                                    <option value="{{ $milestone->id }}">{{ $milestone->title }}</option>
                                @endforeach
                                @foreach($student->tasks->where('is_milestone', false)->take(5) as $task)
                                    <option value="{{ $task->id }}">{{ $task->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Is Milestone Toggle --}}
                        <div class="flex items-center justify-between p-3 bg-surface rounded-xl">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-danger/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-danger" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-primary">Mark as Milestone</span>
                                    <p class="text-[10px] text-secondary">Key deliverable or deadline</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="form.is_milestone" class="sr-only peer">
                                <div class="w-11 h-6 bg-border peer-focus:ring-2 peer-focus:ring-accent/20
                                            rounded-full peer peer-checked:after:translate-x-full
                                            peer-checked:bg-danger after:content-[''] after:absolute
                                            after:top-0.5 after:left-[2px] after:bg-white after:rounded-full
                                            after:h-5 after:w-5 after:transition-all"></div>
                            </label>
                        </div>

                        {{-- Progress --}}
                        <div x-show="!form.is_milestone" x-transition class="space-y-2">
                            <label class="block text-xs font-medium text-secondary">
                                Initial Progress: <span class="font-semibold text-primary" x-text="form.progress"></span>%
                            </label>
                            <input type="range" min="0" max="100" x-model="form.progress"
                                   class="w-full h-2 bg-border-light rounded-xl appearance-none cursor-pointer accent-accent">
                            <div class="flex justify-between text-[10px] text-tertiary">
                                <span>0%</span>
                                <span>50%</span>
                                <span>100%</span>
                            </div>
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label class="block text-xs font-medium text-secondary mb-1.5">Priority</label>
                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="p in [{value: 'low', label: 'Low', color: 'bg-info/10 text-info'}, {value: 'medium', label: 'Medium', color: 'bg-warning/10 text-warning'}, {value: 'high', label: 'High', color: 'bg-accent/10 text-accent'}, {value: 'urgent', label: 'Urgent', color: 'bg-danger/10 text-danger'}]" :key="p.value">
                                    <button type="button" @click="form.priority = form.priority === p.value ? null : p.value"
                                            :class="form.priority === p.value ? p.color + ' ring-1 ring-current' : 'bg-surface text-secondary hover:text-primary'"
                                            class="px-2 py-1.5 rounded-lg text-[10px] font-medium transition-all">
                                        <span x-text="p.label"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" :disabled="submitting"
                                class="w-full inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl
                                       text-sm font-semibold bg-accent text-white hover:bg-amber-700
                                       disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm
                                       group relative overflow-hidden">
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                            <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? 'Adding...' : 'Add Activity'" class="relative"></span>
                        </button>
                    </form>
                </div>

                {{-- Legend Card --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border">
                        <h3 class="text-sm font-semibold text-primary">Legend</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-success to-success/70"></div>
                            <span class="text-xs text-secondary">Completed (100%)</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-accent to-accent/70"></div>
                            <span class="text-xs text-secondary">In Progress</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-info to-info/70"></div>
                            <span class="text-xs text-secondary">Planned</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded bg-gradient-to-r from-warning to-warning/70"></div>
                            <span class="text-xs text-secondary">Review</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded-full bg-gradient-to-r from-danger to-rose-600 ring-4 ring-danger/20"></div>
                            <span class="text-xs text-secondary">Milestone</span>
                        </div>
                    </div>
                </div>

                {{-- Stats Card --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border bg-gradient-to-r from-accent/5 to-transparent">
                        <h3 class="text-sm font-semibold text-primary">Progress Overview</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-surface rounded-xl">
                                <p class="text-2xl font-bold text-primary" x-text="stats.total"></p>
                                <p class="text-[10px] text-secondary uppercase tracking-wider">Total</p>
                            </div>
                            <div class="text-center p-3 bg-success/5 rounded-xl">
                                <p class="text-2xl font-bold text-success" x-text="stats.completed"></p>
                                <p class="text-[10px] text-secondary uppercase tracking-wider">Done</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-border-light">
                            <span class="text-xs text-secondary">In Progress</span>
                            <span class="text-sm font-semibold text-accent" x-text="stats.inProgress"></span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-border-light">
                            <span class="text-xs text-secondary">Milestones</span>
                            <span class="text-sm font-semibold text-danger" x-text="stats.milestones"></span>
                        </div>
                        <div class="pt-2">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-secondary">Overall Progress</span>
                                <span class="text-xs font-bold text-primary" x-text="stats.overallProgress + '%'"></span>
                            </div>
                            <div class="h-3 bg-border-light rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-accent to-success rounded-full transition-all duration-500 ease-out"
                                     :style="'width: ' + stats.overallProgress + '%'"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Panel: Gantt Chart --}}
            <div class="lg:col-span-3 space-y-6">
                {{-- View Mode Selector --}}
                <div class="bg-card rounded-2xl border border-border p-1.5 flex items-center gap-1.5">
                    <template x-for="mode in ['Day', 'Week', 'Month']" :key="mode">
                        <button @click="changeViewMode(mode)"
                                :class="viewMode === mode
                                    ? 'bg-accent text-white shadow-sm'
                                    : 'text-secondary hover:text-primary hover:bg-surface/50'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-medium transition-all">
                            <span x-text="mode + ' View'"></span>
                        </button>
                    </template>
                </div>

                {{-- Gantt Chart Container --}}
                <div class="bg-card rounded-2xl border border-border overflow-hidden">
                    <div class="px-6 py-4 border-b border-border flex items-center justify-between bg-gradient-to-r from-surface/50 to-transparent">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-accent/10 flex items-center justify-center">
                                <svg class="w-4 h-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-primary">Timeline Visualization</h3>
                        </div>
                        <button @click="refresh()" :disabled="loading"
                                class="text-xs text-accent hover:text-amber-700 disabled:opacity-50 flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-accent/5 transition-all">
                            <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>

                    <div class="p-6" style="min-height: 500px;">
                        <!-- Loading State -->
                        <div x-show="loading" class="flex items-center justify-center h-96">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-2xl bg-accent/10 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-accent animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                                <p class="text-sm text-secondary mt-4">Loading timeline data...</p>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div x-show="!loading && tasks.length === 0" class="flex flex-col items-center justify-center h-96">
                            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-accent/15 to-accent/5 flex items-center justify-center mb-6">
                                <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-primary mb-2">No Activities Yet</h3>
                            <p class="text-sm text-secondary text-center max-w-xs mb-6">
                                Start building your project timeline by adding activities and milestones.
                            </p>
                            <button @click="showAddForm = true" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold bg-accent text-white hover:bg-amber-700 transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Add First Activity
                            </button>
                        </div>

                        <!-- Gantt Chart -->
                        <div x-show="!loading && tasks.length > 0" class="gantt-container">
                            <svg id="gantt-chart" class="w-full rounded-xl"></svg>
                        </div>
                    </div>
                </div>

                <!-- Activities List -->
                <div class="bg-card rounded-2xl border border-border overflow-hidden" x-show="tasks.length > 0">
                    <div class="px-6 py-4 border-b border-border flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-primary">Activities List</h3>
                            <span class="px-2 py-0.5 rounded-full bg-surface text-xs text-secondary" x-text="tasks.length + ' items'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" x-model="searchQuery" placeholder="Search..."
                                   class="px-3 py-1.5 text-xs rounded-lg border border-border focus:border-accent focus:ring-2 focus:ring-accent/20 outline-none w-32">
                        </div>
                    </div>
                    <div class="divide-y divide-border max-h-96 overflow-y-auto">
                        <template x-for="task in filteredTasks" :key="task.id">
                            <div class="flex items-center gap-4 p-4 hover:bg-surface transition-colors group cursor-pointer"
                                 @click="viewTaskDetails(task)">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-transform group-hover:scale-110"
                                     :class="task.is_milestone ? 'bg-danger/10' : 'bg-accent/10'">
                                    <svg class="w-5 h-5" :class="task.is_milestone ? 'text-danger' : 'text-accent'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path x-show="task.is_milestone" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        <path x-show="!task.is_milestone" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012 2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-primary truncate" x-text="task.name"></p>
                                        <span x-show="task.is_milestone" class="px-1.5 py-0.5 rounded bg-danger/10 text-danger text-[10px] font-medium">Milestone</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-secondary" x-text="formatDate(task.start)"></span>
                                        <svg class="w-3 h-3 text-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                        </svg>
                                        <span class="text-xs text-secondary" x-text="formatDate(task.end)"></span>
                                    </div>
                                </div>
                                <div class="w-24">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-[10px]" :class="task.progress === 100 ? 'text-success font-semibold' : 'text-secondary'" x-text="task.progress + '%'"></span>
                                    </div>
                                    <div class="h-2 bg-border-light rounded-full overflow-hidden">
                                        <div class="h-full rounded-full transition-all duration-300"
                                             :class="task.progress === 100 ? 'bg-success' : (task.progress > 50 ? 'bg-accent' : (task.progress > 0 ? 'bg-warning' : 'bg-tertiary'))"
                                             :style="'width: ' + task.progress + '%'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredTasks.length === 0" class="p-8 text-center">
                            <p class="text-sm text-secondary">No activities match your search.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function timelineOverview(studentId) {
            return {
                studentId: studentId,
                loading: true,
                submitting: false,
                showAddForm: false,
                ganttInstance: null,
                viewMode: 'Week',
                tasks: [],
                searchQuery: '',
                notificationCounter: 0,
                notifications: [],

                form: {
                    title: '',
                    description: '',
                    start_date: new Date().toISOString().split('T')[0],
                    duration_days: 7,
                    parent_task_id: '',
                    is_milestone: false,
                    progress: 0,
                    priority: null,
                },

                errors: {},

                stats: {
                    total: 0,
                    completed: 0,
                    inProgress: 0,
                    milestones: 0,
                    overallProgress: 0,
                },

                get filteredTasks() {
                    if (!this.searchQuery) return this.tasks;
                    const query = this.searchQuery.toLowerCase();
                    return this.tasks.filter(t =>
                        t.name?.toLowerCase().includes(query) ||
                        t.description?.toLowerCase().includes(query)
                    );
                },

                async init() {
                    await this.loadTasks();
                    this.loading = false;
                },

                async loadTasks() {
                    try {
                        const response = await fetch(`/api/students/${this.studentId}/tasks/gantt`);
                        if (!response.ok) throw new Error('Failed to load tasks');
                        this.tasks = await response.json();
                        this.calculateStats();
                        this.$nextTick(() => this.renderGantt());
                    } catch (error) {
                        console.error('Error loading tasks:', error);
                        this.showNotification('Failed to load timeline data', 'error');
                    }
                },

                calculateStats() {
                    this.stats.total = this.tasks.length;
                    this.stats.completed = this.tasks.filter(t => t.progress === 100).length;
                    this.stats.inProgress = this.tasks.filter(t => t.progress > 0 && t.progress < 100).length;
                    this.stats.milestones = this.tasks.filter(t => t.custom_class === 'gantt-milestone' || t.is_milestone).length;
                    this.stats.overallProgress = this.tasks.length > 0
                        ? Math.round(this.tasks.reduce((sum, t) => sum + (t.progress || 0), 0) / this.tasks.length)
                        : 0;
                },

                renderGantt() {
                    if (this.tasks.length === 0) return;

                    const chartElement = document.getElementById('gantt-chart');
                    if (!chartElement) return;

                    chartElement.innerHTML = '';

                    this.ganttInstance = new Gantt(chartElement, this.tasks, {
                        header_height: 50,
                        column_width: 30,
                        step: 1,
                        view_modes: ['Day', 'Week', 'Month'],
                        bar_height: 28,
                        bar_corner_radius: 6,
                        arrow_curve: 5,
                        padding: 18,
                        view_mode: this.viewMode,
                        date_format: 'YYYY-MM-DD',
                        language: 'en',
                        custom_popup_html: (task) => this.createPopupHtml(task),
                        draggable_progress: true,
                        draggable_update: true,
                        drag_listener: (task, start, end) => this.handleDateChange(task, start, end),
                        progress_change_listener: (task, progress) => this.handleProgressChange(task, progress),
                    });

                    this.$nextTick(() => this.applyMilestoneStyles());
                },

                createPopupHtml(task) {
                    const progress = task.progress || 0;
                    const statusClass = progress === 100 ? 'success' : progress > 0 ? 'accent' : 'info';
                    return `
                        <div class="gantt-popup bg-white border border-gray-200 rounded-xl shadow-xl p-4 min-w-[220px]">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-semibold text-sm text-primary">${task.name}</span>
                                ${task.custom_class === 'gantt-milestone' || task.is_milestone
                                    ? '<span class="text-[10px] px-2 py-0.5 rounded-full bg-danger/10 text-danger font-medium">Milestone</span>'
                                    : ''}
                            </div>
                            <div class="text-xs text-secondary space-y-1.5 mb-3">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>${task.start}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>${task.end}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-${statusClass} rounded-full" style="width: ${progress}%"></div>
                                    </div>
                                    <span>${progress}%</span>
                                </div>
                            </div>
                        </div>
                    `;
                },

                applyMilestoneStyles() {
                    const style = document.createElement('style');
                    style.innerHTML = `
                        .gantt-milestone .gantt-bar-progress {
                            background: linear-gradient(135deg, #DC2626 0%, #E11D48 100%) !important;
                            border-radius: 50% !important;
                            transform: scale(1.15);
                            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
                        }
                        .gantt-bar {
                            transition: all 0.2s ease;
                        }
                        .gantt-bar:hover {
                            transform: scaleY(1.08);
                            filter: brightness(1.03);
                        }
                        .gantt-task-completed .gantt-bar-progress { background: linear-gradient(90deg, #10B981 0%, #059669 100%) !important; }
                        .gantt-task-in_progress .gantt-bar-progress { background: linear-gradient(90deg, #F59E0B 0%, #D97706 100%) !important; }
                        .gantt-task-planned .gantt-bar-progress { background: linear-gradient(90deg, #3B82F6 0%, #2563EB 100%) !important; }
                        .gantt-task-waiting_review .gantt-bar-progress { background: linear-gradient(90deg, #F97316 0%, #EA580C 100%) !important; }
                        .gantt-task-revision .gantt-bar-progress { background: linear-gradient(90deg, #8B5CF6 0%, #7C3AED 100%) !important; }
                    `;
                    document.head.appendChild(style);
                },

                async handleDateChange(task, start, end) {
                    try {
                        const taskId = task.task_id || task.id;
                        const response = await fetch(`/api/tasks/${taskId}/dates`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify({ start_date: start, due_date: end })
                        });
                        if (response.ok) {
                            await this.loadTasks();
                            this.showNotification('Dates updated successfully');
                        } else {
                            throw new Error('Failed to update dates');
                        }
                    } catch (error) {
                        console.error('Error updating dates:', error);
                        this.showNotification('Failed to update dates', 'error');
                    }
                },

                async handleProgressChange(task, progress) {
                    try {
                        const taskId = task.task_id || task.id;
                        const response = await fetch(`/api/tasks/${taskId}/progress`, {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify({ progress: progress })
                        });
                        if (response.ok) {
                            await this.loadTasks();
                        }
                    } catch (error) {
                        console.error('Error updating progress:', error);
                    }
                },

                async addActivity() {
                    this.submitting = true;
                    this.errors = {};

                    // Prepare form data - convert empty strings to null
                    const formData = {
                        title: this.form.title.trim(),
                        description: this.form.description?.trim() || null,
                        start_date: this.form.start_date,
                        duration_days: parseInt(this.form.duration_days),
                        parent_task_id: this.form.parent_task_id || null,
                        is_milestone: Boolean(this.form.is_milestone),
                        progress: this.form.is_milestone ? 0 : parseInt(this.form.progress) || 0,
                        priority: this.form.priority || null,
                    };

                    try {
                        const response = await fetch(`/api/students/${this.studentId}/tasks/activity`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.getCsrfToken() },
                            body: JSON.stringify(formData)
                        });

                        const data = await response.json();

                        if (response.ok) {
                            await this.loadTasks();
                            this.showAddForm = false;
                            this.resetForm();
                            this.showNotification('Activity added successfully', 'success');
                        } else {
                            // Handle validation errors
                            if (data.errors || data.message) {
                                this.errors = data.errors || {};
                                this.showNotification(data.message || 'Please fix the errors below', 'error', data.errors);
                            } else {
                                this.showNotification('Failed to add activity', 'error');
                            }
                        }
                    } catch (error) {
                        console.error('Error adding activity:', error);
                        this.showNotification('Network error. Please try again.', 'error');
                    } finally {
                        this.submitting = false;
                    }
                },

                resetForm() {
                    this.form = {
                        title: '',
                        description: '',
                        start_date: new Date().toISOString().split('T')[0],
                        duration_days: 7,
                        parent_task_id: '',
                        is_milestone: false,
                        progress: 0,
                        priority: null,
                    };
                    this.errors = {};
                },

                changeViewMode(mode) {
                    this.viewMode = mode;
                    if (this.ganttInstance) {
                        this.ganttInstance.change_view_mode(mode);
                    }
                },

                async refresh() {
                    this.loading = true;
                    await this.loadTasks();
                    this.loading = false;
                    this.showNotification('Timeline refreshed', 'success');
                },

                viewTaskDetails(task) {
                    // Navigate to task details if available
                    const taskId = task.task_id || task.id;
                    if (taskId) {
                        window.location.href = `/students/${this.studentId}/tasks/${taskId}`;
                    }
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                },

                exportImage() {
                    if (!this.ganttInstance) return;

                    const svg = document.querySelector('#gantt-chart svg');
                    if (!svg) return;

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    const svgData = new XMLSerializer().serializeToString(svg);
                    const svgBlob = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
                    const url = URL.createObjectURL(svgBlob);

                    const img = new Image();
                    img.onload = () => {
                        canvas.width = svg.clientWidth * 2;
                        canvas.height = svg.clientHeight * 2;
                        ctx.scale(2, 2);
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.drawImage(img, 0, 0);
                        URL.revokeObjectURL(url);

                        const link = document.createElement('a');
                        link.download = `timeline-${this.studentId}-${new Date().toISOString().split('T')[0]}.png`;
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        this.showNotification('Image exported successfully', 'success');
                    };
                    img.src = url;
                },

                exportPdf() {
                    if (!this.ganttInstance) return;

                    const element = document.querySelector('.gantt-container');
                    if (!element) return;

                    const opt = {
                        margin: 10,
                        filename: `timeline-${this.studentId}-${new Date().toISOString().split('T')[0]}.pdf`,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2 },
                        jsPDF: { unit: 'mm', format: 'a4', orientation: 'landscape' }
                    };

                    html2pdf().set(opt).from(element).save().then(() => {
                        this.showNotification('PDF exported successfully', 'success');
                    });
                },

                showNotification(message, type = 'success', errors = null) {
                    const id = ++this.notificationCounter;
                    this.notifications.push({
                        id,
                        message,
                        type,
                        errors,
                        show: true
                    });

                    setTimeout(() => {
                        this.dismissNotification(id);
                    }, 4000);
                },

                dismissNotification(id) {
                    const index = this.notifications.findIndex(n => n.id === id);
                    if (index > -1) {
                        this.notifications.splice(index, 1);
                    }
                },

                getCsrfToken() {
                    return document.querySelector('meta[name="csrf-token"]')?.content || '';
                }
            };
        }
    </script>

    <style>
        .gantt-popup {
            min-width: 220px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border: 1px solid #E5E5E4;
        }
        .gantt-container {
            background: linear-gradient(135deg, #FAFAF9 0%, #F5F5F4 100%);
            border-radius: 12px;
            padding: 16px;
        }
    </style>
</x-layouts.app>
