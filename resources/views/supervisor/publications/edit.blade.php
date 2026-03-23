<x-layouts.app title="Edit Publication">
    <x-slot:header>Edit Publication</x-slot:header>

    <form method="POST" action="{{ route('supervisor.publications.update', $publication) }}" class="space-y-6">
        @csrf
        @method('PUT')
        @include('supervisor.publications._form')
    </form>
</x-layouts.app>
