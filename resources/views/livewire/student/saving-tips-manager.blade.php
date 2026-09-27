<div>
    <x-slot:header>
        Saving Tips
    </x-slot:header>

    <div class="space-y-6">
        {{-- ===================================================== --}}
        {{-- HEADER BAR: TITLE & RE-EVALUATE ACTION                  --}}
        {{-- ===================================================== --}}
        <div class="page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-xs font-caps text-[var(--muted)]">
                    Deterministic Financial Intelligence &bull; Active Analysis
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-medium text-[var(--ink)] mt-1 headline-rule">
                    Saving Opportunities
                </h1>
                <p class="text-xs text-[var(--muted)] mt-1.5">
                    Saving tips based on your transactions, category budgets, and savings target.
                </p>
            </div>

            <x-button variant="accent" wire:click="refreshTips">
                <x-icon name="refresh-cw" class="w-3.5 h-3.5" wire:loading.class="animate-spin" wire:target="refreshTips" />
                <span>Re-evaluate Ledger</span>
            </x-button>
        </div>

        {{-- ===================================================== --}}
        {{-- FLASH FEEDBACK ALERT                                   --}}
        {{-- ===================================================== --}}
        @if ($feedbackMessage)
            <div role="status" aria-live="polite" class="p-4 border border-[var(--accent)] bg-[var(--paper)] text-xs text-[var(--accent)] flex items-center justify-between">
                <div class="flex items-center gap-2 font-medium">
                    <x-icon name="check-circle" class="w-4 h-4 flex-shrink-0" />
                    <span>{{ $feedbackMessage }}</span>
                </div>
                <button type="button" wire:click="$set('feedbackMessage', null)" aria-label="Dismiss feedback message" class="btn-icon w-6 h-6 border-none">&times;</button>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- KPI STRIP: TOTAL POTENTIAL SAVINGS & METRICS           --}}
        {{-- ===================================================== --}}
        <div class="stat-strip">
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Identified Potential Savings</div>
                <div class="font-mono text-2xl font-medium text-[var(--accent)] tabular-nums mt-1">
                    ${{ number_format($totalPotentialSavings, 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    Estimated monthly reduction if active tips are applied
                </div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Active Opportunities</div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums mt-1">
                    {{ $activeCount }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    Immediate ledger-derived spending optimizations
                </div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Pinned Strategies</div>
                <div class="font-mono text-2xl font-medium text-[var(--secondary)] tabular-nums mt-1">
                    {{ $pinnedCount }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    Bookmarked rules saved for student review
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- SEGMENTED TAB CONTROLS                                 --}}
        {{-- ===================================================== --}}
        <div class="flex items-center justify-between border-b hairline-border pb-3">
            <div role="tablist" aria-label="Saving tip status filters" class="segmented-bar">
                <button type="button"
                        role="tab"
                        aria-selected="{{ $activeTab === 'active' ? 'true' : 'false' }}"
                        wire:click="setTab('active')"
                        class="segmented-item flex items-center gap-2 {{ $activeTab === 'active' ? 'active' : '' }}">
                    <span>Active Opportunities</span>
                    <span class="px-1.5 text-[10px] border hairline-border {{ $activeTab === 'active' ? 'bg-[var(--paper)] text-[var(--ink)]' : 'bg-[var(--paper)] text-[var(--muted)]' }}">
                        {{ $activeCount }}
                    </span>
                </button>

                <button type="button"
                        role="tab"
                        aria-selected="{{ $activeTab === 'pinned' ? 'true' : 'false' }}"
                        wire:click="setTab('pinned')"
                        class="segmented-item flex items-center gap-2 {{ $activeTab === 'pinned' ? 'active' : '' }}">
                    <x-icon name="bookmark" class="w-3.5 h-3.5 text-[var(--secondary)]" />
                    <span>Pinned</span>
                    <span class="px-1.5 text-[10px] border hairline-border {{ $activeTab === 'pinned' ? 'bg-[var(--paper)] text-[var(--ink)]' : 'bg-[var(--paper)] text-[var(--muted)]' }}">
                        {{ $pinnedCount }}
                    </span>
                </button>

                <button type="button"
                        role="tab"
                        aria-selected="{{ $activeTab === 'dismissed' ? 'true' : 'false' }}"
                        wire:click="setTab('dismissed')"
                        class="segmented-item flex items-center gap-2 {{ $activeTab === 'dismissed' ? 'active' : '' }}">
                    <span>Dismissed</span>
                    <span class="px-1.5 text-[10px] border hairline-border {{ $activeTab === 'dismissed' ? 'bg-[var(--paper)] text-[var(--ink)]' : 'bg-[var(--paper)] text-[var(--muted)]' }}">
                        {{ $dismissedCount }}
                    </span>
                </button>
            </div>

            <div class="hidden sm:flex items-center gap-1.5 text-xs font-mono text-[var(--muted)]">
                <span>Ranked by estimated savings impact</span>
                <x-icon name="arrow-down" class="w-3.5 h-3.5" />
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- TIPS LISTING                                           --}}
        {{-- ===================================================== --}}
        @if ($tips->isEmpty())
            <div class="empty-state space-y-3">
                @if ($activeTab === 'pinned')
                    <x-icon name="bookmark" class="w-6 h-6 mx-auto text-[var(--muted)]" />
                @elseif ($activeTab === 'dismissed')
                    <x-icon name="archive" class="w-6 h-6 mx-auto text-[var(--muted)]" />
                @else
                    <x-icon name="check-circle" class="w-6 h-6 mx-auto text-[var(--accent)]" />
                @endif

                <div class="font-display font-medium text-base text-[var(--ink)]">
                    @if ($activeTab === 'pinned')
                        No Pinned Strategies
                    @elseif ($activeTab === 'dismissed')
                        No Dismissed Opportunities
                    @else
                        No Active Budget Deviations Detected
                    @endif
                </div>

                <p class="text-xs text-[var(--muted)] max-w-md mx-auto">
                    @if ($activeTab === 'pinned')
                        You haven't bookmarked any tips yet. Pin critical tips from the Active Opportunities tab to monitor them continuously.
                    @elseif ($activeTab === 'dismissed')
                        You have not dismissed any tips. When you dismiss tips you don't wish to track, they are archived here for optional restoration.
                    @else
                        Your current spending is within its historical averages and category budgets. Keep logging transactions to update these checks.
                    @endif
                </p>

                @if ($activeTab === 'active')
                    <div class="pt-2">
                        <x-button variant="accent" href="{{ route('transactions') }}">
                            <x-icon name="plus" class="w-3.5 h-3.5" />
                            <span>Log Transaction</span>
                        </x-button>
                    </div>
                @endif
            </div>
        @else
            <div class="space-y-4">
                @foreach ($tips as $tip)
                    <div class="card-campus p-5 space-y-4" wire:key="tip-card-{{ $tip->id }}">
                        {{-- Top Metadata Strip --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex items-center gap-2.5 flex-wrap">
                                {{-- Category Badge or Ledger Badge --}}
                                @if ($tip->category)
                                    <span class="badge text-white text-xs"
                                        style="background-color: {{ $tip->category->color ?? 'var(--accent)' }}; border-color: {{ $tip->category->color ?? 'var(--accent)' }};">
                                        <x-icon :name="$tip->category->icon ?? 'tag'" class="w-3.5 h-3.5" />
                                        <span>{{ $tip->category->name }}</span>
                                    </span>
                                @else
                                    <span class="badge">
                                        <x-icon name="activity" class="w-3.5 h-3.5 text-[var(--accent)]" />
                                        <span>Overall Ledger</span>
                                    </span>
                                @endif

                                {{-- Rule Key Badge --}}
                                <span class="badge font-mono uppercase tracking-wider">
                                    {{ str_replace('_', ' ', $tip->rule_key) }}
                                </span>

                                @if ($tip->isPinned())
                                    <span class="badge badge-secondary flex items-center gap-1">
                                        <x-icon name="bookmark" class="w-3 h-3" />
                                        <span>Pinned</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Potential Savings --}}
                            <div class="flex items-center gap-2">
                                <div class="border border-[var(--accent)] bg-[var(--paper)] px-3 py-1.5 flex items-center gap-2">
                                    <span class="text-[10px] font-caps text-[var(--muted)]">Est. Potential Savings:</span>
                                    <span class="font-mono font-medium text-sm text-[var(--accent)] tabular-nums">
                                        {{ $tip->formattedEstimatedSavings() }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Title & Trigger Explanation --}}
                        <div class="space-y-1.5">
                            <h2 class="font-display font-medium text-base text-[var(--ink)]">
                                {{ $tip->title }}
                            </h2>
                            <p class="text-xs text-[var(--muted)] leading-relaxed">
                                {{ $tip->message }}
                            </p>
                        </div>

                        {{-- Actionable Suggestion Box --}}
                        <div class="p-3.5 border hairline-border bg-[var(--paper)] space-y-1">
                            <div class="flex items-center gap-1.5 text-[11px] font-caps text-[var(--accent)]">
                                <x-icon name="compass" class="w-3.5 h-3.5" />
                                <span>Suggested Next Step</span>
                            </div>
                            <p class="text-xs font-medium text-[var(--ink)] leading-normal">
                                {{ $tip->suggestion }}
                            </p>
                        </div>

                        {{-- Action Buttons Footer --}}
                        <div class="flex items-center justify-between pt-2.5 border-t hairline-border">
                            <div class="text-[10px] font-mono text-[var(--muted)]">
                                Evaluated: {{ $tip->updated_at->diffForHumans() }}
                                @if ($tip->pinned_at)
                                    &bull; Pinned {{ $tip->pinned_at->format('M d') }}
                                @elseif ($tip->dismissed_at)
                                    &bull; Dismissed {{ $tip->dismissed_at->format('M d') }}
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($tip->isActive())
                                    <x-button variant="secondary" wire:click="pinTip({{ $tip->id }})" aria-label="Pin tip: {{ $tip->title }}">
                                        <x-icon name="bookmark" class="w-3.5 h-3.5" />
                                        <span>Pin Tip</span>
                                    </x-button>
                                    <x-button variant="secondary" wire:click="dismissTip({{ $tip->id }})" aria-label="Dismiss tip: {{ $tip->title }}">
                                        <x-icon name="x" class="w-3.5 h-3.5" />
                                        <span>Dismiss</span>
                                    </x-button>
                                @elseif ($tip->isPinned())
                                    <x-button variant="secondary" wire:click="unpinTip({{ $tip->id }})" aria-label="Unpin tip: {{ $tip->title }}">
                                        <x-icon name="bookmark-minus" class="w-3.5 h-3.5" />
                                        <span>Unpin</span>
                                    </x-button>
                                    <x-button variant="secondary" wire:click="dismissTip({{ $tip->id }})" aria-label="Dismiss tip: {{ $tip->title }}">
                                        <x-icon name="x" class="w-3.5 h-3.5" />
                                        <span>Dismiss</span>
                                    </x-button>
                                @elseif ($tip->isDismissed())
                                    <x-button variant="secondary" wire:click="restoreTip({{ $tip->id }})" aria-label="Restore tip to active: {{ $tip->title }}">
                                        <x-icon name="rotate-ccw" class="w-3.5 h-3.5" />
                                        <span>Restore to Active</span>
                                    </x-button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
