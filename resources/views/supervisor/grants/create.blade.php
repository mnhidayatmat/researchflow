<x-layouts.app title="New Grant">
    <x-slot:header>New Grant</x-slot:header>

    <form method="POST" action="{{ route('supervisor.grants.store') }}">
        @csrf
        @include('supervisor.grants._form', ['submitLabel' => 'Create Grant'])
    </form>
</x-layouts.app>
