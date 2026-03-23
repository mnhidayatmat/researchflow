<x-layouts.app title="Edit Collaborator">
    <x-slot:header>Edit Collaborator</x-slot:header>

    <form method="POST" action="{{ route('supervisor.collaborators.update', $collaborator) }}">
        @csrf
        @method('PUT')
        @include('supervisor.collaborators._form', ['submitLabel' => 'Save Changes'])
    </form>
</x-layouts.app>
