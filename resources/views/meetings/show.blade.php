<x-layouts.app title="{{ $meeting->title }}">
    <x-slot:header>Meeting Detail</x-slot:header>

    <div class="max-w-3xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between mb-5 sm:mb-6">
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-semibold text-primary dark:text-dark-primary">{{ $meeting->title }}</h2>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">{{ $meeting->scheduled_at->format('d M Y, h:i A') }} &middot; {{ ucfirst(str_replace('_', ' ', $meeting->type)) }}</p>
            </div>
            <x-status-badge :status="$meeting->status" />
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-4">
                @if($meeting->agenda)
                    <x-card title="Agenda">
                        <div class="text-sm text-secondary dark:text-dark-secondary whitespace-pre-wrap">{{ $meeting->agenda }}</div>
                    </x-card>
                @endif

                {{-- Notes form / display --}}
                <x-card title="Meeting Notes">
                    @if($meeting->status === 'completed' && $meeting->notes)
                        <div class="text-sm text-secondary dark:text-dark-secondary whitespace-pre-wrap">{{ $meeting->notes }}</div>
                    @else
                        <form method="POST" action="{{ route('meetings.update', [$student, $meeting]) }}" x-data="{ items: [] }" class="space-y-4">
                            @csrf @method('PUT')
                            <x-textarea name="notes" rows="5" placeholder="Record meeting notes...">{{ $meeting->notes }}</x-textarea>

                            <div>
                                <p class="text-xs font-medium text-primary dark:text-dark-primary mb-2">Action Items</p>
                                <template x-for="(item, index) in items" :key="index">
                                    <div class="flex gap-2 mb-2">
                                        <input :name="`action_items[${index}][description]`" x-model="item.description" class="flex-1 rounded-lg border border-border dark:border-dark-border px-3 py-1.5 text-sm" placeholder="Action item...">
                                        <input :name="`action_items[${index}][due_date]`" x-model="item.due_date" type="date" class="rounded-lg border border-border dark:border-dark-border px-3 py-1.5 text-sm">
                                        <button type="button" @click="items.splice(index, 1)" class="text-red-400 hover:text-red-600 text-sm">&times;</button>
                                    </div>
                                </template>
                                <button type="button" @click="items.push({description: '', due_date: ''})" class="text-xs text-accent hover:underline">+ Add action item</button>
                            </div>

                            <x-select name="status" label="Meeting Status" :options="['scheduled' => 'Scheduled', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled']" :selected="$meeting->status" />

                            <x-button type="submit" variant="accent" size="sm">Save</x-button>
                        </form>
                    @endif
                </x-card>

                {{-- Action items --}}
                @if($meeting->actionItems->count())
                    <x-card title="Action Items">
                        @foreach($meeting->actionItems as $item)
                            <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-border' : '' }}">
                                <div class="w-4 h-4 mt-0.5 rounded border {{ $item->is_completed ? 'bg-green-500 border-green-500' : 'border-gray-300' }} flex items-center justify-center">
                                    @if($item->is_completed)
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm {{ $item->is_completed ? 'line-through text-secondary' : '' }}">{{ $item->description }}</p>
                                    <p class="text-xs text-secondary dark:text-dark-secondary">
                                        @if($item->assignee) {{ $item->assignee->name }} @endif
                                        @if($item->due_date) &middot; Due {{ $item->due_date->format('d M') }} @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </x-card>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                <x-card>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Mode</p>
                            <p>{{ ucfirst(str_replace('_', ' ', $meeting->mode)) }}</p>
                        </div>
                        @if($meeting->location)
                            <div>
                                <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Location</p>
                                <p>{{ $meeting->location }}</p>
                            </div>
                        @endif
                        @if($meeting->meeting_link)
                            <div>
                                <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Meeting Link</p>
                                <a href="{{ $meeting->meeting_link }}" target="_blank" class="text-accent hover:underline text-xs break-all">{{ $meeting->meeting_link }}</a>
                            </div>
                        @endif
                        @if($meeting->duration_minutes)
                            <div>
                                <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Duration</p>
                                <p>{{ $meeting->duration_minutes }} minutes</p>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wide">Attendees</p>
                            @foreach($meeting->attendees as $attendee)
                                <p class="text-xs">{{ $attendee->name }}</p>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
