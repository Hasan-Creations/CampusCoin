@props([
    'type' => 'success', // success (accent), error (expense), warning (secondary)
    'message' => '',
])

@php
    $bgStyle = match($type) {
        'error', 'danger' => 'bg-[var(--expense)] text-[var(--paper)] border-[var(--expense)]',
        'warning' => 'bg-[var(--secondary)] text-[var(--paper)] border-[var(--secondary)]',
        default => 'bg-[var(--accent)] text-[var(--paper)] border-[var(--accent)]',
    };
@endphp

<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 4000)"
     class="border p-4 shadow-none flex items-center justify-between gap-3 text-sm {{ $bgStyle }}">
    <div class="flex items-center gap-2">
        <span class="font-sans font-medium">{{ $message ?: $slot }}</span>
    </div>
    <button type="button" @click="show = false" class="p-1 hover:opacity-75" aria-label="Dismiss message">
        <x-icon name="x" class="w-4 h-4" />
    </button>
</div>
