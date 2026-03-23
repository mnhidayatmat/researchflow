<x-layouts.app title="New Collaborator">
    <x-slot:header>New Collaborator</x-slot:header>

    <form method="POST" action="{{ route('supervisor.collaborators.store') }}">
        @csrf
        @include('supervisor.collaborators._form', ['submitLabel' => 'Create Collaborator'])
    </form>
</x-layouts.app>
