<div>
    <x-slot:header>
        Dashboard
    </x-slot:header>

    <div class="space-y-8">
        {{-- ===================================================== --}}
        {{-- HERO BALANCE PANEL (§5: Shadow allowed on Hero)        --}}
        {{-- ===================================================== --}}
        <div class="surface-hero p-8 sm:p-10 border hairline-border bg-[var(--panel)]">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-caps text-[var(--muted)]">
                            {{ $user->academic_year ?? 'Student' }} Ledger &bull; {{ $currentMonth }}
                        </span>
                    </div>

                    <div>
                        <div class="text-xs font-caps text-[var(--muted)] mb-1">Available Safe to Spend</div>
                        {{-- Hero balance figure with count-up animation (§2, §7) --}}
                        <div x-data="{
                            current: 0,
                            target: {{ (float) $safeToSpend }},
                            duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 800,
                            init() {
                                if (this.duration === 0) {
                                    this.current = this.target.toFixed(2);
                                    return;
                                }
                                let startTime = null;
                                const step = (timestamp) => {
                                    if (!startTime) startTime = timestamp;
                                    const progress = Math.min((timestamp - startTime) / this.duration, 1);
                                    this.current = (progress * this.target).toFixed(2);
                                    if (progress < 1) {
                                        window.requestAnimationFrame(step);
                                    } else {
                                        this.current = this.target.toFixed(2);
                                    }
                                };
                                window.requestAnimationFrame(step);
                            }
                        }">
                            <div class="font-display text-4xl sm:text-5xl lg:text-6xl font-normal text-[var(--ink)] tabular-nums tracking-tight">
                                $<span x-text="Number(current).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })">{{ number_format($safeToSpend, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-[var(--muted)]">
                        Monthly baseline: <span class="font-mono text-[var(--ink)] font-medium">${{ number_format($allowance, 2) }}</span> &bull;
                        Savings target: <span class="font-mono text-[var(--secondary)] font-medium">${{ number_format($savingsGoal, 2) }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <x-button variant="secondary" href="{{ route('budgets') }}">
                        <x-icon name="target" class="w-4 h-4" />
                        <span>Budgets</span>
                    </x-button>
                    <x-button variant="secondary" href="{{ route('categories') }}">
                        <x-icon name="tag" class="w-4 h-4" />
                        <span>Categories</span>
                    </x-button>
                    <livewire:student.transaction-list :quick-add-only="true" />
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- IN-APP BUDGET ALERT BANNER (REAL-TIME NOTIFICATION)    --}}
        {{-- ===================================================== --}}
        @if ($overBudgets->isNotEmpty())
            <div role="alert" aria-live="assertive" class="p-4 border border-[var(--expense)] bg-[var(--paper)] text-[var(--ink)] flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <x-icon name="shield-alert" class="w-5 h-5 text-[var(--expense)] flex-shrink-0 mt-0.5" />
                    <div class="text-xs space-y-0.5">
                        <div class="font-medium text-[var(--expense)]">
                            Budget Alert: {{ $overBudgets->pluck('category.name')->join(', ') }} {{ $overBudgets->count() === 1 ? 'has' : 'have' }} exceeded monthly limit
                        </div>
                        <div class="text-[var(--muted)]">
                            Immediate attention required: reduce discretionary spending or adjust your category caps to preserve savings.
                        </div>
                    </div>
                </div>
                <a href="{{ route('budgets') }}" class="text-xs font-medium text-[var(--expense)] hover:underline flex-shrink-0">
                    Manage Budgets &rarr;
                </a>
            </div>
        @elseif ($nearLimitBudgets->isNotEmpty())
            <div role="status" aria-live="polite" class="p-4 border border-[var(--secondary)] bg-[var(--paper)] text-[var(--ink)] flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <x-icon name="target" class="w-5 h-5 text-[var(--secondary)] flex-shrink-0 mt-0.5" />
                    <div class="text-xs space-y-0.5">
                        <div class="font-medium text-[var(--secondary)]">
                            Budget Notice: {{ $nearLimitBudgets->pluck('category.name')->join(', ') }} nearing monthly limit (&ge;75%)
                        </div>
                        <div class="text-[var(--muted)]">
                            Spending in these categories has consumed over 75% of your planned monthly budget.
                        </div>
                    </div>
                </div>
                <a href="{{ route('budgets') }}" class="text-xs font-medium text-[var(--secondary)] hover:underline flex-shrink-0">
                    View Budgets &rarr;
                </a>
            </div>
        @endif

        @if ($systemTemplates->isNotEmpty())
            <section class="border hairline-border bg-[var(--panel)]">
                <div class="p-4 border-b hairline-border">
                    <h2 class="font-display text-base font-medium text-[var(--ink)]">Campus Updates</h2>
                </div>
                <div class="divide-y divide-[var(--hairline)]">
                    @foreach ($systemTemplates as $template)
                        <article class="p-4 border-l-2 {{ $template->type === 'announcement' ? 'border-[var(--secondary)]' : 'border-[var(--accent)]' }}">
                            <div class="text-[10px] font-caps text-[var(--muted)]">{{ ucfirst($template->type) }}</div>
                            <h3 class="text-sm font-medium text-[var(--ink)] mt-1">{{ $template->title }}</h3>
                            <p class="text-xs text-[var(--muted)] mt-1">{{ $template->message }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===================================================== --}}
        {{-- STAT STRIP (§12: Hairline-divided stat row, NO nested card borders) --}}
        {{-- ===================================================== --}}
        <div class="stat-strip">
            {{-- Monthly Income --}}
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">This Month Income</div>
                <div class="mt-2 text-2xl font-mono font-medium text-[var(--accent)] tabular-nums">
                    +${{ number_format($monthlyIncome, 2) }}
                </div>
                <div class="mt-1 text-xs text-[var(--muted)]">
                    {{ $totalCount > 0 ? 'Verified from ledger' : 'No entries this month' }}
                </div>
            </div>

            {{-- Monthly Spent --}}
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">This Month Spent</div>
                <div class="mt-2 text-2xl font-mono font-medium text-[var(--expense)] tabular-nums">
                    -${{ number_format($monthlyExpense, 2) }}
                </div>
                <div class="mt-1 text-xs text-[var(--muted)]">
                    @if ($expenseDelta !== null)
                        @if ($expenseDelta > 0)
                            <span class="text-[var(--expense)]">+{{ $expenseDelta }}% vs last month</span>
                        @elseif ($expenseDelta < 0)
                            <span class="text-[var(--accent)]">{{ $expenseDelta }}% vs last month</span>
                        @else
                            Same as last month
                        @endif
                    @else
                        First recorded month
                    @endif
                </div>
            </div>

            {{-- Safe to Spend --}}
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Budget Allocation</div>
                <div class="mt-2 text-2xl font-mono font-medium text-[var(--ink)] tabular-nums">
                    ${{ number_format((float) $totalBudgeted, 2) }}
                </div>
                <div class="mt-1 text-xs text-[var(--muted)]">
                    Active category caps
                </div>
            </div>

            {{-- Savings Goal Progress --}}
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Savings Goal ({{ $savingsProgress }}%)</div>
                <div class="mt-2 text-2xl font-mono font-medium text-[var(--secondary)] tabular-nums">
                    ${{ number_format($savedAmount, 2) }}
                </div>
                <div class="mt-1 text-xs text-[var(--muted)]">
                    Target: ${{ number_format($savingsGoal, 2) }}
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- BUDGET GOALS & CONSUMPTION                             --}}
        {{-- ===================================================== --}}
        <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
            <div class="section-header flex items-baseline justify-between">
                <div>
                    <h2 class="font-display text-lg font-medium text-[var(--ink)] headline-rule">
                        Budget Goals & Spending Caps
                    </h2>
                    <p class="text-xs text-[var(--muted)] mt-1">
                        {{ $currentMonth }} consumption vs. established monthly caps
                    </p>
                </div>
                <a href="{{ route('budgets') }}" class="text-xs font-sans text-[var(--accent)] hover:underline">
                    Manage All Goals &rarr;
                </a>
            </div>

            @if ($decoratedBudgets->isEmpty())
                <x-empty-state message="No budget goals established for {{ $currentMonth }}. Set category caps to track monthly consumption." actionText="Set Category Budget" actionHref="{{ route('budgets') }}" />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($decoratedBudgets as $b)
                        @php
                            $cat = $b['category'];
                            $pct = $b['pct'];
                            $isOver = $b['status'] === 'over_budget';
                        @endphp
                        <div class="p-4 border hairline-border bg-[var(--paper)] space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-sans font-medium text-xs text-[var(--ink)]">
                                    {{ $cat?->name ?? 'Uncategorized' }}
                                </span>
                                <x-badge :variant="$isOver ? 'expense' : ($pct >= 75 ? 'secondary' : 'default')">
                                    {{ $b['statusLabel'] }}
                                </x-badge>
                            </div>

                            <div class="space-y-1.5">
                                <div class="flex items-baseline justify-between text-xs font-mono tabular-nums">
                                    <span class="text-[var(--ink)] font-medium">
                                        ${{ number_format((float) $b['spent'], 2) }}
                                        <span class="text-[var(--muted)] text-[11px]">/ ${{ number_format((float) $b['limit'], 2) }}</span>
                                    </span>
                                    <span class="font-mono text-xs {{ $isOver ? 'text-[var(--expense)]' : 'text-[var(--ink)]' }}">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <div class="w-full h-1 bg-[var(--hairline)] overflow-hidden">
                                    <div class="h-full {{ $isOver ? 'bg-[var(--expense)]' : ($pct >= 75 ? 'bg-[var(--secondary)]' : 'bg-[var(--accent)]') }}"
                                         style="width: {{ min(100, $pct) }}%;">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] font-mono text-[var(--muted)]">
                                    <span>{{ $isOver ? 'Over cap:' : 'Remaining:' }}</span>
                                    <span class="tabular-nums {{ $isOver ? 'text-[var(--expense)]' : 'text-[var(--accent)]' }}">
                                        {{ $isOver ? '-' : '' }}${{ number_format(abs((float) $b['remaining']), 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- INTELLIGENT SAVING OPPORTUNITIES WIDGET               --}}
        {{-- ===================================================== --}}
        <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
            <div class="section-header flex items-baseline justify-between">
                <div>
                    <h2 class="font-display text-lg font-medium text-[var(--ink)] headline-rule">
                        Personalized Saving Opportunities
                    </h2>
                    <p class="text-xs text-[var(--muted)] mt-1">
                        Rule-based intelligence evaluated against your recent transactions
                    </p>
                </div>
                <a href="{{ route('student.tips') }}" class="text-xs font-sans text-[var(--accent)] hover:underline">
                    View All Tips &rarr;
                </a>
            </div>

            @if ($topSavingTips->isEmpty())
                <x-empty-state message="No spending spikes or budget deviations detected. All expense categories are operating within historical baselines." actionText="Open Saving Tips Center" actionHref="{{ route('student.tips') }}" />
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($topSavingTips as $tip)
                        <div class="p-4 border hairline-border bg-[var(--paper)] space-y-3 flex flex-col justify-between" wire:key="dashboard-tip-{{ $tip->id }}">
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between gap-2">
                                    @if ($tip->category)
                                        <div class="flex items-center gap-1.5 min-w-0">
                                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $tip->category->color ?? '#234F3B' }};"></span>
                                            <span class="text-xs font-medium text-[var(--ink)] truncate">
                                                {{ $tip->category->name }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1.5 text-xs font-mono text-[var(--accent)]">
                                            <x-icon name="activity" class="w-3.5 h-3.5" />
                                            <span>Overall Ledger</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if ($tip->isPinned())
                                            <span class="text-[var(--secondary)]" title="Pinned">
                                                <x-icon name="bookmark" class="w-3.5 h-3.5" />
                                            </span>
                                        @endif
                                        <span class="border border-[var(--accent)] px-2 py-0.5 text-[10px] font-mono font-medium text-[var(--accent)] tabular-nums">
                                            Est. {{ $tip->formattedEstimatedSavings() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <h3 class="font-display font-medium text-xs text-[var(--ink)] line-clamp-1">
                                        {{ $tip->title }}
                                    </h3>
                                    <p class="text-[11px] text-[var(--muted)] leading-relaxed line-clamp-2">
                                        {{ $tip->message }}
                                    </p>
                                </div>

                                <div class="p-2.5 border hairline-border bg-[var(--panel)] text-[11px] text-[var(--ink)] font-sans leading-normal">
                                    {{ $tip->suggestion }}
                                </div>
                            </div>

                            <div class="pt-2.5 border-t hairline-border flex items-center justify-between text-[11px]">
                                <span class="text-[10px] font-caps text-[var(--muted)]">
                                    {{ str_replace('_', ' ', $tip->rule_key) }}
                                </span>

                                <div class="flex items-center gap-1.5">
                                    @if ($tip->isPinned())
                                        <button type="button"
                                                wire:click="unpinTip({{ $tip->id }})"
                                                class="btn-icon w-7 h-7 text-[var(--secondary)]"
                                                title="Unpin tip">
                                            <x-icon name="bookmark-minus" class="w-3.5 h-3.5" />
                                        </button>
                                    @else
                                        <button type="button"
                                                wire:click="pinTip({{ $tip->id }})"
                                                class="btn-icon w-7 h-7 text-[var(--muted)] hover:text-[var(--secondary)]"
                                                title="Pin tip">
                                            <x-icon name="bookmark" class="w-3.5 h-3.5" />
                                        </button>
                                    @endif

                                    <button type="button"
                                            wire:click="dismissTip({{ $tip->id }})"
                                            class="btn-icon w-7 h-7 text-[var(--muted)] hover:text-[var(--expense)]"
                                            title="Dismiss tip">
                                        <x-icon name="x" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- SIX-MONTH CASH FLOW TRENDS (SRS §4.4, §4.6)            --}}
        {{-- ===================================================== --}}
        <div class="bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
            <div class="section-header flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-3">
                <div>
                    <h2 class="font-display text-lg font-medium text-[var(--ink)] headline-rule">
                        Historical Cash Flow (Income vs. Expense)
                    </h2>
                    <p class="text-xs text-[var(--muted)] mt-1">
                        6-Month Velocity &bull; Volume comparison across income and expense velocities
                    </p>
                </div>
                <div class="flex items-center gap-4 text-xs font-mono text-[var(--muted)]">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[var(--accent)] inline-block"></span>
                        <span>Income</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-[var(--expense)] inline-block"></span>
                        <span>Expense</span>
                    </div>
                </div>
            </div>

            {{-- Responsive Native SVG Chart with palette tokens (§8 flat fills, no gradients) --}}
            <div class="w-full overflow-x-auto">
                <div class="min-w-[580px]">
                    <svg viewBox="0 0 660 190" class="w-full h-44 sm:h-52 select-none" aria-label="6-Month Cash Flow Bar Chart">
                        {{-- Horizontal Grid Lines (§8: solid hairline weight, dashed only when meaningful) --}}
                        <line x1="55" y1="35" x2="640" y2="35" stroke="var(--hairline)" stroke-width="1" />
                        <line x1="55" y1="75" x2="640" y2="75" stroke="var(--hairline)" stroke-width="1" />
                        <line x1="55" y1="115" x2="640" y2="115" stroke="var(--hairline)" stroke-width="1" />
                        <line x1="55" y1="155" x2="640" y2="155" stroke="var(--hairline)" stroke-width="1" />

                        {{-- Y-Axis Labels --}}
                        <text x="50" y="38" text-anchor="end" class="font-mono text-[9px] fill-[var(--muted)]">${{ number_format($sixMonthTrends['max_volume'], 0) }}</text>
                        <text x="50" y="78" text-anchor="end" class="font-mono text-[9px] fill-[var(--muted)]">${{ number_format($sixMonthTrends['max_volume'] * 0.66, 0) }}</text>
                        <text x="50" y="118" text-anchor="end" class="font-mono text-[9px] fill-[var(--muted)]">${{ number_format($sixMonthTrends['max_volume'] * 0.33, 0) }}</text>
                        <text x="50" y="158" text-anchor="end" class="font-mono text-[9px] fill-[var(--muted)]">$0</text>

                        {{-- 6 Month Bars --}}
                        @php
                            $maxVol = max(1.0, (float) $sixMonthTrends['max_volume']);
                            $slotWidth = (640 - 60) / 6;
                        @endphp

                        @foreach ($sixMonthTrends['months'] as $idx => $m)
                            @php
                                $cx = 60 + ($idx * $slotWidth) + ($slotWidth / 2);
                                $incVal = (float) $m['income'];
                                $expVal = (float) $m['expense'];
                                $incH = $incVal > 0 ? max(3, round(($incVal / $maxVol) * 120)) : 0;
                                $expH = $expVal > 0 ? max(3, round(($expVal / $maxVol) * 120)) : 0;
                                $incY = 155 - $incH;
                                $expY = 155 - $expH;
                            @endphp

                            {{-- Month background column highlight on current --}}
                            @if ($m['is_current'])
                                <rect x="{{ $cx - 36 }}" y="25" width="72" height="130" fill="var(--hairline)" opacity="0.25" />
                            @endif

                            {{-- Income Bar (§8: Flat fill in accent) --}}
                            @if ($incH > 0)
                                <rect x="{{ $cx - 18 }}" y="{{ $incY }}" width="15" height="{{ $incH }}"
                                      fill="var(--accent)" />
                            @endif

                            {{-- Expense Bar (§8: Flat fill in expense) --}}
                            @if ($expH > 0)
                                <rect x="{{ $cx + 3 }}" y="{{ $expY }}" width="15" height="{{ $expH }}"
                                      fill="var(--expense)" />
                            @endif

                            {{-- Month Label --}}
                            <text x="{{ $cx }}" y="174" text-anchor="middle"
                                  class="font-mono text-[11px] {{ $m['is_current'] ? 'font-medium fill-[var(--accent)]' : 'fill-[var(--muted)]' }}">
                                {{ $m['short_label'] }}
                            </text>
                        @endforeach
                    </svg>
                </div>
            </div>

            {{-- 6-Month Detailed Strip (Hairline-divided, zero card nesting) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-4">
                @foreach ($sixMonthTrends['months'] as $m)
                    <div class="p-3 space-y-1 bg-[var(--paper)]">
                        <div class="font-mono text-[11px] font-medium text-[var(--ink)]">
                            {{ $m['month_label'] }}
                        </div>
                        <div class="text-[10px] font-mono tabular-nums text-[var(--muted)]">
                            <div>In: <span class="text-[var(--accent)]">+${{ number_format((float) $m['income'], 2) }}</span></div>
                            <div>Out: <span class="text-[var(--expense)]">-${{ number_format((float) $m['expense'], 2) }}</span></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- COMPARATIVE CATEGORY SPENDING + RECENT TRANSACTIONS   --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Category Spending (7-col) --}}
            <div class="lg:col-span-7 bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
                <div class="section-header flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-medium text-[var(--ink)] headline-rule">
                            Category Spending Trends
                        </h2>
                        <p class="text-xs text-[var(--muted)] mt-1">Comparative Analysis &bull; Distribution across active periods</p>
                    </div>

                    {{-- Period Switcher --}}
                    <div role="group" aria-label="Comparative period filter" class="segmented-bar flex-shrink-0">
                        <button type="button"
                                wire:click="setTimePeriod('this_month')"
                                class="segmented-item {{ $timePeriod === 'this_month' ? 'active' : '' }}">
                            This Month
                        </button>
                        <button type="button"
                                wire:click="setTimePeriod('last_3_months')"
                                class="segmented-item {{ $timePeriod === 'last_3_months' ? 'active' : '' }}">
                            3 Months
                        </button>
                        <button type="button"
                                wire:click="setTimePeriod('last_6_months')"
                                class="segmented-item {{ $timePeriod === 'last_6_months' ? 'active' : '' }}">
                            6 Months
                        </button>
                        <button type="button"
                                wire:click="setTimePeriod('year')"
                                class="segmented-item {{ $timePeriod === 'year' ? 'active' : '' }}">
                            Year
                        </button>
                    </div>
                </div>

                @if (empty($categoryComparisons['categories']))
                    <x-empty-state message="No expense transactions logged for {{ $categoryComparisons['period_label'] }}." actionText="Add Transaction" actionHref="{{ route('transactions') }}" />
                @else
                    <div class="divide-y divide-[var(--hairline)]">
                        @foreach ($categoryComparisons['categories'] as $c)
                            @php
                                $dir = $c['direction'];
                                $isInc = $dir === 'increased';
                            @endphp
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-medium text-[var(--ink)]">{{ $c['name'] }}</span>
                                        <span class="font-mono tabular-nums text-[var(--ink)]">${{ number_format((float) $c['current_spent'], 2) }}</span>
                                    </div>
                                    <div class="w-full h-1 bg-[var(--hairline)] overflow-hidden">
                                        <div class="h-full bg-[var(--secondary)]" style="width: {{ $c['share_pct'] }}%;"></div>
                                    </div>
                                </div>
                                <span class="text-[11px] font-mono tabular-nums text-[var(--muted)] flex-shrink-0 w-16 text-right">
                                    {{ $c['share_pct'] }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Transactions (5-col) --}}
            <div class="lg:col-span-5 bg-[var(--panel)] border hairline-border p-6 sm:p-8 space-y-6">
                <div class="section-header flex items-baseline justify-between">
                    <div>
                        <h2 class="font-display text-lg font-medium text-[var(--ink)] headline-rule">
                            Recent Entries
                        </h2>
                        <p class="text-xs text-[var(--muted)] mt-1">Live recorded cash transactions</p>
                    </div>
                    <a href="{{ route('transactions') }}" class="text-xs font-sans text-[var(--accent)] hover:underline">
                        All &rarr;
                    </a>
                </div>

                @if ($recentTransactions->isEmpty())
                    <x-empty-state message="No transactions recorded yet." actionText="Add First Entry" actionHref="{{ route('transactions') }}" />
                @else
                    <div class="divide-y divide-[var(--hairline)]">
                        @foreach ($recentTransactions as $t)
                            <div class="py-3 flex items-center justify-between gap-3 table-row-tactile">
                                <div class="min-w-0">
                                    <div class="font-medium text-xs text-[var(--ink)] truncate">
                                        {{ $t->merchant }}
                                    </div>
                                    <div class="text-[11px] font-mono text-[var(--muted)]">
                                        {{ $t->transaction_date->format('M d') }} &bull; {{ $t->category?->name ?? 'General' }}
                                    </div>
                                </div>
                                <div class="font-mono text-xs tabular-nums font-medium flex-shrink-0 {{ $t->isIncome() ? 'text-[var(--accent)]' : 'text-[var(--ink)]' }}">
                                    {{ $t->formattedAmount() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- ALL-TIME LEDGER FOOTER (Single hairline divided row)   --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-center">
            <div class="p-4 bg-[var(--panel)]">
                <div class="text-xs font-caps text-[var(--muted)]">All-Time Inflow</div>
                <div class="mt-1 font-mono text-base font-medium text-[var(--accent)] tabular-nums">
                    ${{ number_format($allTimeIncome, 2) }}
                </div>
            </div>
            <div class="p-4 bg-[var(--panel)]">
                <div class="text-xs font-caps text-[var(--muted)]">All-Time Outflow</div>
                <div class="mt-1 font-mono text-base font-medium text-[var(--expense)] tabular-nums">
                    ${{ number_format($allTimeExpense, 2) }}
                </div>
            </div>
            <div class="p-4 bg-[var(--panel)]">
                <div class="text-xs font-caps text-[var(--muted)]">Total Ledger Entries</div>
                <div class="mt-1 font-mono text-base font-medium text-[var(--ink)] tabular-nums">
                    {{ number_format($totalCount) }}
                </div>
            </div>
        </div>
    </div>
</div>
