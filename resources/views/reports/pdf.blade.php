<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Campus Coin — Financial Statement</title>
    <style>
        @page {
            margin: 15mm 12mm;
            size: a4 portrait;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.5px;
        }
        .brand-accent {
            color: #059669;
        }
        .brand-sub {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .report-meta {
            text-align: right;
            font-size: 10px;
            color: #475569;
        }
        .student-strip {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }
        .student-table {
            width: 100%;
        }
        .student-table td {
            font-size: 10px;
            color: #334155;
        }
        .student-table strong {
            color: #0f172a;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-top: 18px;
            margin-bottom: 8px;
            border-left: 3px solid #059669;
            padding-left: 6px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .kpi-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            background-color: #ffffff;
            width: 25%;
        }
        .kpi-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 15px;
            font-weight: 700;
            font-family: monospace, monospace;
            color: #0f172a;
        }
        .kpi-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .data-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
            color: #334155;
        }
        .data-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            font-size: 10px;
            color: #1e293b;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: monospace, monospace;
        }
        .text-income {
            color: #059669;
        }
        .text-expense {
            color: #e11d48;
        }
        .footer {
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: 600;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .badge-income {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .badge-expense {
            background-color: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-title">Campus<span class="brand-accent">Coin</span></div>
                <div class="brand-sub">Smart Spending &bull; Official Financial Statement</div>
            </td>
            <td class="report-meta">
                <div><strong>Period:</strong> {{ $periodLabel }}</div>
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                @if (!empty($filterLabels))
                    <div><strong>Filters:</strong> {{ implode(' &bull; ', $filterLabels) }}</div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Student Baseline Strip --}}
    <div class="student-strip">
        <table class="student-table">
            <tr>
                <td><strong>Student:</strong> {{ $user->name }}</td>
                <td><strong>Academic Cohort:</strong> {{ $user->academic_year ?? 'Enrolled Student' }}</td>
                <td><strong>Monthly Baseline:</strong> ${{ number_format((float) ($user->monthly_allowance ?? 0), 2) }}</td>
                <td class="text-right"><strong>Target Savings Goal:</strong> ${{ number_format((float) ($user->savings_goal ?? 0), 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- Summary KPIs --}}
    <div class="section-title">Executive Financial Summary</div>
    <table class="kpi-table">
        <tr>
            <td>
                <div class="kpi-label">Total Inflow</div>
                <div class="kpi-value text-income">+${{ number_format((float) $summary['total_income'], 2) }}</div>
                <div class="kpi-sub">{{ $summary['income_count'] }} recorded entries</div>
            </td>
            <td>
                <div class="kpi-label">Total Outflow</div>
                <div class="kpi-value text-expense">-${{ number_format((float) $summary['total_expense'], 2) }}</div>
                <div class="kpi-sub">{{ $summary['expense_count'] }} recorded entries</div>
            </td>
            <td>
                <div class="kpi-label">Net Movement</div>
                <div class="kpi-value {{ (float) $summary['net_movement'] >= 0 ? 'text-income' : 'text-expense' }}">
                    {{ (float) $summary['net_movement'] >= 0 ? '+' : '' }}${{ number_format((float) $summary['net_movement'], 2) }}
                </div>
                <div class="kpi-sub">
                    @if ((float) $summary['net_movement'] >= 0)
                        Net Positive Reserve
                    @else
                        Deficit vs. Inflow
                    @endif
                </div>
            </td>
            <td>
                <div class="kpi-label">Savings Rate</div>
                <div class="kpi-value">{{ $summary['savings_rate'] }}%</div>
                <div class="kpi-sub">Preceding: ${{ number_format((float) $summary['prev_net'], 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Category Breakdown --}}
    <div class="section-title">Category-Wise Breakdown</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Type</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">% Share</th>
                <th class="text-center">Count</th>
                <th class="text-right">Avg / Entry</th>
                <th class="text-right">Prior Period</th>
                <th class="text-right">Delta / Change</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($categoryReport['categories'] as $cat)
                <tr>
                    <td><strong>{{ $cat['name'] }}</strong></td>
                    <td>
                        <span class="badge {{ $cat['type'] === 'income' ? 'badge-income' : 'badge-expense' }}">
                            {{ $cat['type'] }}
                        </span>
                    </td>
                    <td class="text-right font-mono">${{ number_format((float) $cat['spent'], 2) }}</td>
                    <td class="text-right font-mono">{{ $cat['percentage_of_total'] }}%</td>
                    <td class="text-center font-mono">{{ $cat['count'] }}</td>
                    <td class="text-right font-mono">${{ number_format((float) $cat['average_amount'], 2) }}</td>
                    <td class="text-right font-mono">${{ number_format((float) $cat['prev_spent'], 2) }}</td>
                    <td class="text-right font-mono {{ $cat['direction'] === 'increased' ? 'text-expense' : ($cat['direction'] === 'decreased' ? 'text-income' : '') }}">
                        @if ($cat['is_new'])
                            <span style="color: #d97706; font-weight: bold;">New</span>
                        @else
                            {{ (float) $cat['delta'] > 0 ? '+' : '' }}${{ number_format((float) $cat['delta'], 2) }}
                            ({{ $cat['pct_formatted'] }})
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 14px; color: #64748b;">
                        No expense records found for the selected parameters.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if (!empty($categoryReport['categories']))
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: 700;">
                    <td colspan="2">Total Expense Volume</td>
                    <td class="text-right font-mono">${{ number_format((float) $categoryReport['total_spent'], 2) }}</td>
                    <td class="text-right font-mono">100.0%</td>
                    <td class="text-center font-mono">{{ $summary['expense_count'] }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- Transactions Detail --}}
    <div class="section-title">Verified Ledger Activity ({{ $transactions->count() }} Entries)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Merchant / Description</th>
                <th>Category</th>
                <th>Type</th>
                <th>Method</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td class="font-mono">{{ $t->transaction_date->format('Y-m-d') }}</td>
                    <td>
                        <strong>{{ $t->merchant }}</strong>
                        @if ($t->description)
                            <br><span style="color: #64748b; font-size: 8px;">{{ $t->description }}</span>
                        @endif
                    </td>
                    <td>{{ $t->category?->name ?? 'Uncategorized' }}</td>
                    <td>
                        <span class="badge {{ $t->isIncome() ? 'badge-income' : 'badge-expense' }}">
                            {{ $t->type }}
                        </span>
                    </td>
                    <td style="text-transform: capitalize;">{{ $t->payment_method }}</td>
                    <td class="text-right font-mono {{ $t->isIncome() ? 'text-income' : 'text-expense' }}">
                        {{ $t->isIncome() ? '+' : '-' }}${{ number_format((float) $t->amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 14px; color: #64748b;">
                        No transactions recorded matching the selected filter criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        Campus Coin Student Personal Finance &bull; Generated electronically for {{ $user->email }} &bull; Page 1 &bull; Strictly Confidential
    </div>

</body>
</html>
