<x-layouts.app title="Edit Publication Track">
    <x-slot:header>Edit Publication Track</x-slot:header>

    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-secondary">{{ $student->user->name }}'s journal submission tracker</p>
            </div>
        </div>

        <x-card title="Publication Details" subtitle="Update stage, rejection rounds, and reviewer comments.">
            <form method="POST" action="{{ route('publications.update', [$student, $publicationTrack]) }}">
                @csrf
                @method('PUT')
                @include('publications._form')
            </form>
        </x-card>
    </div>
</x-layouts.app>
