<x-layouts.app title="Programmes">
    <x-slot:header>Programmes</x-slot:header>

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-5 sm:mb-6">
        <div>
            <h2 class="text-base font-semibold text-primary dark:text-dark-primary">Academic Programmes</h2>
            <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Manage research degree programmes</p>
        </div>
        <x-button href="{{ route('admin.programmes.create') }}" variant="primary" size="sm" class="w-full justify-center sm:w-auto">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Programme
        </x-button>
    </div>

    {{-- Mobile Card List --}}
    <div class="sm:hidden space-y-3">
        @forelse($programmes as $programme)
            <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-primary dark:text-dark-primary">{{ $programme->name }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-dark-surface text-[10px] font-mono text-gray-600 dark:text-dark-secondary">
                                {{ $programme->code }}
                            </span>
                            @if($programme->duration_months)
                            <span class="text-xs text-secondary dark:text-dark-secondary">{{ $programme->duration_months }}mo</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        <a href="{{ route('admin.programmes.edit', $programme) }}" class="p-2 text-secondary dark:text-dark-secondary hover:text-primary dark:hover:text-dark-primary hover:bg-surface dark:hover:bg-dark-surface rounded-lg transition-colors" title="Edit">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.programmes.destroy', $programme) }}" onsubmit="return confirm('Delete this programme?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-secondary dark:text-dark-secondary hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Delete">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @if($programme->description)
                <p class="text-xs text-secondary dark:text-dark-secondary mt-2 line-clamp-2">{{ $programme->description }}</p>
                @endif
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border dark:border-dark-border">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-tertiary dark:text-dark-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-xs text-secondary dark:text-dark-secondary">
                            <span class="font-medium text-primary dark:text-dark-primary">{{ $programme->students_count ?? 0 }}</span> enrolled
                        </span>
                    </div>
                    @if($programme->duration_months)
                    <span class="text-xs text-secondary dark:text-dark-secondary">{{ $programme->duration_months }} months</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-card dark:bg-dark-card rounded-2xl border border-border dark:border-dark-border p-8 text-center">
                <svg class="w-10 h-10 text-tertiary dark:text-dark-tertiary mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                <p class="text-sm font-medium text-primary dark:text-dark-primary">No programmes yet</p>
                <p class="text-xs text-secondary dark:text-dark-secondary mt-0.5">Create your first academic programme</p>
            </div>
        @endforelse
    </div>

    {{-- Desktop Table --}}
    <x-card :padding='false' class="hidden sm:block">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border dark:border-dark-border bg-surface dark:bg-dark-surface">
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Programme</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Code</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Duration</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Students</th>
                        <th class="text-left text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Description</th>
                        <th class="text-right text-xs font-medium text-secondary dark:text-dark-secondary uppercase tracking-wider px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border dark:divide-dark-border">
                    @forelse($programmes as $programme)
                        <tr class="hover:bg-surface/60 dark:hover:bg-dark-surface/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-primary dark:text-dark-primary">{{ $programme->name }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 dark:bg-dark-surface text-xs font-mono text-gray-600 dark:text-dark-secondary">
                                    {{ $programme->code }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-secondary dark:text-dark-secondary">
                                {{ $programme->duration_months ? $programme->duration_months . ' months' : '—' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm font-medium text-primary dark:text-dark-primary">{{ $programme->students_count ?? 0 }}</span>
                                    <span class="text-xs text-secondary dark:text-dark-secondary">enrolled</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-secondary dark:text-dark-secondary">
                                <p class="truncate max-w-[260px] text-xs">{{ $programme->description ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.programmes.edit', $programme) }}" class="p-1.5 text-secondary hover:text-primary hover:bg-gray-100 dark:hover:bg-dark-surface rounded transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.programmes.destroy', $programme) }}" onsubmit="return confirm('Delete this programme? Students enrolled will not be removed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-secondary hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="text-secondary dark:text-dark-secondary">
                                    <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    <p class="text-sm">No programmes yet</p>
                                    <p class="text-xs mt-0.5">Create your first academic programme</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.app>
