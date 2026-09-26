@props([
    'type' => 'text',
    'numeric' => false,
    'hasError' => false,
])

@php
    $baseClass = 'field w-full';
    if ($numeric) {
        $baseClass .= ' field-numeric';
    }
    if ($hasError) {
        $baseClass .= ' has-error';
    }
@endphp

@if($type === 'textarea')
    <textarea {{ $attributes->merge(['class' => $baseClass]) }}>{{ $slot }}</textarea>
@elseif($type === 'select')
    <select {{ $attributes->merge(['class' => $baseClass]) }}>
        {{ $slot }}
    </select>
@else
    <input type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }} />
@endif
