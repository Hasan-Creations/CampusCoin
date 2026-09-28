@props([
    'stats' => [],
])

<div {{ $attributes->merge(['class' => 'stat-strip w-full grid grid-cols-2 md:grid-cols-4 gap-3']) }}>
    @if(!empty($stats))
        @foreach($stats as $stat)
            <div class="p-5 sm:p-6 flex flex-col justify-between">
                <span class="text-xs font-caps text-[var(--muted)]">{{ $stat['label'] }}</span>
                <div class="mt-2 text-2xl font-mono font-medium text-[var(--ink)] tabular-nums {{ ($stat['variant'] ?? '') === 'income' ? 'text-[var(--accent)]' : (($stat['variant'] ?? '') === 'expense' ? 'text-[var(--expense)]' : '') }}">
                    {{ $stat['value'] }}
                </div>
                @if(!empty($stat['meta']))
                    <div class="mt-1 text-xs text-[var(--muted)]">
                        {{ $stat['meta'] }}
                    </div>
                @endif
            </div>
        @endforeach
    @else
        {{ $slot }}
    @endif
</div>
