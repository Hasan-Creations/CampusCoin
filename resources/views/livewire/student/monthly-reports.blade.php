<div>
    <x-slot:header>
        Financial Reports
    </x-slot:header>

    <div class="space-y-6">
        {{-- ===================================================== --}}
        {{-- REPORT HEADER & EXPORT ACTIONS                         --}}
        {{-- ===================================================== --}}
        <div class="p-6 rounded-[8px] border hairline-border bg-[var(--bg-surface)] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="text-xs font-mono text-[var(--accent-primary)] font-semibold uppercase tracking-wider mb-1">
                    {{ $user->academic_year ?? 'Student' }} Cohort &bull; Statement Generator
                </div>
                <h1 class="font-heading text-2xl font-bold text-[var(--text-primary)]">
                    Monthly Financial Reports
                </h1>
                <p class="text-xs text-[var(--text-muted)] mt-1">
                    Comprehensive ledger reconciliation, category breakdowns, daily velocities, and multi-format exports.
                </p>
            </div>

            @php
                $exportParams = [
                    'preset' => $presetPeriod,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'category_id' => $categoryFilter,
                    'type' => $typeFilter !== 'all' ? $typeFilter : null,
                ];
            @endphp

            <div class="flex flex-wrap items-center gap-2.5 flex-shrink-0">
                <a href="{{ route('reports.export.csv', $exportParams) }}"
                   class="btn-secondary py-2 px-3 text-xs flex items-center gap-1.5"
                   title="Export CSV data">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>CSV Export</span>
                </a>
                <a href="{{ route('reports.export.pdf', array_merge($exportParams, ['preview' => 1])) }}"
                   target="_blank"
                   class="btn-secondary py-2 px-3 text-xs flex items-center gap-1.5"
                   title="Printable Statement">
                    <x-icon name="credit-card" class="w-4 h-4" />
                    <span>Print Statement</span>
                </a>
                <a href="{{ route('reports.export.pdf', $exportParams) }}"
                   target="_blank"
                   class="btn-primary py-2 px-4 text-xs flex items-center gap-1.5"
                   title="Download PDF statement">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>Download PDF</span>
                </a>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- INTERACTIVE REPORT FILTERS                             --}}
        {{-- ===================================================== --}}
        <div class="card-campus border hairline-border p-4 space-y-3">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                {{-- Period Selector Tabs --}}
                <div class="inline-flex flex-wrap items-center gap-1 p-0.5 rounded-[6px] border hairline-border bg-[var(--bg-subtle)]">
                    <button type="button"
                            wire:click="setPresetPeriod('this_month')"
                            class="px-2.5 py-1 rounded-[4px] text-xs font-mono transition-colors {{ $presetPeriod === 'this_month' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                        This Month
                    </button>
                    <button type="button"
                            wire:click="setPresetPeriod('last_month')"
                            class="px-2.5 py-1 rounded-[4px] text-xs font-mono transition-colors {{ $presetPeriod === 'last_month' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                        Last Month
                    </button>
                    <button type="button"
                            wire:click="setPresetPeriod('last_3_months')"
                            class="px-2.5 py-1 rounded-[4px] text-xs font-mono transition-colors {{ $presetPeriod === 'last_3_months' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                        3 Months
                    </button>
                    <button type="button"
                            wire:click="setPresetPeriod('last_6_months')"
                            class="px-2.5 py-1 rounded-[4px] text-xs font-mono transition-colors {{ $presetPeriod === 'last_6_months' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                        6 Months
                    </button>
                    <button type="button"
                            wire:click="setPresetPeriod('year')"
                            class="px-2.5 py-1 rounded-[4px] text-xs font-mono transition-colors {{ $presetPeriod === 'year' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                        This Year
                    </button>
                    <button type="button"
                            wire:click="setPresetPeriod('custom')"
                            class="px-2.5 py-1 rounded-[4px] text-xs font-mono transition-colors {{ $presetPeriod === 'custom' ? 'bg-[var(--bg-surface)] text-[var(--text-primary)] font-semibold shadow-xs' : 'text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                        Custom Range
                    </button>
                </div>

                {{-- Live Filter Status / Reset --}}
                <div class="flex items-center gap-2 text-xs font-mono text-[var(--text-muted)]">
                    <span wire:loading class="text-[var(--accent-primary)] animate-pulse">&bull; updating report...</span>
                    <span>Range: <strong class="text-[var(--text-primary)]">{{ $periodLabel }}</strong></span>
                    @if ($categoryFilter || $typeFilter !== 'all' || $presetPeriod === 'custom')
                        <button type="button" wire:click="resetFilters" class="text-rose-600 hover:underline ml-2">
                            Reset Filters
                        </button>
                    @endif
                </div>
            </div>

            {{-- Custom Date Inputs & Dropdown Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-2 border-t hairline-border">
                {{-- Date From --}}
                <div>
                    <label for="report-date-from" class="block text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)] mb-1">Date From</label>
                    <input type="date"
                           id="report-date-from"
                           wire:model.live="dateFrom"
                           class="w-full px-2.5 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-surface)] text-xs font-mono text-[var(--text-primary)] focus:outline-none focus:border-[var(--accent-primary)]" />
                </div>

                {{-- Date To --}}
                <div>
                    <label for="report-date-to" class="block text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)] mb-1">Date To</label>
                    <input type="date"
                           id="report-date-to"
                           wire:model.live="dateTo"
                           class="w-full px-2.5 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-surface)] text-xs font-mono text-[var(--text-primary)] focus:outline-none focus:border-[var(--accent-primary)]" />
                </div>

                {{-- Category Filter --}}
                <div>
                    <label for="report-category-filter" class="block text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)] mb-1">Category</label>
                    <select id="report-category-filter"
                            wire:model.live="categoryFilter"
                            class="w-full px-2.5 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] focus:outline-none focus:border-[var(--accent-primary)]">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Type Filter --}}
                <div>
                    <label for="report-type-filter" class="block text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)] mb-1">Transaction Type</label>
                    <select id="report-type-filter"
                            wire:model.live="typeFilter"
                            class="w-full px-2.5 py-1.5 rounded-[4px] border hairline-border bg-[var(--bg-surface)] text-xs font-medium text-[var(--text-primary)] focus:outline-none focus:border-[var(--accent-primary)]">
                        <option value="all">All Movements</option>
                        <option value="expense">Expenses Only</option>
                        <option value="income">Income Only</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- FOUR EXECUTIVE SUMMARY KPI CARDS                       --}}
        {{-- ===================================================== --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Income --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Total Inflow</span>
                    <x-icon name="trending-up" class="w-4 h-4 text-emerald-500" />
                </div>
                <div class="font-mono text-2xl font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                    +${{ number_format((float) $summary['total_income'], 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    {{ $summary['income_count'] }} income entries &bull; Prior: ${{ number_format((float) $summary['prev_income'], 2) }}
                </div>
            </div>

            {{-- Total Expenses --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Total Outflow</span>
                    <x-icon name="trending-down" class="w-4 h-4 text-[var(--danger)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--text-primary)] tabular-nums">
                    -${{ number_format((float) $summary['total_expense'], 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    {{ $summary['expense_count'] }} expense entries &bull; Prior: ${{ number_format((float) $summary['prev_expense'], 2) }}
                </div>
            </div>

            {{-- Net Movement --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Net Cash Flow</span>
                    <x-icon name="wallet" class="w-4 h-4 text-[var(--accent-primary)]" />
                </div>
                <div class="font-mono text-2xl font-bold tabular-nums {{ (float) $summary['net_movement'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    {{ (float) $summary['net_movement'] >= 0 ? '+' : '' }}${{ number_format((float) $summary['net_movement'], 2) }}
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    Delta vs. Prior:
                    <span class="font-mono font-semibold {{ (float) $summary['net_delta'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ (float) $summary['net_delta'] >= 0 ? '+' : '' }}${{ number_format((float) $summary['net_delta'], 2) }}
                    </span>
                </div>
            </div>

            {{-- Savings Rate & Total Count --}}
            <div class="card-campus border hairline-border p-5 space-y-2">
                <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                    <span>Savings Efficiency</span>
                    <x-icon name="target" class="w-4 h-4 text-[var(--gold)]" />
                </div>
                <div class="font-mono text-2xl font-bold text-[var(--gold)] tabular-nums">
                    {{ $summary['savings_rate'] }}%
                </div>
                <div class="text-[11px] text-[var(--text-muted)]">
                    {{ $summary['total_count'] }} total transactions audited
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- REPORT TABS NAVIGATION                                 --}}
        {{-- ===================================================== --}}
        <div role="tablist" aria-label="Report Views" class="border-b hairline-border flex items-center gap-6 overflow-x-auto text-sm">
            <button type="button"
                    role="tab"
                    aria-selected="{{ ($reportTab === 'monthly' || $reportTab === 'category') ? 'true' : 'false' }}"
                    wire:click="setTab('category')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'monthly' || $reportTab === 'category' ? 'border-[var(--accent-primary)] text-[var(--accent-primary)] font-semibold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                Category Spending Breakdown
            </button>
            <button type="button"
                    role="tab"
                    aria-selected="{{ $reportTab === 'six_month' ? 'true' : 'false' }}"
                    wire:click="setTab('six_month')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'six_month' ? 'border-[var(--accent-primary)] text-[var(--accent-primary)] font-semibold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                Six-Month Velocity View
            </button>
            <button type="button"
                    role="tab"
                    aria-selected="{{ $reportTab === 'daily' ? 'true' : 'false' }}"
                    wire:click="setTab('daily')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'daily' ? 'border-[var(--accent-primary)] text-[var(--accent-primary)] font-semibold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                Daily Current-Month Velocity
            </button>
            <button type="button"
                    role="tab"
                    aria-selected="{{ $reportTab === 'weekly' ? 'true' : 'false' }}"
                    wire:click="setTab('weekly')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'weekly' ? 'border-[var(--accent-primary)] text-[var(--accent-primary)] font-semibold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                Weekly Current-Month Movement
            </button>
            <button type="button"
                    role="tab"
                    aria-selected="{{ $reportTab === 'ledger' ? 'true' : 'false' }}"
                    wire:click="setTab('ledger')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'ledger' ? 'border-[var(--accent-primary)] text-[var(--accent-primary)] font-semibold' : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text-primary)]' }}">
                Filtered Ledger ({{ $transactions->total() }})
            </button>
        </div>

        {{-- ===================================================== --}}
        {{-- TAB 1: CATEGORY-WISE SPENDING REPORT                   --}}
        {{-- ===================================================== --}}
        @if ($reportTab === 'monthly' || $reportTab === 'category')
            <div class="card-campus border hairline-border overflow-hidden p-0 space-y-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">SRS §4.4, §4.6 &bull; Expense Breakdown</div>
                        <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">Category Spending Analysis</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--text-muted)]">
                        Total Period Outflow: <span class="font-bold text-[var(--text-primary)]">${{ number_format((float) $categoryReport['total_spent'], 2) }}</span>
                    </div>
                </div>

                @if (empty($categoryReport['categories']))
                    <div class="p-12 text-center space-y-2">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                            <x-icon name="pie-chart" class="w-5 h-5" />
                        </div>
                        <div class="text-xs font-semibold text-[var(--text-primary)]">No category expenses found</div>
                        <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
                            No expense entries match your active date range or filters.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[var(--bg-subtle)]/50 border-b hairline-border text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                                <tr>
                                    <th scope="col" class="px-5 py-3">Category</th>
                                    <th scope="col" class="px-4 py-3 text-right">Total Spent</th>
                                    <th scope="col" class="px-4 py-3 text-right">% Share</th>
                                    <th scope="col" class="px-4 py-3 text-center">Entries</th>
                                    <th scope="col" class="px-4 py-3 text-right">Avg / Entry</th>
                                    <th scope="col" class="px-4 py-3 text-right">Prior Period</th>
                                    <th scope="col" class="px-5 py-3 text-right">Trend / Change</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y hairline-border font-mono tabular-nums">
                                @foreach ($categoryReport['categories'] as $cat)
                                    <tr class="hover:bg-[var(--bg-subtle)]/30 transition-colors">
                                        <td class="px-5 py-3 font-sans">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-[4px] flex items-center justify-center text-white flex-shrink-0"
                                                     style="background-color: {{ $cat['color'] }};">
                                                    <x-icon :name="$cat['icon']" class="w-3.5 h-3.5" />
                                                </div>
                                                <div>
                                                    <div class="font-medium text-xs text-[var(--text-primary)]">{{ $cat['name'] }}</div>
                                                    <div class="text-[10px] font-mono text-[var(--text-muted)] capitalize">{{ $cat['type'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-[var(--text-primary)]">
                                            ${{ number_format((float) $cat['spent'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-[var(--text-muted)]">
                                            {{ $cat['percentage_of_total'] }}%
                                        </td>
                                        <td class="px-4 py-3 text-center text-[var(--text-muted)]">
                                            {{ $cat['count'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-[var(--text-muted)]">
                                            ${{ number_format((float) $cat['average_amount'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-[var(--text-muted)]">
                                            ${{ number_format((float) $cat['prev_spent'], 2) }}
                                        </td>
                                        <td class="px-5 py-3 text-right">
                                            @if ($cat['is_new'])
                                                <span class="badge-campus bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                                    New
                                                </span>
                                            @elseif ($cat['direction'] === 'increased')
                                                <span class="badge-campus bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                                    +${{ number_format((float) $cat['delta'], 2) }} ({{ $cat['pct_formatted'] }})
                                                </span>
                                            @elseif ($cat['direction'] === 'decreased')
                                                <span class="badge-campus bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                                    -${{ number_format(abs((float) $cat['delta']), 2) }} ({{ $cat['pct_formatted'] }})
                                                </span>
                                            @else
                                                <span class="badge-campus bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">
                                                    $0.00 (0.0%)
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- TAB 2: SIX-MONTH INCOME VS EXPENSE REPORT              --}}
        {{-- ===================================================== --}}
        @if ($reportTab === 'six_month')
            <div class="card-campus border hairline-border overflow-hidden p-0 space-y-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">SRS §4.4, §4.6 &bull; Multi-Month Historical View</div>
                        <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">Six-Month Income vs. Expense Trend Report</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--text-muted)]">
                        6-Month Net: <span class="font-bold {{ (float) $sixMonthTrends['total_net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ (float) $sixMonthTrends['total_net'] >= 0 ? '+' : '' }}${{ number_format((float) $sixMonthTrends['total_net'], 2) }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[var(--bg-subtle)]/50 border-b hairline-border text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                            <tr>
                                <th scope="col" class="px-5 py-3">Calendar Month</th>
                                <th scope="col" class="px-4 py-3 text-right">Total Inflow</th>
                                <th scope="col" class="px-4 py-3 text-right">Total Outflow</th>
                                <th scope="col" class="px-4 py-3 text-right">Net Movement</th>
                                <th scope="col" class="px-4 py-3 text-center">Savings Rate</th>
                                <th scope="col" class="px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y hairline-border font-mono tabular-nums">
                            @foreach ($sixMonthTrends['months'] as $m)
                                <tr class="hover:bg-[var(--bg-subtle)]/30 transition-colors {{ $m['is_current'] ? 'bg-[var(--accent-tint)]/15 font-semibold' : '' }}">
                                    <td class="px-5 py-3 font-sans">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-semibold text-[var(--text-primary)]">{{ $m['month_label'] }}</span>
                                            @if ($m['is_current'])
                                                <span class="badge-campus bg-[var(--accent-tint)] text-[var(--accent-primary)]">Current</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-semibold">
                                        +${{ number_format((float) $m['income'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-[var(--text-primary)]">
                                        -${{ number_format((float) $m['expense'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ (float) $m['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ (float) $m['net'] >= 0 ? '+' : '' }}${{ number_format((float) $m['net'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-[var(--text-muted)]">
                                        {{ $m['savings_rate'] }}%
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($m['status'] === 'positive')
                                            <span class="badge-campus bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Surplus</span>
                                        @elseif ($m['status'] === 'negative')
                                            <span class="badge-campus bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">Deficit</span>
                                        @else
                                            <span class="badge-campus bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">Balanced</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[var(--bg-subtle)]/70 border-t hairline-border font-mono font-bold text-xs">
                            <tr>
                                <td class="px-5 py-3 font-sans">6-Month Aggregate Totals</td>
                                <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400">+${{ number_format((float) $sixMonthTrends['total_income'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-[var(--text-primary)]">-${{ number_format((float) $sixMonthTrends['total_expense'], 2) }}</td>
                                <td class="px-4 py-3 text-right {{ (float) $sixMonthTrends['total_net'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ (float) $sixMonthTrends['total_net'] >= 0 ? '+' : '' }}${{ number_format((float) $sixMonthTrends['total_net'], 2) }}</td>
                                <td class="px-4 py-3 text-center text-[var(--text-muted)]">Avg: ${{ number_format((float) $sixMonthTrends['average_monthly_expense'], 2) }}/mo</td>
                                <td class="px-5 py-3 text-center">—</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- TAB 3: DAILY CURRENT-MONTH SUMMARY                     --}}
        {{-- ===================================================== --}}
        @if ($reportTab === 'daily')
            <div class="card-campus border hairline-border overflow-hidden p-0 space-y-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">{{ $dailySummary['month_label'] }} &bull; Actual Calendar Activity</div>
                        <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">Daily Transaction Velocity</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--text-muted)]">
                        {{ $dailySummary['total_days_active'] }} days with logged activity
                    </div>
                </div>

                @if (empty($dailySummary['days']))
                    <div class="p-12 text-center space-y-2">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                            <x-icon name="calendar" class="w-5 h-5" />
                        </div>
                        <div class="text-xs font-semibold text-[var(--text-primary)]">No daily activity recorded this month</div>
                        <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
                            Transactions logged in the ledger will populate this day-by-day audit log.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[var(--bg-subtle)]/50 border-b hairline-border text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                                <tr>
                                    <th scope="col" class="px-5 py-3">Date</th>
                                    <th scope="col" class="px-4 py-3 text-right">Daily Inflow</th>
                                    <th scope="col" class="px-4 py-3 text-right">Daily Outflow</th>
                                    <th scope="col" class="px-4 py-3 text-right">Net Daily Movement</th>
                                    <th scope="col" class="px-4 py-3 text-center">Entries</th>
                                    <th scope="col" class="px-5 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y hairline-border font-mono tabular-nums">
                                @foreach ($dailySummary['days'] as $day)
                                    <tr class="hover:bg-[var(--bg-subtle)]/30 transition-colors">
                                        <td class="px-5 py-3 font-sans font-medium text-[var(--text-primary)]">
                                            {{ $day['formatted_date'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-semibold">
                                            {{ (float) $day['income'] > 0 ? '+'.number_format((float) $day['income'], 2) : '$0.00' }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-[var(--text-primary)]">
                                            {{ (float) $day['expense'] > 0 ? '-'.number_format((float) $day['expense'], 2) : '$0.00' }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-bold {{ (float) $day['net'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : ((float) $day['net'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-[var(--text-muted)]') }}">
                                            {{ (float) $day['net'] > 0 ? '+' : '' }}${{ number_format((float) $day['net'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-[var(--text-muted)]">
                                            {{ $day['count'] }}
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            @if ($day['status'] === 'positive')
                                                <span class="badge-campus bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Surplus</span>
                                            @elseif ($day['status'] === 'negative')
                                                <span class="badge-campus bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">Deficit</span>
                                            @else
                                                <span class="badge-campus bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">Neutral</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- TAB 4: WEEKLY CURRENT-MONTH SUMMARY                    --}}
        {{-- ===================================================== --}}
        @if ($reportTab === 'weekly')
            <div class="card-campus border hairline-border overflow-hidden p-0 space-y-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">{{ $weeklySummary['month_label'] }} &bull; Calendar Periods</div>
                        <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">Weekly Cash Movement</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--text-muted)]">
                        5 Calendar Periods Evaluated
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-[var(--bg-subtle)]/50 border-b hairline-border text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                            <tr>
                                <th scope="col" class="px-5 py-3">Calendar Period</th>
                                <th scope="col" class="px-4 py-3 text-right">Weekly Inflow</th>
                                <th scope="col" class="px-4 py-3 text-right">Weekly Outflow</th>
                                <th scope="col" class="px-4 py-3 text-right">Net Weekly Movement</th>
                                <th scope="col" class="px-4 py-3 text-center">Entries</th>
                                <th scope="col" class="px-5 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y hairline-border font-mono tabular-nums">
                            @foreach ($weeklySummary['weeks'] as $week)
                                <tr class="hover:bg-[var(--bg-subtle)]/30 transition-colors">
                                    <td class="px-5 py-3 font-sans font-medium text-[var(--text-primary)]">
                                        {{ $week['label'] }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-emerald-600 dark:text-emerald-400 font-semibold">
                                        +${{ number_format((float) $week['income'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-[var(--text-primary)]">
                                        -${{ number_format((float) $week['expense'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold {{ (float) $week['net'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : ((float) $week['net'] < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-[var(--text-muted)]') }}">
                                        {{ (float) $week['net'] > 0 ? '+' : '' }}${{ number_format((float) $week['net'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-[var(--text-muted)]">
                                        {{ $week['count'] }}
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($week['status'] === 'positive')
                                            <span class="badge-campus bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">Surplus</span>
                                        @elseif ($week['status'] === 'negative')
                                            <span class="badge-campus bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">Deficit</span>
                                        @else
                                            <span class="badge-campus bg-slate-100 text-slate-700 dark:bg-zinc-800 dark:text-zinc-300">Neutral</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ===================================================== --}}
        {{-- TAB 5: FILTERED LEDGER TRANSACTIONS                    --}}
        {{-- ===================================================== --}}
        @if ($reportTab === 'ledger')
            <div class="card-campus border hairline-border overflow-hidden p-0 space-y-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between">
                    <div>
                        <div class="text-[11px] font-mono uppercase tracking-wider text-[var(--text-muted)]">Verified Ledger</div>
                        <h2 class="font-heading font-semibold text-sm text-[var(--text-primary)] mt-0.5">Audited Transaction Records</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--text-muted)]">
                        Showing {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                    </div>
                </div>

                @if ($transactions->isEmpty())
                    <div class="p-12 text-center space-y-2">
                        <div class="inline-flex items-center justify-center w-10 h-10 rounded-[6px] bg-[var(--bg-subtle)] text-[var(--text-muted)] mx-auto">
                            <x-icon name="wallet" class="w-5 h-5" />
                        </div>
                        <div class="text-xs font-semibold text-[var(--text-primary)]">No transactions found</div>
                        <p class="text-xs text-[var(--text-muted)] max-w-sm mx-auto">
                            No ledger entries match the selected date range and filter criteria.
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-[var(--bg-subtle)]/50 border-b hairline-border text-[10px] font-mono uppercase tracking-wider text-[var(--text-muted)]">
                                <tr>
                                    <th scope="col" class="px-5 py-3">Date</th>
                                    <th scope="col" class="px-4 py-3">Merchant / Description</th>
                                    <th scope="col" class="px-4 py-3">Category</th>
                                    <th scope="col" class="px-4 py-3">Type</th>
                                    <th scope="col" class="px-4 py-3">Method</th>
                                    <th scope="col" class="px-5 py-3 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y hairline-border font-mono tabular-nums">
                                @foreach ($transactions as $t)
                                    <tr class="hover:bg-[var(--bg-subtle)]/30 transition-colors">
                                        <td class="px-5 py-3 text-[var(--text-muted)]">
                                            {{ $t->transaction_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 font-sans">
                                            <div class="font-medium text-xs text-[var(--text-primary)]">{{ $t->merchant }}</div>
                                            @if ($t->description)
                                                <div class="text-[10px] text-[var(--text-muted)] truncate max-w-xs">{{ $t->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 font-sans">
                                            <div class="flex items-center gap-2">
                                                <div class="w-2.5 h-2.5 rounded-sm flex-shrink-0" style="background-color: {{ $t->category?->color ?? '#64748B' }};"></div>
                                                <span class="text-xs">{{ $t->category?->name ?? 'Uncategorized' }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 font-sans">
                                            <span class="badge-campus {{ $t->isIncome() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' }}">
                                                {{ ucfirst($t->type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-sans text-xs capitalize text-[var(--text-muted)]">
                                            {{ $t->payment_method }}
                                        </td>
                                        <td class="px-5 py-3 text-right font-bold {{ $t->isIncome() ? 'text-emerald-600 dark:text-emerald-400' : 'text-[var(--text-primary)]' }}">
                                            {{ $t->formattedAmount() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($transactions->hasPages())
                        <div class="p-4 border-t hairline-border">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>
