<x-layouts.app title="Schedule Meeting">
    <x-slot:header>Schedule Meeting</x-slot:header>

    <div class="max-w-2xl">
        <x-card>
            <form method="POST" action="{{ route('meetings.store', $student) }}" class="space-y-4">
                @csrf
                <x-input name="title" label="Title" required placeholder="e.g. Weekly Supervision Meeting" />
                <x-textarea name="agenda" label="Agenda" rows="3" placeholder="Meeting agenda items..." />

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-select name="type" label="Type" required :options="['supervision' => 'Supervision', 'progress_review' => 'Progress Review', 'viva' => 'Viva', 'other' => 'Other']" />
                    <x-select name="mode" label="Mode" required :options="['in_person' => 'In Person', 'online' => 'Online', 'hybrid' => 'Hybrid']" />
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <x-input name="scheduled_at" type="datetime-local" label="Date & Time" required />
                    <x-input name="duration_minutes" type="number" label="Duration (minutes)" placeholder="60" />
                </div>

                <x-input name="location" label="Location" placeholder="Room number or building" />
                <x-input name="meeting_link" label="Meeting Link" type="url" placeholder="https://meet.google.com/..." />

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-2">
                    <x-button href="{{ route('meetings.index', $student) }}" variant="secondary" class="w-full justify-center sm:w-auto">Cancel</x-button>
                    <x-button type="submit" variant="accent" class="w-full justify-center sm:w-auto">Schedule Meeting</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
