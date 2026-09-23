<div>
    <x-slot:header>
        Dashboard
    </x-slot:header>

    <div class="space-y-6">
        {{-- ===================================================== --}}
        {{-- GREETING + MONTH SUMMARY BANNER                        --}}
        {{-- ===================================================== --}}
        <div class="p-6 rounded-[8px] border hairline-border bg-[var(--bg-surface)] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="text-xs font-mono text-[var(--accent-primary)] font-semibold uppercase tracking-wider mb-1">
                    {{ $user->academic_year ?? 'Student' }} Cohort &bull; {{ $currentMonth }}
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">
                    Welcome back, {{ $user->name }}
                </h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">
                    Your financial baseline: <span class="font-mono font-semibold text-[var(--text-primary)]">${{ number_format($allowance, 2) }}</span>/mo &bull;
                    Savings target: <span class="font-mono font-semibold text-[var(--gold)]">${{ number_format($savingsGoal, 2) }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <a href="{{ route('budgets') }}" class="btn-secondary py-2 px-3 text-xs">
                    <x-icon name="target" class="w-4 h-4" />
                    <span class="hidden sm:inline">Budgets</span>
                </a>
                <a href="{{ route('categories') }}" class="btn-secondary py-2 px-3 text-xs">
                    <x-icon name="tag" class="w-4 h-4" />
                    <span class="hidden sm:inline">Categories</span>
                </a>
                <a href="{{ route('transactions') }}" class="btn-primary py-2 px-4 text-xs">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Add Transaction</span>
                </a>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- IN-APP BUDGET ALERT BANNER (REAL-TIME NOTIFICATION)    --}}
        {{-- ===================================================== --}}
        @if ($overBudgets->isNotEmpty())
            <div class="p-4 rounded-[6px] border border-rose-300 bg-rose-50 dark:border-rose-900 dark:bg-rose-950/50 text-rose-900 dark:text-rose-200 flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <x-icon name="shield-alert" class="w-5 h-5 text-rose-600 dark:text-rose-400 flex-shrink-0 mt-0.5" />
                    <div class="text-xs space-y-0.5">
                        <div class="font-bold tracking-tight">
                            Budget Alert: {{ $overBudgets->pluck('category.name')->join(', ') }} {{ $overBudgets->count() === 1 ? 'has' : 'have' }} exceeded monthly limit
                        </div>
                        <div class="text-rose-700 dark:text-rose-300">
                            Immediate attention required: reduce discretionary spending or adjust your category caps to preserve savings.
                        </div>
                    </div>
                </div>
                <a href="{{ route('budgets') }}" class="text-xs font-semibold text-rose-700 hover:text-rose-900 dark:text-rose-300 underline flex-shrink-0">
                    Manage Budgets &rarr;
                </a>
            </div>
        @elseif ($nearLimitBudgets->isNotEmpty())
            <div class="p-4 rounded-[6px] border border-amber-300 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/50 text-amber-900 dark:text-amber-200 flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <x-icon name="target" class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                    <div class="text-xs space-y-0.5">
                        <div class="font-bold tracking-tight">
                            Budget Notice: {{ $nearLimitBudgets->pluck('category.name')->join(', ') }} nearing monthly limit (&ge;75%)
                        </div>
                        <div class="text-amber-700 dark:text-amber-300">
                            Spending in these categories has consumed over 75% of your planned monthly budget.
                        </div>
                    </div>
                </div>
                <a href="{{ route('budgets') }}" class="text-xs font-semibold text-amber-700 hover:text-amber-900 dark:text-amber-300 underline flex-shrink-0">
                    View Budgets &rarr;
                </a>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- FOUR KPI CARDS (live data)                             --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Monthly Income --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>This Month Income</span>
                    <x-icon name="trending-up" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                    ${{ number_format($monthlyIncome, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    {{ $totalCount > 0 ? 'Verified from ledger' : 'No entries this month yet' }}
                </div>
            </div>

            {{-- Monthly Expense --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>This Month Spent</span>
                    <x-icon name="trending-down" class="w-4 h-4 text-[var(--danger)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    ${{ number_format($monthlyExpense, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    @if ($expenseDelta !== null)
                        @if ($expenseDelta > 0)
                            <span class="text-[var(--danger)]">+{{ $expenseDelta }}% vs last month</span>
                        @elseif ($expenseDelta < 0)
                            <span class="text-emerald-600">{{ $expenseDelta }}% vs last month</span>
                        @else
                            Same as last month
                        @endif
                    @else
                        First recorded month
                    @endif
                </div>
            </div>

            {{-- Safe to Spend --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Safe to Spend</span>
                    <x-icon name="wallet" class="w-4 h-4 text-[var(--accent-primary)]" />
                </div>
                <div class="font-mono text-2xl font-bold tabular-nums {{ $safeToSpend > 0 ? 'text-[var(--accent-primary)]' : 'text-[var(--danger)]' }}">
                    ${{ number_format($safeToSpend, 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Allowance minus expenses
                    @if ((float) $totalBudgeted > 0)
                        &bull; <span class="font-mono font-semibold">${{ number_format((float) $totalBudgeted, 2) }}</span> cap
                    @endif
                </div>
            </div>

            {{-- Savings Progress --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Savings Goal</span>
                    <x-icon name="target" class="w-4 h-4 text-[var(--gold)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--gold)] tabular-nums">
                    {{ $savingsProgress }}%
                </div>
                <div class="w-full h-1.5 rounded-full bg-[var(--bg-subtle)] mt-1">
                    <div class="h-1.5 rounded-full bg-[var(--gold)] transition-all duration-500"
                         style="width: {{ $savingsProgress }}%"></div>
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    ${{ number_format($savedAmount, 2) }} of ${{ number_format($savingsGoal, 2) }}
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- BUDGET GOALS & CONSUMPTION WIDGET (SRS §4.4, §4.5)     --}}
        {{-- ===================================================== --}}
        <div class="card-campus border hairline-border p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">{{ $currentMonth }} &bull; Budget vs. Actual</div>
                    <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">Budget Goals & Spending Caps</h2>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('budgets') }}" class="text-xs font-medium text-[var(--accent-primary)] hover:underline flex items-center gap-1">
                        <span>Manage All Goals</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            @if ($decoratedBudgets->isEmpty())
                <div class="py-6 text-center space-y-2">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                        <x-icon name="target" class="w-5 h-5" />
                    </div>
                    <div class="text-xs font-semibold text-[var(--text-primary)]">No budget goals set for {{ $currentMonth }}</div>
                    <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
                        Set spending caps on your expense categories to track consumption in real time and protect your savings.
                    </p>
                    <a href="{{ route('budgets') }}" class="btn-primary py-1.5 px-3 text-xs inline-flex mt-1">
                        Set Category Budget
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($decoratedBudgets as $b)
                        @php
                            $cat = $b['category'];
                            $pct = $b['pct'];
                            $isOver = $b['status'] === 'over_budget';
                        @endphp
                        <div class="p-3.5 rounded-[6px] border hairline-border bg-[var(--bg-subtle)]/30 space-y-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-[4px] flex items-center justify-center text-white flex-shrink-0"
                                         style="background-color: {{ $cat?->color ?? '#64748B' }};">
                                        <x-icon :name="$cat?->icon ?? 'tag'" class="w-3 h-3" />
                                    </div>
                                    <span class="font-heading font-medium text-xs text-[var(--text-primary)] truncate">
                                        {{ $cat?->name ?? 'Uncategorized' }}
                                    </span>
                                </div>
                                <span class="badge-campus text-[9px] font-mono uppercase tracking-wider {{ $b['badgeClass'] }} flex-shrink-0">
                                    {{ $b['statusLabel'] }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-baseline justify-between text-[11px] font-mono tabular-nums">
                                    <span class="text-[var(--text-primary)] font-semibold">
                                        ${{ number_format((float) $b['spent'], 2) }}
                                        <span class="text-[var(--text-muted)] font-normal text-[10px]">/ ${{ number_format((float) $b['limit'], 2) }}</span>
                                    </span>
                                    <span class="font-bold" style="color: {{ $b['barColor'] }};">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <div class="w-full h-1.5 rounded-[3px] bg-[var(--bg-surface)] overflow-hidden border hairline-border">
                                    <div class="h-full rounded-[3px] transition-all duration-500"
                                         style="width: {{ min(100, $pct) }}%; background-color: {{ $b['barColor'] }};">
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-[10px] font-mono pt-0.5">
                                    <span class="text-[var(--text-muted)]">
                                        {{ $isOver ? 'Over cap:' : 'Remaining:' }}
                                    </span>
                                    @if ($isOver)
                                        <span class="font-bold text-rose-600 dark:text-rose-400">
                                            -${{ number_format(abs((float) $b['remaining']), 2) }}
                                        </span>
                                    @else
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400">
                                            ${{ number_format((float) $b['remaining'], 2) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===================================================== --}}
        {{-- ===================================================== --}}
        {{-- SIX-MONTH CASH FLOW TRENDS (SRS §4.4, §4.6)            --}}
        {{-- ===================================================== --}}
        <div class="card-campus border hairline-border p-5 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                        6-Month Velocity &bull; Cash Flow Dynamics
                    </div>
                    <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">
                        Historical Cash Flow (Income vs. Expense)
                    </h2>
                </div>
                {{-- Quick Summary Badges --}}
                <div class="flex flex-wrap items-center gap-2 text-xs font-mono">
                    <div class="px-2.5 py-1 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] flex items-center gap-1.5">
                        <span class="text-[10px] text-[var(--text-muted)] uppercase">Inflow:</span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-400 tabular-nums">
                            +${{ number_format((float) $sixMonthTrends['total_income'], 2) }}
                        </span>
                    </div>
                    <div class="px-2.5 py-1 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] flex items-center gap-1.5">
                        <span class="text-[10px] text-[var(--text-muted)] uppercase">Outflow:</span>
                        <span class="font-semibold text-[var(--text-primary)] tabular-nums">
                            -${{ number_format((float) $sixMonthTrends['total_expense'], 2) }}
                        </span>
                    </div>
                    <div class="px-2.5 py-1 rounded-[4px] border hairline-border bg-[var(--bg-subtle)] flex items-center gap-1.5">
                        <span class="text-[10px] text-[var(--text-muted)] uppercase">Net:</span>
                        <span class="font-bold tabular-nums {{ (float) $sixMonthTrends['total_net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ (float) $sixMonthTrends['total_net'] >= 0 ? '+' : '' }}${{ number_format((float) $sixMonthTrends['total_net'], 2) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Legend & Averages --}}
            <div class="flex items-center justify-between text-xs pt-1 border-t hairline-border">
                <div class="flex items-center gap-4 text-[11px] font-mono text-[var(--text-muted)]">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-[2px] bg-emerald-500 inline-block"></span>
                        <span>Income</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-[2px] bg-rose-500 inline-block"></span>
                        <span>Expense</span>
                    </div>
                </div>
                <div class="text-[11px] font-mono text-[var(--text-muted)]">
                    Avg Monthly Expense: <span class="font-semibold text-[var(--text-primary)]">${{ number_format((float) $sixMonthTrends['average_monthly_expense'], 2) }}</span>
                </div>
            </div>

            {{-- Responsive Native SVG Chart --}}
            <div class="w-full overflow-x-auto">
                <div class="min-w-[580px]">
                    <svg viewBox="0 0 660 190" class="w-full h-44 sm:h-52 select-none" aria-label="6-Month Cash Flow Bar Chart">
                        {{-- Horizontal Grid Lines --}}
                        <line x1="55" y1="35" x2="640" y2="35" stroke="currentColor" stroke-opacity="0.08" stroke-dasharray="3 3" />
                        <line x1="55" y1="75" x2="640" y2="75" stroke="currentColor" stroke-opacity="0.08" stroke-dasharray="3 3" />
                        <line x1="55" y1="115" x2="640" y2="115" stroke="currentColor" stroke-opacity="0.08" stroke-dasharray="3 3" />
                        <line x1="55" y1="155" x2="640" y2="155" stroke="currentColor" stroke-opacity="0.25" />

                        {{-- Y-Axis Labels --}}
                        <text x="50" y="38" text-anchor="end" class="font-mono text-[9px] fill-[var(--text-muted)]">${{ number_format($sixMonthTrends['max_volume'], 0) }}</text>
                        <text x="50" y="78" text-anchor="end" class="font-mono text-[9px] fill-[var(--text-muted)]">${{ number_format($sixMonthTrends['max_volume'] * 0.66, 0) }}</text>
                        <text x="50" y="118" text-anchor="end" class="font-mono text-[9px] fill-[var(--text-muted)]">${{ number_format($sixMonthTrends['max_volume'] * 0.33, 0) }}</text>
                        <text x="50" y="158" text-anchor="end" class="font-mono text-[9px] fill-[var(--text-muted)]">$0</text>

                        {{-- 6 Month Bars --}}
                        @php
                            $maxVol = max(1.0, (float) $sixMonthTrends['max_volume']);
                            $slotWidth = (640 - 60) / 6; // 96.67
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
                                <rect x="{{ $cx - 36 }}" y="25" width="72" height="130" fill="currentColor" fill-opacity="0.03" rx="4" />
                            @endif

                            {{-- Income Bar --}}
                            @if ($incH > 0)
                                <rect x="{{ $cx - 18 }}" y="{{ $incY }}" width="15" height="{{ $incH }}"
                                      fill="#10B981" rx="2" ry="2" opacity="0.9" />
                            @endif

                            {{-- Expense Bar --}}
                            @if ($expH > 0)
                                <rect x="{{ $cx + 3 }}" y="{{ $expY }}" width="15" height="{{ $expH }}"
                                      fill="#F43F5E" rx="2" ry="2" opacity="0.9" />
                            @endif

                            {{-- Month Label --}}
                            <text x="{{ $cx }}" y="174" text-anchor="middle"
                                  class="font-mono text-[11px] {{ $m['is_current'] ? 'font-bold fill-[var(--accent-primary)]' : 'fill-[var(--text-muted)]' }}">
                                {{ $m['short_label'] }}
                            </text>
                        @endforeach
                    </svg>
                </div>
            </div>

            {{-- 6-Month Detailed Month Cards Strip --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 pt-2 border-t hairline-border">
                @foreach ($sixMonthTrends['months'] as $m)
                    @php
                        $isPos = $m['status'] === 'positive';
                        $isNeg = $m['status'] === 'negative';
                    @endphp
                    <div class="p-2.5 rounded-[6px] border hairline-border {{ $m['is_current'] ? 'bg-[var(--accent-tint)]/30 border-[var(--accent-primary)]/40' : 'bg-[var(--bg-subtle)]/40' }} space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-[10px] font-semibold text-[var(--text-primary)]">
                                {{ $m['month_label'] }}
                            </span>
                            @if ($m['is_current'])
                                <span class="text-[8px] font-mono uppercase px-1 py-0.2 bg-[var(--accent-primary)] text-white rounded-[2px]">
                                    Current
                                </span>
                            @endif
                        </div>
                        <div class="space-y-0.5 text-[10px] font-mono tabular-nums">
                            <div class="flex justify-between text-[var(--text-muted)]">
                                <span>In:</span>
                                <span class="font-medium text-emerald-600 dark:text-emerald-400">+${{ number_format((float) $m['income'], 2) }}</span>
                            </div>
                            <div class="flex justify-between text-[var(--text-muted)]">
                                <span>Out:</span>
                                <span class="font-medium text-[var(--text-primary)]">-${{ number_format((float) $m['expense'], 2) }}</span>
                            </div>
                        </div>
                        <div class="pt-1 border-t hairline-border flex items-center justify-between text-[10px] font-mono">
                            <span class="text-[var(--text-muted)]">Net:</span>
                            <span class="font-bold tabular-nums {{ $isPos ? 'text-emerald-600 dark:text-emerald-400' : ($isNeg ? 'text-rose-600 dark:text-rose-400' : 'text-[var(--text-muted)]') }}">
                                {{ (float) $m['net'] > 0 ? '+' : '' }}${{ number_format((float) $m['net'], 2) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- COMPARATIVE CATEGORY SPENDING + RECENT TRANSACTIONS   --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            {{-- Comparative Category Spending Widget (7-col) --}}
            <div class="lg:col-span-7 card-campus border hairline-border p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)] flex items-center gap-1.5">
                            <span>Comparative Analysis</span>
                            <span wire:loading wire:target="setTimePeriod" class="text-[var(--accent-primary)] animate-pulse">&bull; updating...</span>
                        </div>
                        <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">
                            Category Spending Trends
                        </h2>
                    </div>

                    {{-- Period Switcher Segmented Control --}}
                    <div class="inline-flex items-center gap-1 p-0.5 rounded-[6px] border hairline-border bg-[var(--bg-subtle)] flex-shrink-0">
                        <button type="button"
                                wire:click="setTimePeriod('this_month')"
                                class="px-2.5 py-1 rounded-[4px] text-[11px] font-mono transition-colors {{ $timePeriod === 'this_month' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                            This Month
                        </button>
                        <button type="button"
                                wire:click="setTimePeriod('last_3_months')"
                                class="px-2.5 py-1 rounded-[4px] text-[11px] font-mono transition-colors {{ $timePeriod === 'last_3_months' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                            3 Months
                        </button>
                        <button type="button"
                                wire:click="setTimePeriod('last_6_months')"
                                class="px-2.5 py-1 rounded-[4px] text-[11px] font-mono transition-colors {{ $timePeriod === 'last_6_months' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                            6 Months
                        </button>
                        <button type="button"
                                wire:click="setTimePeriod('year')"
                                class="px-2.5 py-1 rounded-[4px] text-[11px] font-mono transition-colors {{ $timePeriod === 'year' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                            This Year
                        </button>
                    </div>
                </div>

                {{-- Period Totals Summary Strip --}}
                <div class="p-3 rounded-[6px] border hairline-border bg-[var(--bg-subtle)]/40 flex items-center justify-between text-xs">
                    <div>
                        <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">
                            {{ $categoryComparisons['period_label'] }} Total
                        </div>
                        <div class="font-mono text-base font-bold text-[var(--text-primary)] tabular-nums">
                            ${{ number_format((float) $categoryComparisons['current_total'], 2) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-mono uppercase text-[var(--text-muted)]">
                            {{ $categoryComparisons['comparison_label'] }}
                        </div>
                        <div class="font-mono text-xs tabular-nums text-[var(--text-muted)]">
                            ${{ number_format((float) $categoryComparisons['previous_total'], 2) }}
                            @php
                                $totDir = $categoryComparisons['total_change']['direction'];
                            @endphp
                            @if ($totDir === 'increased')
                                <span class="font-semibold text-rose-600 dark:text-rose-400 ml-1">
                                    (+${{ number_format((float) $categoryComparisons['total_delta'], 2) }} / {{ $categoryComparisons['total_change']['formatted'] }})
                                </span>
                            @elseif ($totDir === 'decreased')
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400 ml-1">
                                    (-${{ number_format(abs((float) $categoryComparisons['total_delta']), 2) }} / {{ $categoryComparisons['total_change']['formatted'] }})
                                </span>
                            @else
                                <span class="text-[var(--text-muted)] ml-1">({{ $categoryComparisons['total_change']['formatted'] }})</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Category Comparison Rows --}}
                @if (empty($categoryComparisons['categories']))
                    <div class="text-center py-8 space-y-2">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                            <x-icon name="pie-chart" class="w-5 h-5" />
                        </div>
                        <div class="text-xs font-semibold text-[var(--text-primary)]">No expense transactions recorded</div>
                        <p class="text-xs text-[var(--text-muted)] max-w-xs mx-auto">
                            No expenses logged for {{ $categoryComparisons['period_label'] }}.
                        </p>
                        <a href="{{ route('transactions') }}" class="btn-primary py-1.5 px-3 text-xs inline-flex mt-1">
                            Add Transaction
                        </a>
                    </div>
                @else
                    <div class="space-y-3.5">
                        @foreach ($categoryComparisons['categories'] as $c)
                            @php
                                $dir = $c['direction'];
                                $isInc = $dir === 'increased';
                                $isDec = $dir === 'decreased';
                            @endphp
                            <div class="p-3 rounded-[6px] border hairline-border bg-[var(--bg-surface)] hover:bg-[var(--bg-subtle)]/30 transition-colors space-y-2">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-6 h-6 rounded-[4px] flex items-center justify-center text-white flex-shrink-0"
                                             style="background-color: {{ $c['color'] }};">
                                            <x-icon :name="$c['icon']" class="w-3.5 h-3.5" />
                                        </div>
                                        <span class="font-heading font-medium text-xs text-[var(--text-primary)] truncate">
                                            {{ $c['name'] }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if ($c['is_new'])
                                            <span class="badge-campus text-[9px] font-mono uppercase bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                New
                                            </span>
                                        @elseif ($isInc)
                                            <span class="badge-campus text-[9px] font-mono uppercase bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                {{ $c['pct_formatted'] }}
                                            </span>
                                        @elseif ($isDec)
                                            <span class="badge-campus text-[9px] font-mono uppercase bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                {{ $c['pct_formatted'] }}
                                            </span>
                                        @else
                                            <span class="badge-campus text-[9px] font-mono uppercase bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                0.0%
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-baseline justify-between text-xs font-mono tabular-nums">
                                        <div class="text-[var(--text-primary)] font-semibold">
                                            ${{ number_format((float) $c['current_spent'], 2) }}
                                            <span class="text-[var(--text-muted)] font-normal text-[10px] ml-1">
                                                vs. ${{ number_format((float) $c['previous_spent'], 2) }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] font-semibold {{ $isInc ? 'text-rose-600 dark:text-rose-400' : ($isDec ? 'text-emerald-600 dark:text-emerald-400' : 'text-[var(--text-muted)]') }}">
                                            @if ($isInc)
                                                +${{ number_format((float) $c['delta'], 2) }}
                                            @elseif ($isDec)
                                                -${{ number_format(abs((float) $c['delta']), 2) }}
                                            @else
                                                $0.00
                                            @endif
                                            <span class="text-[10px] text-[var(--text-muted)] font-normal">({{ $c['share_pct'] }}% of total)</span>
                                        </div>
                                    </div>
                                    <div class="w-full h-1.5 rounded-[3px] bg-[var(--bg-subtle)] overflow-hidden">
                                        <div class="h-full rounded-[3px] transition-all duration-500"
                                             style="width: {{ $c['share_pct'] }}%; background-color: {{ $c['color'] }};"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Transactions (5-col) --}}
            <div class="lg:col-span-5 card-campus border hairline-border overflow-hidden p-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Live Ledger</div>
                        <div class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">Recent Transactions</div>
                    </div>
                    <a href="{{ route('transactions') }}" class="text-xs font-medium text-[var(--accent-primary)] hover:underline">
                        View All &rarr;
                    </a>
                </div>

                @if ($recentTransactions->isEmpty())
                    <div class="p-10 text-center space-y-3">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                            <x-icon name="wallet" class="w-5 h-5" />
                        </div>
                        <div class="font-heading text-sm font-bold text-[var(--text-primary)] uppercase tracking-wide">
                            NO TRANSACTIONS RECORDED YET
                        </div>
                        <p class="text-xs text-[var(--text-muted)] max-w-xs mx-auto">
                            Start tracking your campus expenses to unlock insights.
                        </p>
                        <a href="{{ route('transactions') }}" class="btn-primary py-2 px-4 text-xs inline-flex mt-2">
                            Add First Transaction
                        </a>
                    </div>
                @else
                    <div class="divide-y hairline-border">
                        @foreach ($recentTransactions as $t)
                            <div class="px-5 py-3.5 flex items-center justify-between hover:bg-[var(--bg-subtle)]/60 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-[6px] flex items-center justify-center text-white flex-shrink-0"
                                         style="background-color: {{ $t->category?->color ?? '#64748B' }};">
                                        <x-icon :name="$t->category?->icon ?? 'tag'" class="w-3.5 h-3.5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-xs text-[var(--text-primary)] truncate">
                                            {{ $t->merchant }}
                                        </div>
                                        <div class="text-[10px] font-mono text-[var(--text-muted)]">
                                            {{ $t->transaction_date->format('M d') }} &bull;
                                            {{ $t->category?->name ?? 'Uncategorized' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="font-mono font-semibold text-xs tabular-nums flex-shrink-0 ml-2 {{ $t->isIncome() ? 'text-emerald-600 dark:text-emerald-400' : 'text-[var(--text-primary)]' }}">
                                    {{ $t->formattedAmount() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-5 py-3 border-t hairline-border bg-[var(--bg-subtle)]/30">
                        <a href="{{ route('transactions') }}" class="text-xs text-[var(--text-muted)] hover:text-[var(--accent-primary)] font-medium transition-colors">
                            View full ledger &rarr;
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- ALL-TIME STATS FOOTER                                  --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="card-campus border hairline-border p-4 text-center space-y-1">
                <div class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">All-Time Income</div>
                <div class="font-mono font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                    ${{ number_format($allTimeIncome, 2) }}
                </div>
            </div>
            <div class="card-campus border hairline-border p-4 text-center space-y-1">
                <div class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">All-Time Expenses</div>
                <div class="font-mono font-bold text-[var(--text-primary)] tabular-nums">
                    ${{ number_format($allTimeExpense, 2) }}
                </div>
            </div>
            <div class="card-campus border hairline-border p-4 text-center space-y-1">
                <div class="text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Total Entries</div>
                <div class="font-mono font-bold text-[var(--text-primary)] tabular-nums">
                    {{ number_format($totalCount) }}
                </div>
            </div>
        </div>
    </div>
</div>
