<x-layouts.app title="Edit Grant">
    <x-slot:header>Edit Grant</x-slot:header>

    <form method="POST" action="{{ route('supervisor.grants.update', $grant) }}">
        @csrf
        @method('PUT')
        @include('supervisor.grants._form', ['submitLabel' => 'Save Changes'])
    </form>
</x-layouts.app>
