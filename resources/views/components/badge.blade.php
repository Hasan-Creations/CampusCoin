@props([
    'variant' => 'default', // default, income, expense, secondary
])

@php
    $variantClass = match($variant) {
        'income', 'accent', 'success' => 'badge-income',
        'expense', 'destructive', 'danger' => 'badge-expense',
        'secondary', 'gold' => 'badge-secondary',
        default => '',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$variantClass}"]) }}>
    {{ $slot }}
</span>
