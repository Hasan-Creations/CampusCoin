<div>
    <x-slot:header>
        Saving Tips
    </x-slot:header>

    <div class="space-y-6">
        {{-- ===================================================== --}}
        {{-- HEADER BAR: TITLE, KPI SUMMARY & RE-EVALUATE ACTION    --}}
        {{-- ===================================================== --}}
        <div class="p-6 rounded-[8px] border hairline-border bg-[var(--bg-surface)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="text-xs font-mono text-[var(--accent-primary)] font-semibold uppercase tracking-wider mb-1">
                    Deterministic Financial Intelligence &bull; Active Analysis
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">
                    Saving Opportunities
                </h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">
                    Actionable, data-driven saving tips evaluated directly from your spending ledger, category budgets, and savings targets.
                </p>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                <button type="button"
                        wire:click="refreshTips"
                        class="btn-secondary py-2 px-4 text-xs inline-flex items-center gap-2">
                    <x-icon name="refresh-cw" class="w-3.5 h-3.5" wire:loading.class="animate-spin" wire:target="refreshTips" />
                    <span>Re-evaluate Ledger</span>
                </button>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- FLASH FEEDBACK ALERT                                  --}}
        {{-- ===================================================== --}}
        @if ($feedbackMessage)
            <div class="p-4 rounded-[6px] border border-emerald-200 bg-emerald-50 dark:border-emerald-900/50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <x-icon name="check-circle" class="w-4 h-4 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" class="text-emerald-600 hover:text-emerald-800 font-mono text-base leading-none">
                    &times;
                </button>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- KPI STRIP: TOTAL POTENTIAL SAVINGS & METRICS          --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card-campus border hairline-border p-5 space-y-1">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Identified Potential Savings</span>
                    <x-icon name="trending-up" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                    ${{ number_format($totalPotentialSavings, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Estimated monthly reduction if active tips are applied
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-1">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Active Opportunities</span>
                    <x-icon name="lightbulb" class="w-4 h-4 text-[var(--accent-primary)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    {{ $activeCount }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Immediate ledger-derived spending optimizations
                </div>
            </div>

            <div class="card-campus border hairline-border p-5 space-y-1">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Pinned Strategies</span>
                    <x-icon name="bookmark" class="w-4 h-4 text-[var(--gold)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--gold)] tabular-nums">
                    {{ $pinnedCount }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Bookmarked rules saved for student review
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- SEGMENTED TAB CONTROLS                                --}}
        {{-- ===================================================== --}}
        <div class="flex items-center justify-between border-b hairline-border pb-3">
            <div class="inline-flex items-center gap-1.5 p-1 rounded-[6px] border hairline-border bg-[var(--bg-subtle)]">
                <button type="button"
                        wire:click="setTab('active')"
                        class="px-3.5 py-1.5 rounded-[4px] text-xs font-mono transition-colors flex items-center gap-2 {{ $activeTab === 'active' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    <span>Active Opportunities</span>
                    <span class="px-1.5 py-0.5 rounded-[3px] text-[10px] {{ $activeTab === 'active' ? 'bg-[var(--accent-tint)] text-[var(--accent-primary)] font-bold' : 'bg-[var(--bg-canvas)] text-[var(--text-muted)]' }}">
                        {{ $activeCount }}
                    </span>
                </button>

                <button type="button"
                        wire:click="setTab('pinned')"
                        class="px-3.5 py-1.5 rounded-[4px] text-xs font-mono transition-colors flex items-center gap-2 {{ $activeTab === 'pinned' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    <x-icon name="bookmark" class="w-3.5 h-3.5 text-[var(--gold)]" />
                    <span>Pinned</span>
                    <span class="px-1.5 py-0.5 rounded-[3px] text-[10px] {{ $activeTab === 'pinned' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-bold' : 'bg-[var(--bg-canvas)] text-[var(--text-muted)]' }}">
                        {{ $pinnedCount }}
                    </span>
                </button>

                <button type="button"
                        wire:click="setTab('dismissed')"
                        class="px-3.5 py-1.5 rounded-[4px] text-xs font-mono transition-colors flex items-center gap-2 {{ $activeTab === 'dismissed' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                    <span>Dismissed</span>
                    <span class="px-1.5 py-0.5 rounded-[3px] text-[10px] {{ $activeTab === 'dismissed' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-bold' : 'bg-[var(--bg-canvas)] text-[var(--text-muted)]' }}">
                        {{ $dismissedCount }}
                    </span>
                </button>
            </div>

            <div class="hidden sm:flex items-center gap-1.5 text-xs font-mono text-[var(--text-muted)]">
                <span>Ranked by estimated savings impact</span>
                <x-icon name="arrow-down" class="w-3.5 h-3.5" />
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- TIPS LISTING                                          --}}
        {{-- ===================================================== --}}
        @if ($tips->isEmpty())
            <div class="card-campus border hairline-border p-12 text-center space-y-3">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                    @if ($activeTab === 'pinned')
                        <x-icon name="bookmark" class="w-6 h-6" />
                    @elseif ($activeTab === 'dismissed')
                        <x-icon name="archive" class="w-6 h-6" />
                    @else
                        <x-icon name="check-circle" class="w-6 h-6 text-emerald-500" />
                    @endif
                </div>

                <div class="font-heading font-bold text-base text-[var(--text-primary)]">
                    @if ($activeTab === 'pinned')
                        No Pinned Strategies
                    @elseif ($activeTab === 'dismissed')
                        No Dismissed Opportunities
                    @else
                        No Active Budget Deviations Detected
                    @endif
                </div>

                <p class="text-xs text-[var(--text-muted)] max-w-md mx-auto">
                    @if ($activeTab === 'pinned')
                        You haven't bookmarked any tips yet. Pin critical tips from the Active Opportunities tab to monitor them continuously.
                    @elseif ($activeTab === 'dismissed')
                        You have not dismissed any tips. When you dismiss tips you don't wish to track, they are archived here for optional restoration.
                    @else
                        Your financial ledger is operating strictly within historical averages and category budgets. Continue logging transactions to maintain real-time evaluation.
                    @endif
                </p>

                @if ($activeTab === 'active')
                    <div class="pt-2">
                        <a href="{{ route('transactions') }}" class="btn-primary py-2 px-4 text-xs inline-flex items-center gap-2">
                            <x-icon name="plus" class="w-3.5 h-3.5" />
                            <span>Log Transaction</span>
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="space-y-4">
                @foreach ($tips as $tip)
                    <div class="card-campus border hairline-border p-5 space-y-4 hover:border-[var(--accent-primary)]/40 transition-colors" wire:key="tip-card-{{ $tip->id }}">
                        {{-- Top Metadata Strip --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                {{-- Category Badge or Ledger Badge --}}
                                @if ($tip->category)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] text-xs font-medium text-white"
                                          style="background-color: {{ $tip->category->color ?? '#64748B' }};">
                                        <x-icon :name="$tip->category->icon ?? 'tag'" class="w-3.5 h-3.5" />
                                        <span>{{ $tip->category->name }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[4px] text-xs font-mono font-medium border hairline-border bg-[var(--bg-subtle)] text-[var(--text-primary)]">
                                        <x-icon name="activity" class="w-3.5 h-3.5 text-[var(--accent-primary)]" />
                                        <span>Overall Ledger</span>
                                    </span>
                                @endif

                                {{-- Rule Key Pill --}}
                                <span class="badge-campus text-[10px] font-mono uppercase tracking-wider bg-[var(--bg-subtle)] text-[var(--text-muted)] border hairline-border">
                                    {{ str_replace('_', ' ', $tip->rule_key) }}
                                </span>

                                @if ($tip->isPinned())
                                    <span class="badge-campus text-[10px] font-mono uppercase tracking-wider bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1">
                                        <x-icon name="bookmark" class="w-3 h-3" />
                                        <span>Pinned</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Potential Savings Badge --}}
                            <div class="flex items-center gap-2">
                                <div class="px-3 py-1.5 rounded-[4px] border border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                                    <span class="text-[10px] font-mono uppercase tracking-wider">Est. Potential Savings:</span>
                                    <span class="font-mono font-bold text-sm tabular-nums">
                                        {{ $tip->formattedEstimatedSavings() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Title & Trigger Explanation --}}
                        <div class="space-y-1.5">
                            <h2 class="font-heading font-bold text-base text-[var(--text-primary)]">
                                {{ $tip->title }}
                            </h2>
                            <p class="text-xs text-[var(--text-muted)] leading-relaxed">
                                {{ $tip->message }}
                            </p>
                        </div>

                        {{-- Actionable Suggestion Box --}}
                        <div class="p-3.5 rounded-[6px] border hairline-border bg-[var(--bg-subtle)]/50 space-y-1">
                            <div class="flex items-center gap-1.5 text-[11px] font-mono uppercase tracking-wider text-[var(--accent-primary)] font-semibold">
                                <x-icon name="compass" class="w-3.5 h-3.5" />
                                <span>Actionable Recommendation</span>
                            </div>
                            <p class="text-xs font-medium text-[var(--text-primary)] leading-normal">
                                {{ $tip->suggestion }}
                            </p>
                        </div>

                        {{-- Action Buttons Footer --}}
                        <div class="flex items-center justify-between pt-2 border-t hairline-border">
                            <div class="text-[10px] font-mono text-[var(--text-muted)]">
                                Evaluated: {{ $tip->updated_at->diffForHumans() }}
                                @if ($tip->pinned_at)
                                    &bull; Pinned {{ $tip->pinned_at->format('M d') }}
                                @elseif ($tip->dismissed_at)
                                    &bull; Dismissed {{ $tip->dismissed_at->format('M d') }}
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($tip->isActive())
                                    <button type="button"
                                            wire:click="pinTip({{ $tip->id }})"
                                            class="btn-secondary py-1.5 px-3 text-xs inline-flex items-center gap-1.5">
                                        <x-icon name="bookmark" class="w-3.5 h-3.5 text-[var(--gold)]" />
                                        <span>Pin Tip</span>
                                    </button>

                                    <button type="button"
                                            wire:click="dismissTip({{ $tip->id }})"
                                            class="btn-secondary py-1.5 px-3 text-xs inline-flex items-center gap-1.5 text-[var(--text-muted)] hover:text-rose-600 dark:hover:text-rose-400">
                                        <x-icon name="x" class="w-3.5 h-3.5" />
                                        <span>Dismiss</span>
                                    </button>
                                @elseif ($tip->isPinned())
                                    <button type="button"
                                            wire:click="unpinTip({{ $tip->id }})"
                                            class="btn-secondary py-1.5 px-3 text-xs inline-flex items-center gap-1.5">
                                        <x-icon name="bookmark-minus" class="w-3.5 h-3.5 text-[var(--gold)]" />
                                        <span>Unpin</span>
                                    </button>

                                    <button type="button"
                                            wire:click="dismissTip({{ $tip->id }})"
                                            class="btn-secondary py-1.5 px-3 text-xs inline-flex items-center gap-1.5 text-[var(--text-muted)] hover:text-rose-600 dark:hover:text-rose-400">
                                        <x-icon name="x" class="w-3.5 h-3.5" />
                                        <span>Dismiss</span>
                                    </button>
                                @elseif ($tip->isDismissed())
                                    <button type="button"
                                            wire:click="restoreTip({{ $tip->id }})"
                                            class="btn-secondary py-1.5 px-3 text-xs inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="rotate-ccw" class="w-3.5 h-3.5" />
                                        <span>Restore to Active</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
