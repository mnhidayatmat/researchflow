<x-layouts.app title="New Publication">
    <x-slot:header>New Publication</x-slot:header>

    <form method="POST" action="{{ route('supervisor.publications.store') }}" class="space-y-6">
        @csrf
        @include('supervisor.publications._form')
    </form>
</x-layouts.app>
