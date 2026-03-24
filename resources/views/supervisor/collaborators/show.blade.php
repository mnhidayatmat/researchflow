<x-layouts.app :title="$collaborator->name">
    <x-slot:header>Collaborator Detail</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-primary dark:text-dark-primary">{{ $collaborator->name }}</h2>
                <p class="mt-0.5 sm:mt-1 text-xs sm:text-sm text-secondary dark:text-dark-secondary truncate">{{ $collaborator->institution_name }}</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <x-button href="{{ route('supervisor.collaborators.edit', $collaborator) }}" variant="secondary" size="sm" class="flex-1 justify-center sm:flex-none">Edit</x-button>
                <form method="POST" action="{{ route('supervisor.collaborators.destroy', $collaborator) }}" onsubmit="return confirm('Delete this collaborator record?');" class="flex-1 sm:flex-none">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger" size="sm" class="w-full justify-center sm:w-auto">Delete</x-button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Category</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->category_label }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Position / Title</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->position_title ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">University / Organization</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->institution_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Country</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->country ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Department</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->department ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Faculty</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->faculty ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Working Email</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->working_email }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Phone Number</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->phone_number ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Expertise</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->expertise_area ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Field</p>
                            <p class="mt-1 text-sm font-medium text-primary dark:text-dark-primary">{{ $collaborator->research_field ?: 'Not set' }}</p>
                        </div>
                    </div>

                    @if($collaborator->notes)
                        <div class="mt-5 border-t border-border dark:border-dark-border pt-4">
                            <p class="text-[10px] uppercase tracking-wide text-tertiary dark:text-dark-tertiary">Notes</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-primary dark:text-dark-primary">{{ $collaborator->notes }}</p>
                        </div>
                    @endif
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card>
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-primary dark:text-dark-primary">Usage</h3>
                        <div class="space-y-2 text-sm text-primary dark:text-dark-primary">
                            <div class="flex items-center justify-between rounded-xl bg-surface/70 px-3 py-2">
                                <span>Grant collaborator</span>
                                <span>{{ $collaborator->suitable_for_grant ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-surface/70 px-3 py-2">
                                <span>Paper co-researcher</span>
                                <span>{{ $collaborator->suitable_for_publication ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-surface/70 px-3 py-2">
                                <span>Suggested reviewer</span>
                                <span>{{ $collaborator->suggested_reviewer ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
