@props([
    'message' => 'No records found.',
    'actionText' => null,
    'actionHref' => null,
    'actionWireClick' => null,
])

<div {{ $attributes->merge(['class' => 'empty-state border border-dashed hairline-border p-12 text-center bg-transparent']) }}>
    <p class="text-sm text-[var(--muted)] font-sans max-w-sm mx-auto">
        {{ $message ?: $slot }}
    </p>
    @if($actionText)
        <div class="mt-4">
            @if($actionHref)
                <x-button variant="primary" :href="$actionHref">
                    {{ $actionText }}
                </x-button>
            @elseif($actionWireClick)
                <x-button variant="primary" :wire:click="$actionWireClick">
                    {{ $actionText }}
                </x-button>
            @elseif(isset($action))
                {{ $action }}
            @endif
        </div>
    @endif
</div>
