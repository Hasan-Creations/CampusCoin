<div>
    <x-slot:header>
        Financial Reports
    </x-slot:header>

    <div class="space-y-6">
        {{-- ===================================================== --}}
        {{-- REPORT HEADER & EXPORT ACTIONS                         --}}
        {{-- ===================================================== --}}
        <div class="page-header flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <span class="text-xs font-caps text-[var(--muted)]">
                    {{ $user->academic_year ?? 'Student' }} Cohort &bull; Statement Generator
                </span>
                <h1 class="font-display text-2xl sm:text-3xl font-medium text-[var(--ink)] mt-1 headline-rule">
                    Monthly Financial Reports
                </h1>
                <p class="text-xs text-[var(--muted)] mt-1.5">
                    Review ledger totals, category breakdowns, daily activity, and export options.
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
                <x-button variant="secondary" href="{{ route('reports.export.csv', $exportParams) }}" title="Export CSV data">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>CSV Export</span>
                </x-button>
                <x-button variant="secondary" href="{{ route('reports.export.pdf', array_merge($exportParams, ['preview' => 1])) }}" title="Printable Statement">
                    <x-icon name="credit-card" class="w-4 h-4" />
                    <span>Print Statement</span>
                </x-button>
                <x-button variant="accent" href="{{ route('reports.export.pdf', $exportParams) }}" title="Download PDF statement">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>Download PDF</span>
                </x-button>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- INTERACTIVE REPORT FILTERS                             --}}
        {{-- ===================================================== --}}
        <div class="card-campus p-4 sm:p-5 space-y-3.5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                {{-- Period Selector Segmented Bar --}}
                <div class="segmented-bar flex-wrap" role="group" aria-label="Report period filter">
                    <button type="button" wire:click="setPresetPeriod('this_month')" class="segmented-item {{ $presetPeriod === 'this_month' ? 'active' : '' }}">This Month</button>
                    <button type="button" wire:click="setPresetPeriod('last_month')" class="segmented-item {{ $presetPeriod === 'last_month' ? 'active' : '' }}">Last Month</button>
                    <button type="button" wire:click="setPresetPeriod('last_3_months')" class="segmented-item {{ $presetPeriod === 'last_3_months' ? 'active' : '' }}">3 Months</button>
                    <button type="button" wire:click="setPresetPeriod('last_6_months')" class="segmented-item {{ $presetPeriod === 'last_6_months' ? 'active' : '' }}">6 Months</button>
                    <button type="button" wire:click="setPresetPeriod('year')" class="segmented-item {{ $presetPeriod === 'year' ? 'active' : '' }}">This Year</button>
                    <button type="button" wire:click="setPresetPeriod('custom')" class="segmented-item {{ $presetPeriod === 'custom' ? 'active' : '' }}">Custom Range</button>
                </div>

                {{-- Live Filter Status / Reset --}}
                <div class="flex items-center gap-2 text-xs font-mono text-[var(--muted)]">
                    <span wire:loading class="text-[var(--accent)] animate-pulse">&bull; updating report...</span>
                    <span>Range: <strong class="text-[var(--ink)]">{{ $periodLabel }}</strong></span>
                    @if ($categoryFilter || $typeFilter !== 'all' || $presetPeriod === 'custom')
                        <button type="button" wire:click="resetFilters" class="text-[var(--expense)] hover:underline ml-2">
                            Reset Filters
                        </button>
                    @endif
                </div>
            </div>

            {{-- Custom Date Inputs & Dropdown Filters --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 pt-3 border-t hairline-border">
                <div>
                    <label for="report-date-from" class="block text-xs font-caps text-[var(--muted)] mb-1">Date From</label>
                    <x-field type="date" id="report-date-from" wire:model.live="dateFrom" class="text-xs font-mono py-1.5" />
                </div>
                <div>
                    <label for="report-date-to" class="block text-xs font-caps text-[var(--muted)] mb-1">Date To</label>
                    <x-field type="date" id="report-date-to" wire:model.live="dateTo" class="text-xs font-mono py-1.5" />
                </div>
                <div>
                    <label for="report-category-filter" class="block text-xs font-caps text-[var(--muted)] mb-1">Category</label>
                    <x-field type="select" id="report-category-filter" wire:model.live="categoryFilter" class="text-xs py-1.5">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </x-field>
                </div>
                <div>
                    <label for="report-type-filter" class="block text-xs font-caps text-[var(--muted)] mb-1">Transaction Type</label>
                    <x-field type="select" id="report-type-filter" wire:model.live="typeFilter" class="text-xs py-1.5">
                        <option value="all">All Movements</option>
                        <option value="expense">Expenses Only</option>
                        <option value="income">Income Only</option>
                    </x-field>
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- FOUR EXECUTIVE SUMMARY KPI CARDS                       --}}
        {{-- ===================================================== --}}
        <div class="stat-strip">
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Total Inflow</div>
                <div class="font-mono text-2xl font-medium text-[var(--accent)] tabular-nums mt-1">
                    +${{ number_format((float) $summary['total_income'], 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    {{ $summary['income_count'] }} income entries &bull; Prior: ${{ number_format((float) $summary['prev_income'], 2) }}
                </div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Total Outflow</div>
                <div class="font-mono text-2xl font-medium text-[var(--ink)] tabular-nums mt-1">
                    -${{ number_format((float) $summary['total_expense'], 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    {{ $summary['expense_count'] }} expense entries &bull; Prior: ${{ number_format((float) $summary['prev_expense'], 2) }}
                </div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Net Cash Flow</div>
                <div class="font-mono text-2xl font-medium tabular-nums mt-1 {{ (float) $summary['net_movement'] >= 0 ? 'text-[var(--accent)]' : 'text-[var(--expense)]' }}">
                    {{ (float) $summary['net_movement'] >= 0 ? '+' : '' }}${{ number_format((float) $summary['net_movement'], 2) }}
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    Delta vs. Prior:
                    <span class="font-mono font-medium {{ (float) $summary['net_delta'] >= 0 ? 'text-[var(--accent)]' : 'text-[var(--expense)]' }}">
                        {{ (float) $summary['net_delta'] >= 0 ? '+' : '' }}${{ number_format((float) $summary['net_delta'], 2) }}
                    </span>
                </div>
            </div>
            <div>
                <div class="text-xs font-caps text-[var(--muted)]">Savings Efficiency</div>
                <div class="font-mono text-2xl font-medium text-[var(--secondary)] tabular-nums mt-1">
                    {{ $summary['savings_rate'] }}%
                </div>
                <div class="text-[11px] text-[var(--muted)] mt-0.5">
                    {{ $summary['total_count'] }} total transactions audited
                </div>
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- REPORT TABS NAVIGATION                                 --}}
        {{-- ===================================================== --}}
        <div role="tablist" aria-label="Report Views" class="border-b hairline-border flex items-center gap-6 overflow-x-auto text-sm">
            <button type="button" role="tab"
                    aria-selected="{{ ($reportTab === 'monthly' || $reportTab === 'category') ? 'true' : 'false' }}"
                    wire:click="setTab('category')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'monthly' || $reportTab === 'category' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-[var(--muted)] hover:text-[var(--ink)]' }}">
                Category Spending Breakdown
            </button>
            <button type="button" role="tab"
                    aria-selected="{{ $reportTab === 'six_month' ? 'true' : 'false' }}"
                    wire:click="setTab('six_month')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'six_month' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-[var(--muted)] hover:text-[var(--ink)]' }}">
                Six-Month Velocity View
            </button>
            <button type="button" role="tab"
                    aria-selected="{{ $reportTab === 'daily' ? 'true' : 'false' }}"
                    wire:click="setTab('daily')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'daily' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-[var(--muted)] hover:text-[var(--ink)]' }}">
                Daily Current-Month Velocity
            </button>
            <button type="button" role="tab"
                    aria-selected="{{ $reportTab === 'weekly' ? 'true' : 'false' }}"
                    wire:click="setTab('weekly')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'weekly' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-[var(--muted)] hover:text-[var(--ink)]' }}">
                Weekly Current-Month Movement
            </button>
            <button type="button" role="tab"
                    aria-selected="{{ $reportTab === 'ledger' ? 'true' : 'false' }}"
                    wire:click="setTab('ledger')"
                    class="pb-3 border-b-2 font-medium transition-colors whitespace-nowrap {{ $reportTab === 'ledger' ? 'border-[var(--accent)] text-[var(--accent)]' : 'border-transparent text-[var(--muted)] hover:text-[var(--ink)]' }}">
                Filtered Ledger ({{ $transactions->total() }})
            </button>
        </div>

        {{-- ===================================================== --}}
        {{-- TAB 1: CATEGORY-WISE SPENDING REPORT                   --}}
        {{-- ===================================================== --}}
        @if ($reportTab === 'monthly' || $reportTab === 'category')
            <div class="card-campus overflow-hidden p-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between bg-[var(--panel)]">
                    <div>
                        <div class="text-xs font-caps text-[var(--muted)]">Expense Breakdown</div>
                        <h2 class="font-display font-medium text-base text-[var(--ink)] mt-0.5">Category Spending Analysis</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--muted)]">
                        Total Period Outflow: <span class="font-medium text-[var(--ink)] tabular-nums">${{ number_format((float) $categoryReport['total_spent'], 2) }}</span>
                    </div>
                </div>

                @if (empty($categoryReport['categories']))
                    <div class="p-12 text-center space-y-2">
                        <x-icon name="pie-chart" class="w-6 h-6 mx-auto text-[var(--muted)]" />
                        <div class="text-xs font-medium text-[var(--ink)]">No category expenses found</div>
                        <p class="text-xs text-[var(--muted)] max-w-sm mx-auto">No expense entries match your active date range or filters.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="ledger-table">
                            <thead>
                                <tr>
                                    <th scope="col">Category</th>
                                    <th scope="col" class="text-right">Total Spent</th>
                                    <th scope="col" class="text-right">% Share</th>
                                    <th scope="col" class="text-center">Entries</th>
                                    <th scope="col" class="text-right">Avg / Entry</th>
                                    <th scope="col" class="text-right">Prior Period</th>
                                    <th scope="col" class="text-right">Trend / Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categoryReport['categories'] as $cat)
                                    <tr>
                                        <td>
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-6 h-6 flex items-center justify-center text-white flex-shrink-0"
                                                     style="background-color: {{ $cat['color'] }};">
                                                    <x-icon :name="$cat['icon']" class="w-3.5 h-3.5" />
                                                </div>
                                                <div>
                                                    <div class="font-medium text-xs text-[var(--ink)]">{{ $cat['name'] }}</div>
                                                    <div class="text-[10px] font-mono text-[var(--muted)] capitalize">{{ $cat['type'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right font-mono font-medium text-[var(--ink)]">${{ number_format((float) $cat['spent'], 2) }}</td>
                                        <td class="text-right font-mono text-[var(--muted)]">{{ $cat['percentage_of_total'] }}%</td>
                                        <td class="text-center font-mono text-[var(--muted)]">{{ $cat['count'] }}</td>
                                        <td class="text-right font-mono text-[var(--muted)]">${{ number_format((float) $cat['average_amount'], 2) }}</td>
                                        <td class="text-right font-mono text-[var(--muted)]">${{ number_format((float) $cat['prev_spent'], 2) }}</td>
                                        <td class="text-right">
                                            @if ($cat['is_new'])
                                                <span class="badge badge-secondary">New</span>
                                            @elseif ($cat['direction'] === 'increased')
                                                <span class="badge badge-expense">+${{ number_format((float) $cat['delta'], 2) }} ({{ $cat['pct_formatted'] }})</span>
                                            @elseif ($cat['direction'] === 'decreased')
                                                <span class="badge badge-income">-${{ number_format(abs((float) $cat['delta']), 2) }} ({{ $cat['pct_formatted'] }})</span>
                                            @else
                                                <span class="badge">$0.00 (0.0%)</span>
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
            <div class="card-campus overflow-hidden p-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between bg-[var(--panel)]">
                    <div>
                        <div class="text-xs font-caps text-[var(--muted)]">Multi-Month Historical View</div>
                        <h2 class="font-display font-medium text-base text-[var(--ink)] mt-0.5">Six-Month Income vs. Expense Trend Report</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--muted)]">
                        6-Month Net: <span class="font-medium {{ (float) $sixMonthTrends['total_net'] >= 0 ? 'text-[var(--accent)]' : 'text-[var(--expense)]' }}">{{ (float) $sixMonthTrends['total_net'] >= 0 ? '+' : '' }}${{ number_format((float) $sixMonthTrends['total_net'], 2) }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="ledger-table">
                        <thead>
                            <tr>
                                <th scope="col">Calendar Month</th>
                                <th scope="col" class="text-right">Total Inflow</th>
                                <th scope="col" class="text-right">Total Outflow</th>
                                <th scope="col" class="text-right">Net Movement</th>
                                <th scope="col" class="text-center">Savings Rate</th>
                                <th scope="col" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sixMonthTrends['months'] as $m)
                                <tr class="{{ $m['is_current'] ? 'bg-[var(--paper)]' : '' }}">
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-[var(--ink)]">{{ $m['month_label'] }}</span>
                                            @if ($m['is_current'])
                                                <span class="badge badge-income">Current</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-right font-mono text-[var(--accent)] font-medium">+${{ number_format((float) $m['income'], 2) }}</td>
                                    <td class="text-right font-mono text-[var(--ink)]">-${{ number_format((float) $m['expense'], 2) }}</td>
                                    <td class="text-right font-mono font-medium {{ (float) $m['net'] >= 0 ? 'text-[var(--accent)]' : 'text-[var(--expense)]' }}">
                                        {{ (float) $m['net'] >= 0 ? '+' : '' }}${{ number_format((float) $m['net'], 2) }}
                                    </td>
                                    <td class="text-center font-mono text-[var(--muted)]">{{ $m['savings_rate'] }}%</td>
                                    <td class="text-center">
                                        @if ($m['status'] === 'positive')
                                            <span class="badge badge-income">Surplus</span>
                                        @elseif ($m['status'] === 'negative')
                                            <span class="badge badge-expense">Deficit</span>
                                        @else
                                            <span class="badge">Balanced</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[var(--paper)]">
                            <tr>
                                <td class="font-medium text-[var(--ink)]">6-Month Aggregate Totals</td>
                                <td class="text-right font-mono font-medium text-[var(--accent)]">+${{ number_format((float) $sixMonthTrends['total_income'], 2) }}</td>
                                <td class="text-right font-mono text-[var(--ink)]">-${{ number_format((float) $sixMonthTrends['total_expense'], 2) }}</td>
                                <td class="text-right font-mono font-medium {{ (float) $sixMonthTrends['total_net'] >= 0 ? 'text-[var(--accent)]' : 'text-[var(--expense)]' }}">{{ (float) $sixMonthTrends['total_net'] >= 0 ? '+' : '' }}${{ number_format((float) $sixMonthTrends['total_net'], 2) }}</td>
                                <td class="text-center font-mono text-[var(--muted)]">Avg: ${{ number_format((float) $sixMonthTrends['average_monthly_expense'], 2) }}/mo</td>
                                <td class="text-center text-[var(--muted)]">—</td>
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
            <div class="card-campus overflow-hidden p-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between bg-[var(--panel)]">
                    <div>
                        <div class="text-xs font-caps text-[var(--muted)]">{{ $dailySummary['month_label'] }} &bull; Actual Calendar Activity</div>
                        <h2 class="font-display font-medium text-base text-[var(--ink)] mt-0.5">Daily Transaction Velocity</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--muted)]">{{ $dailySummary['total_days_active'] }} days with logged activity</div>
                </div>

                @if (empty($dailySummary['days']))
                    <div class="p-12 text-center space-y-2">
                        <x-icon name="calendar" class="w-6 h-6 mx-auto text-[var(--muted)]" />
                        <div class="text-xs font-medium text-[var(--ink)]">No daily activity recorded this month</div>
                        <p class="text-xs text-[var(--muted)] max-w-sm mx-auto">Transactions logged in the ledger will populate this day-by-day audit log.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="ledger-table">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col" class="text-right">Daily Inflow</th>
                                    <th scope="col" class="text-right">Daily Outflow</th>
                                    <th scope="col" class="text-right">Net Daily Movement</th>
                                    <th scope="col" class="text-center">Entries</th>
                                    <th scope="col" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dailySummary['days'] as $day)
                                    <tr>
                                        <td class="font-medium text-[var(--ink)]">{{ $day['formatted_date'] }}</td>
                                        <td class="text-right font-mono text-[var(--accent)] font-medium">{{ (float) $day['income'] > 0 ? '+'.number_format((float) $day['income'], 2) : '$0.00' }}</td>
                                        <td class="text-right font-mono text-[var(--ink)]">{{ (float) $day['expense'] > 0 ? '-'.number_format((float) $day['expense'], 2) : '$0.00' }}</td>
                                        <td class="text-right font-mono font-medium {{ (float) $day['net'] > 0 ? 'text-[var(--accent)]' : ((float) $day['net'] < 0 ? 'text-[var(--expense)]' : 'text-[var(--muted)]') }}">
                                            {{ (float) $day['net'] > 0 ? '+' : '' }}${{ number_format((float) $day['net'], 2) }}
                                        </td>
                                        <td class="text-center font-mono text-[var(--muted)]">{{ $day['count'] }}</td>
                                        <td class="text-center">
                                            @if ($day['status'] === 'positive')
                                                <span class="badge badge-income">Surplus</span>
                                            @elseif ($day['status'] === 'negative')
                                                <span class="badge badge-expense">Deficit</span>
                                            @else
                                                <span class="badge">Neutral</span>
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
            <div class="card-campus overflow-hidden p-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between bg-[var(--panel)]">
                    <div>
                        <div class="text-xs font-caps text-[var(--muted)]">{{ $weeklySummary['month_label'] }} &bull; Calendar Periods</div>
                        <h2 class="font-display font-medium text-base text-[var(--ink)] mt-0.5">Weekly Cash Movement</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--muted)]">5 Calendar Periods Evaluated</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="ledger-table">
                        <thead>
                            <tr>
                                <th scope="col">Calendar Period</th>
                                <th scope="col" class="text-right">Weekly Inflow</th>
                                <th scope="col" class="text-right">Weekly Outflow</th>
                                <th scope="col" class="text-right">Net Weekly Movement</th>
                                <th scope="col" class="text-center">Entries</th>
                                <th scope="col" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($weeklySummary['weeks'] as $week)
                                <tr>
                                    <td class="font-medium text-[var(--ink)]">{{ $week['label'] }}</td>
                                    <td class="text-right font-mono text-[var(--accent)] font-medium">+${{ number_format((float) $week['income'], 2) }}</td>
                                    <td class="text-right font-mono text-[var(--ink)]">-${{ number_format((float) $week['expense'], 2) }}</td>
                                    <td class="text-right font-mono font-medium {{ (float) $week['net'] > 0 ? 'text-[var(--accent)]' : ((float) $week['net'] < 0 ? 'text-[var(--expense)]' : 'text-[var(--muted)]') }}">
                                        {{ (float) $week['net'] > 0 ? '+' : '' }}${{ number_format((float) $week['net'], 2) }}
                                    </td>
                                    <td class="text-center font-mono text-[var(--muted)]">{{ $week['count'] }}</td>
                                    <td class="text-center">
                                        @if ($week['status'] === 'positive')
                                            <span class="badge badge-income">Surplus</span>
                                        @elseif ($week['status'] === 'negative')
                                            <span class="badge badge-expense">Deficit</span>
                                        @else
                                            <span class="badge">Neutral</span>
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
            <div class="card-campus overflow-hidden p-0">
                <div class="p-5 border-b hairline-border flex items-center justify-between bg-[var(--panel)]">
                    <div>
                        <div class="text-xs font-caps text-[var(--muted)]">Verified Ledger</div>
                        <h2 class="font-display font-medium text-base text-[var(--ink)] mt-0.5">Audited Transaction Records</h2>
                    </div>
                    <div class="text-xs font-mono text-[var(--muted)]">
                        Showing {{ $transactions->firstItem() ?? 0 }}–{{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries
                    </div>
                </div>

                @if ($transactions->isEmpty())
                    <div class="p-12 text-center space-y-2">
                        <x-icon name="wallet" class="w-6 h-6 mx-auto text-[var(--muted)]" />
                        <div class="text-xs font-medium text-[var(--ink)]">No transactions found</div>
                        <p class="text-xs text-[var(--muted)] max-w-sm mx-auto">No ledger entries match the selected date range and filter criteria.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="ledger-table">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Merchant / Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Method</th>
                                    <th scope="col" class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transactions as $t)
                                    <tr>
                                        <td class="font-mono text-[var(--muted)]">{{ $t->transaction_date->format('M d, Y') }}</td>
                                        <td>
                                            <div class="font-medium text-xs text-[var(--ink)]">{{ $t->merchant }}</div>
                                            @if ($t->description)
                                                <div class="text-[10px] text-[var(--muted)] truncate max-w-xs">{{ $t->description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 flex-shrink-0" style="background-color: {{ $t->category?->color ?? '#64748B' }};"></div>
                                                <span class="text-xs text-[var(--ink)]">{{ $t->category?->name ?? 'Uncategorized' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $t->isIncome() ? 'badge-income' : 'badge-expense' }}">
                                                {{ ucfirst($t->type) }}
                                            </span>
                                        </td>
                                        <td class="text-xs capitalize text-[var(--muted)]">{{ $t->payment_method }}</td>
                                        <td class="text-right font-mono font-medium {{ $t->isIncome() ? 'text-[var(--accent)]' : 'text-[var(--ink)]' }}">
                                            {{ $t->formattedAmount() }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($transactions->hasPages())
                        <div class="p-3.5 border-t hairline-border bg-[var(--paper)]">
                            {{ $transactions->links() }}
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>
