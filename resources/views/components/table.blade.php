@props([
    'headers' => [],
    'striped' => false,
    'bordered' => true,
    'hover' => true,
    'compact' => false,
    'empty' => 'No data available',
    'emptyIcon' => null,
])

@php
$rowClass = $compact
    ? 'px-4 py-2.5'
    : 'px-5 py-3.5';
@endphp

<div class="overflow-hidden rounded-xl border border-border dark:border-dark-border bg-white dark:bg-dark-card" x-data="{ selected: [] }">
    <div class="overflow-x-auto scrollbar-thin">
        <table class="min-w-full text-left text-sm">
            @if($headers)
                <thead class="text-xs text-secondary dark:text-dark-secondary uppercase tracking-wider bg-surface/50 dark:bg-dark-surface/50 border-b border-border dark:border-dark-border">
                    <tr>
                        @foreach($headers as $header)
                            @php
                                $isAction = ($header['key'] ?? '') === 'actions';
                                $align = $header['align'] ?? 'left';
                                $alignClass = match($align) {
                                    'center' => 'text-center',
                                    'right' => 'text-right',
                                    default => 'text-left',
                                };
                                $width = $header['width'] ?? '';
                            @endphp
                            <th scope="col"
                                class="{{ $rowClass }} font-semibold {{ $alignClass }} {{ $isAction ? 'text-right' : '' }}"
                                @if($width) style="{{ $width }}" @endif>
                                @if(isset($header['label']))
                                    {{ $header['label'] }}
                                @elseif(is_string($header))
                                    {{ $header }}
                                @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-border dark:divide-dark-border {{ $striped ? 'divide-y-0' : '' }}">
                @if($slot->isNotEmpty())
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="{{ count($headers) ?: 1 }}" class="px-5 py-12 text-center">
                            <div class="flex flex-col items-center gap-3 text-secondary dark:text-dark-secondary">
                                @if($emptyIcon)
                                    <svg class="w-10 h-10 text-tertiary dark:text-dark-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $emptyIcon }}"/>
                                    </svg>
                                @else
                                    <svg class="w-10 h-10 text-tertiary dark:text-dark-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                @endif
                                <p class="text-sm">{{ $empty }}</p>
                            </div>
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
