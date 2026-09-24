@props([
    'headers' => [],
])

<div class="w-full overflow-x-auto border hairline-border bg-[var(--panel)]">
    <table {{ $attributes->merge(['class' => 'ledger-table w-full']) }}>
        @if(isset($head))
            <thead>
                {{ $head }}
            </thead>
        @elseif(!empty($headers))
            <thead>
                <tr>
                    @foreach($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-[var(--hairline)]">
            {{ $slot }}
        </tbody>
    </table>
</div>
