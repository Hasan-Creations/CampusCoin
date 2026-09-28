@props([
    'variant' => 'primary', 
    'type' => 'button',
    'href' => null,
])

@php
    $variantClass = match($variant) {
        'accent' => 'btn-accent',
        'secondary' => 'btn-secondary',
        'destructive', 'danger' => 'btn-destructive',
        default => 'btn-primary',
    };
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "btn {$variantClass}"]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => "btn {$variantClass}"]) }}>
        {{ $slot }}
    </button>
@endif
