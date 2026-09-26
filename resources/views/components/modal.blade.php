@props([
    'show' => false,
    'id' => null,
    'title' => null,
    'titleId' => 'modal-title',
    'maxWidth' => 'md',
    'onClose' => null,
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        '4xl' => 'sm:max-w-4xl',
        default => 'sm:max-w-lg',
    };
    $hasWireModel = (bool) $attributes->wire('model')->value();
@endphp

<div x-data="{ 
        open: @if($hasWireModel) @entangle($attributes->wire('model')) @else {{ $show ? 'true' : 'false' }} @endif,
        close() {
            this.open = false;
            @if($onClose)
                {{ $onClose }};
            @endif
        }
     }"
     x-show="open"
     x-cloak
     @if($id) id="{{ $id }}" @endif
     role="dialog"
     aria-modal="true"
     @if($titleId) aria-labelledby="{{ $titleId }}" @endif
     @keydown.escape.window="close()"
     class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         class="fixed inset-0 bg-black/60"></div>

    <!-- Modal Panel (fade + 8px upward slide, 200ms ease per §7) -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="relative w-full {{ $maxWidthClass }} modal-dialog-surface bg-[var(--panel)] border hairline-border p-6 sm:p-8 z-10">
        @if($title)
            <div class="flex items-center justify-between pb-4 mb-6 border-b hairline-border">
                <h3 id="{{ $titleId }}" class="font-display text-xl font-medium text-[var(--ink)]">
                    {{ $title }}
                </h3>
                <button type="button" @click="close()" aria-label="Close dialog" class="btn-icon">
                    <x-icon name="x" class="w-4 h-4" />
                </button>
            </div>
        @endif

        {{ $slot }}
    </div>
</div>

