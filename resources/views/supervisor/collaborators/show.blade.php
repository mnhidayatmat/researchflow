<x-layouts.app :title="$collaborator->name">
    <x-slot:header>Collaborator Detail</x-slot:header>

    <div class="space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-base font-semibold text-primary">{{ $collaborator->name }}</h2>
                <p class="mt-1 text-sm text-secondary">{{ $collaborator->institution_name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <x-button href="{{ route('supervisor.collaborators.edit', $collaborator) }}" variant="secondary">Edit</x-button>
                <form method="POST" action="{{ route('supervisor.collaborators.destroy', $collaborator) }}" onsubmit="return confirm('Delete this collaborator record?');">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">Delete</x-button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Category</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->category_label }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Position / Title</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->position_title ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">University / Organization</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->institution_name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Country</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->country ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Department</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->department ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Faculty</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->faculty ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Working Email</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->working_email }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Phone Number</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->phone_number ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Expertise</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->expertise_area ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Field</p>
                            <p class="mt-1 text-sm font-medium text-primary">{{ $collaborator->research_field ?: 'Not set' }}</p>
                        </div>
                    </div>

                    @if($collaborator->notes)
                        <div class="mt-5 border-t border-border pt-4">
                            <p class="text-[10px] uppercase tracking-wide text-tertiary">Notes</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-primary">{{ $collaborator->notes }}</p>
                        </div>
                    @endif
                </x-card>
            </div>

            <div class="space-y-6">
                <x-card>
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-primary">Usage</h3>
                        <div class="space-y-2 text-sm text-primary">
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
