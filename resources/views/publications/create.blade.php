<x-layouts.app title="Add Publication Track">
    <x-slot:header>Add Publication Track</x-slot:header>

    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-secondary dark:text-dark-secondary">{{ $student->user->name }}'s journal submission tracker</p>
            </div>
        </div>

        <x-card title="Publication Details" subtitle="Track submission stage, rejection rounds, and reviewer input.">
            <form method="POST" action="{{ route('publications.store', $student) }}">
                @csrf
                @include('publications._form')
            </form>
        </x-card>
    </div>
</x-layouts.app>
