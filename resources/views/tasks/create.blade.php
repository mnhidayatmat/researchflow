<x-layouts.app title="Create Task">
    <x-slot:header>Create Task</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            <form method="POST" action="{{ route('tasks.store', $student) }}" class="space-y-4">
                @csrf
                <x-input name="title" label="Title" required placeholder="Task title" />
                <x-textarea name="description" label="Description" placeholder="Describe what needs to be done..." />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-select name="milestone_id" label="Milestone" :options="$milestones->pluck('name', 'id')->toArray()" />
                    <x-select name="priority" label="Priority" required :options="['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent']" selected="medium" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input name="start_date" type="date" label="Start Date" />
                    <x-input name="due_date" type="date" label="Due Date" />
                </div>

                <x-input name="estimated_hours" type="number" label="Estimated Hours" />

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <x-button href="{{ route('tasks.index', $student) }}" variant="secondary" class="w-full justify-center sm:w-auto">Cancel</x-button>
                    <x-button type="submit" variant="accent" class="w-full justify-center sm:w-auto">Create Task</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
