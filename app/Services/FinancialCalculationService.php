<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialCalculationService
{
    public function getSixMonthCashFlow(int $userId, ?Carbon $referenceDate = null): array
    {
        $ref = $referenceDate ? $referenceDate->copy() : Carbon::now();
        $refMonth = $ref->copy()->startOfMonth();

        $monthDates = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDates[] = $refMonth->copy()->subMonths($i);
        }

        $startDate = $monthDates[0]->copy()->startOfMonth()->toDateString();
        $endDate = $refMonth->copy()->endOfMonth()->toDateString();

        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', transaction_date)"
            : "DATE_FORMAT(transaction_date, '%Y-%m')";

        $rows = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw("{$dateExpr} as month_key, type, SUM(amount) as total")
            ->groupBy('month_key', 'type')
            ->get();

        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[$row->month_key][$row->type] = number_format((float) $row->total, 2, '.', '');
        }

        $months = [];
        $totalIncome = '0.00';
        $totalExpense = '0.00';
        $maxVolume = 100.00;
        $topExpAmt = -1.0;
        $topExpMonth = null;
        $topIncAmt = -1.0;
        $topIncMonth = null;

        $currentMonthKey = $ref->format('Y-m');

        foreach ($monthDates as $m) {
            $key = $m->format('Y-m');
            $income = $byMonth[$key]['income'] ?? '0.00';
            $expense = $byMonth[$key]['expense'] ?? '0.00';
            $net = bcsub($income, $expense, 2);

            $cmp = bccomp($net, '0.00', 2);
            $status = $cmp > 0 ? 'positive' : ($cmp < 0 ? 'negative' : 'balanced');

            $savingsRate = bccomp($income, '0.00', 2) > 0
                ? max(0.0, min(100.0, round(((float) $net / (float) $income) * 100, 1)))
                : 0.0;

            $totalIncome = bcadd($totalIncome, $income, 2);
            $totalExpense = bcadd($totalExpense, $expense, 2);

            if ((float) $income > $maxVolume) {
                $maxVolume = (float) $income;
            }
            if ((float) $expense > $maxVolume) {
                $maxVolume = (float) $expense;
            }

            if ((float) $expense > $topExpAmt && (float) $expense > 0) {
                $topExpAmt = (float) $expense;
                $topExpMonth = $m->format('M Y');
            }
            if ((float) $income > $topIncAmt && (float) $income > 0) {
                $topIncAmt = (float) $income;
                $topIncMonth = $m->format('M Y');
            }

            $months[] = [
                'month_key' => $key,
                'month_label' => $m->format('M Y'),
                'short_label' => $m->format('M'),
                'income' => $income,
                'expense' => $expense,
                'net' => $net,
                'status' => $status,
                'savings_rate' => $savingsRate,
                'is_current' => $key === $currentMonthKey,
            ];
        }

        $totalNet = bcsub($totalIncome, $totalExpense, 2);
        $avgExpense = number_format((float) $totalExpense / 6, 2, '.', '');
        $avgIncome = number_format((float) $totalIncome / 6, 2, '.', '');

        return [
            'months' => $months,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'total_net' => $totalNet,
            'average_monthly_expense' => $avgExpense,
            'average_monthly_income' => $avgIncome,
            'max_volume' => $maxVolume,
            'highest_expense_month' => $topExpMonth,
            'highest_income_month' => $topIncMonth,
        ];
    }

    public function getCategoryComparisons(int $userId, string $period = 'this_month', ?Carbon $referenceDate = null): array
    {
        $ref = $referenceDate ? $referenceDate->copy() : Carbon::now();

        [$currentStart, $currentEnd, $prevStart, $prevEnd, $periodLabel, $comparisonLabel] = $this->resolvePeriodRanges($period, $ref);

        $currExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->groupBy('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->pluck('total', 'category_id');

        $prevExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevStart->toDateString(), $prevEnd->toDateString()])
            ->groupBy('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->pluck('total', 'category_id');

        $catIds = $currExpenses->keys()
            ->merge($prevExpenses->keys())
            ->unique()
            ->filter()
            ->values();

        $currentTotal = '0.00';
        $prevTotal = '0.00';

        foreach ($currExpenses as $amt) {
            $currentTotal = bcadd($currentTotal, (string) $amt, 2);
        }
        foreach ($prevExpenses as $amt) {
            $prevTotal = bcadd($prevTotal, (string) $amt, 2);
        }

        $totalDelta = bcsub($currentTotal, $prevTotal, 2);
        $totalChange = $this->calculateSafePercentageChange($currentTotal, $prevTotal);

        if ($catIds->isEmpty()) {
            return [
                'period_key' => $period,
                'period_label' => $periodLabel,
                'comparison_label' => $comparisonLabel,
                'current_total' => $currentTotal,
                'previous_total' => $prevTotal,
                'total_delta' => $totalDelta,
                'total_change' => $totalChange,
                'categories' => [],
            ];
        }

        $categories = Category::whereIn('id', $catIds)->get()->keyBy('id');

        $rows = [];
        foreach ($catIds as $catId) {
            $cat = $categories->get($catId);
            $current = number_format((float) ($currExpenses->get($catId, 0)), 2, '.', '');
            $prev = number_format((float) ($prevExpenses->get($catId, 0)), 2, '.', '');
            $delta = bcsub($current, $prev, 2);
            $change = $this->calculateSafePercentageChange($current, $prev);

            $sharePct = (float) $currentTotal > 0
                ? round(((float) $current / (float) $currentTotal) * 100, 1)
                : 0.0;

            $rows[] = [
                'category_id' => (int) $catId,
                'name' => $cat?->name ?? 'Uncategorized',
                'color' => $cat?->color ?? '#64748B',
                'icon' => $cat?->icon ?? 'tag',
                'current_spent' => $current,
                'previous_spent' => $prev,
                'delta' => $delta,
                'direction' => $change['direction'],
                'pct_change' => $change['pct'],
                'pct_formatted' => $change['formatted'],
                'is_new' => $change['is_new'],
                'share_pct' => $sharePct,
            ];
        }

        usort($rows, function ($a, $b) {
            $cmp = bccomp((string) $b['current_spent'], (string) $a['current_spent'], 2);
            if ($cmp !== 0) {
                return $cmp;
            }

            return bccomp((string) $b['delta'], (string) $a['delta'], 2);
        });

        return [
            'period_key' => $period,
            'period_label' => $periodLabel,
            'comparison_label' => $comparisonLabel,
            'current_total' => $currentTotal,
            'previous_total' => $prevTotal,
            'total_delta' => $totalDelta,
            'total_change' => $totalChange,
            'categories' => $rows,
        ];
    }

    protected function resolvePeriodRanges(string $period, Carbon $ref): array
    {
        switch ($period) {
            case 'last_3_months':
                $currentEnd = $ref->copy()->endOfMonth();
                $currentStart = $ref->copy()->subMonths(2)->startOfMonth();

                $prevEnd = $currentStart->copy()->subDay()->endOfDay();
                $prevStart = $ref->copy()->subMonths(5)->startOfMonth();

                $periodLabel = 'Last 3 Months';
                $comparisonLabel = 'vs. Prior 3 Months';
                break;

            case 'last_6_months':
                $currentEnd = $ref->copy()->endOfMonth();
                $currentStart = $ref->copy()->subMonths(5)->startOfMonth();

                $prevEnd = $currentStart->copy()->subDay()->endOfDay();
                $prevStart = $ref->copy()->subMonths(11)->startOfMonth();

                $periodLabel = 'Last 6 Months';
                $comparisonLabel = 'vs. Prior 6 Months';
                break;

            case 'year':
                $currentStart = $ref->copy()->startOfYear();
                $currentEnd = $ref->copy()->endOfYear();

                $prevStart = $ref->copy()->subYear()->startOfYear();
                $prevEnd = $ref->copy()->subYear()->endOfYear();

                $periodLabel = 'This Year';
                $comparisonLabel = 'vs. Prior Year';
                break;

            case 'this_month':
            default:
                $currentStart = $ref->copy()->startOfMonth();
                $currentEnd = $ref->copy()->endOfMonth();

                $prevStart = $ref->copy()->subMonth()->startOfMonth();
                $prevEnd = $ref->copy()->subMonth()->endOfMonth();

                $periodLabel = 'This Month';
                $comparisonLabel = 'vs. Last Month';
                break;
        }

        return [$currentStart, $currentEnd, $prevStart, $prevEnd, $periodLabel, $comparisonLabel];
    }

    public function calculateSafePercentageChange(string $current, string $previous): array
    {
        $hasPrev = bccomp($previous, '0.00', 2) > 0;
        $hasCurr = bccomp($current, '0.00', 2) > 0;

        if (! $hasPrev && $hasCurr) {
            return [
                'pct' => 100.0,
                'formatted' => '+100%',
                'is_new' => true,
                'direction' => 'increased',
            ];
        }

        if (! $hasPrev && ! $hasCurr) {
            return [
                'pct' => 0.0,
                'formatted' => '0.0%',
                'is_new' => false,
                'direction' => 'unchanged',
            ];
        }

        $prevFloat = (float) $previous;
        $currFloat = (float) $current;

        $pct = round((($currFloat - $prevFloat) / $prevFloat) * 100, 1);

        if ($pct > 0) {
            return [
                'pct' => $pct,
                'formatted' => '+'.number_format($pct, 1).'%',
                'is_new' => false,
                'direction' => 'increased',
            ];
        }

        if ($pct < 0) {
            return [
                'pct' => $pct,
                'formatted' => number_format($pct, 1).'%',
                'is_new' => false,
                'direction' => 'decreased',
            ];
        }

        return [
            'pct' => 0.0,
            'formatted' => '0.0%',
            'is_new' => false,
            'direction' => 'unchanged',
        ];
    }

    public function getReportSummary(int $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $query = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startStr, $endStr]);

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true)) {
            $query->where('type', $filters['type']);
        }

        $totals = (clone $query)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $totalIncome = number_format((float) ($totals->get('income')->total ?? 0), 2, '.', '');
        $totalExpense = number_format((float) ($totals->get('expense')->total ?? 0), 2, '.', '');
        $incomeCount = (int) ($totals->get('income')->count ?? 0);
        $expenseCount = (int) ($totals->get('expense')->count ?? 0);
        $totalCount = $incomeCount + $expenseCount;

        $netMovement = bcsub($totalIncome, $totalExpense, 2);
        $cmp = bccomp($netMovement, '0.00', 2);
        $status = $cmp > 0 ? 'positive' : ($cmp < 0 ? 'negative' : 'balanced');

        $savingsRate = bccomp($totalIncome, '0.00', 2) > 0 && $cmp > 0
            ? min(100.0, round(((float) $netMovement / (float) $totalIncome) * 100, 1))
            : 0.0;

        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevEnd = $startDate->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($diffDays - 1);

        $prevQuery = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$prevStart->toDateString(), $prevEnd->toDateString()]);

        if (! empty($filters['category_id'])) {
            $prevQuery->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true)) {
            $prevQuery->where('type', $filters['type']);
        }

        $prevTotals = $prevQuery
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $prevIncome = number_format((float) ($prevTotals->get('income')->total ?? 0), 2, '.', '');
        $prevExpense = number_format((float) ($prevTotals->get('expense')->total ?? 0), 2, '.', '');
        $prevNet = bcsub($prevIncome, $prevExpense, 2);

        $expenseDelta = bcsub($totalExpense, $prevExpense, 2);
        $expenseChange = $this->calculateSafePercentageChange($totalExpense, $prevExpense);
        $incomeDelta = bcsub($totalIncome, $prevIncome, 2);
        $incomeChange = $this->calculateSafePercentageChange($totalIncome, $prevIncome);
        $netDelta = bcsub($netMovement, $prevNet, 2);

        return [
            'start_date' => $startStr,
            'end_date' => $endStr,
            'period_label' => $startDate->format('M d, Y').' – '.$endDate->format('M d, Y'),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_movement' => $netMovement,
            'status' => $status,
            'savings_rate' => $savingsRate,
            'total_count' => $totalCount,
            'income_count' => $incomeCount,
            'expense_count' => $expenseCount,
            'prev_income' => $prevIncome,
            'prev_expense' => $prevExpense,
            'prev_net' => $prevNet,
            'expense_delta' => $expenseDelta,
            'expense_change' => $expenseChange,
            'income_delta' => $incomeDelta,
            'income_change' => $incomeChange,
            'net_delta' => $netDelta,
        ];
    }

    public function getCategoryWiseReport(int $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $targetType = (! empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true))
            ? $filters['type']
            : 'expense';

        $query = Transaction::where('user_id', $userId)
            ->where('type', $targetType)
            ->whereBetween('transaction_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $currRows = $query
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $diffDays = $startDate->diffInDays($endDate) + 1;
        $prevEnd = $startDate->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($diffDays - 1);

        $prevQuery = Transaction::where('user_id', $userId)
            ->where('type', $targetType)
            ->whereBetween('transaction_date', [$prevStart->toDateString(), $prevEnd->toDateString()]);

        if (! empty($filters['category_id'])) {
            $prevQuery->where('category_id', $filters['category_id']);
        }

        $prevRows = $prevQuery
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $catIds = $currRows->keys()->merge($prevRows->keys())->unique()->filter()->values();

        $totalSpent = '0.00';
        foreach ($currRows as $r) {
            $totalSpent = bcadd($totalSpent, (string) $r->total, 2);
        }

        if ($catIds->isEmpty()) {
            return [
                'total_spent' => $totalSpent,
                'category_count' => 0,
                'categories' => [],
            ];
        }

        $categories = Category::whereIn('id', $catIds)->get()->keyBy('id');

        $rows = [];
        foreach ($catIds as $catId) {
            $cat = $categories->get($catId);
            $spent = number_format((float) ($currRows->get($catId)->total ?? 0), 2, '.', '');
            $count = (int) ($currRows->get($catId)->count ?? 0);
            $avg = $count > 0 ? number_format((float) $spent / $count, 2, '.', '') : '0.00';

            $prevSpent = number_format((float) ($prevRows->get($catId)->total ?? 0), 2, '.', '');
            $delta = bcsub($spent, $prevSpent, 2);
            $change = $this->calculateSafePercentageChange($spent, $prevSpent);

            $sharePct = (float) $totalSpent > 0
                ? round(((float) $spent / (float) $totalSpent) * 100, 1)
                : 0.0;

            $rows[] = [
                'category_id' => (int) $catId,
                'name' => $cat?->name ?? 'Uncategorized',
                'color' => $cat?->color ?? '#64748B',
                'icon' => $cat?->icon ?? 'tag',
                'type' => $cat?->type ?? $targetType,
                'spent' => $spent,
                'count' => $count,
                'average_amount' => $avg,
                'percentage_of_total' => $sharePct,
                'prev_spent' => $prevSpent,
                'delta' => $delta,
                'direction' => $change['direction'],
                'pct_change' => $change['pct'],
                'pct_formatted' => $change['formatted'],
                'is_new' => $change['is_new'],
            ];
        }

        usort($rows, function ($a, $b) {
            $cmp = bccomp((string) $b['spent'], (string) $a['spent'], 2);
            if ($cmp !== 0) {
                return $cmp;
            }

            return $b['count'] <=> $a['count'];
        });

        return [
            'total_spent' => $totalSpent,
            'category_count' => count($rows),
            'categories' => $rows,
        ];
    }

    public function getCurrentMonthDailySummary(int $userId, ?Carbon $referenceDate = null, array $filters = []): array
    {
        $ref = $referenceDate ? $referenceDate->copy() : Carbon::now();
        $start = $ref->copy()->startOfMonth()->toDateString();
        $end = $ref->copy()->endOfMonth()->toDateString();

        $query = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$start, $end]);

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true)) {
            $query->where('type', $filters['type']);
        }

        $driver = DB::connection()->getDriverName();
        $dayExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m-%d', transaction_date)"
            : "DATE_FORMAT(transaction_date, '%Y-%m-%d')";

        $rows = $query->selectRaw("{$dayExpr} as day_date, type, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('day_date', 'type')
            ->orderBy('day_date', 'desc')
            ->get();

        $lookup = [];
        foreach ($rows as $r) {
            $lookup[$r->day_date][$r->type] = [
                'total' => number_format((float) $r->total, 2, '.', ''),
                'count' => (int) $r->count,
            ];
        }

        $days = [];
        foreach ($lookup as $dayDate => $types) {
            $inc = $types['income']['total'] ?? '0.00';
            $incCount = $types['income']['count'] ?? 0;
            $exp = $types['expense']['total'] ?? '0.00';
            $expCount = $types['expense']['count'] ?? 0;

            $net = bcsub($inc, $exp, 2);
            $cmp = bccomp($net, '0.00', 2);
            $status = $cmp > 0 ? 'positive' : ($cmp < 0 ? 'negative' : 'balanced');

            $cDate = Carbon::parse($dayDate);

            $days[] = [
                'date' => $dayDate,
                'formatted_date' => $cDate->format('M d, D'),
                'income' => $inc,
                'expense' => $exp,
                'net' => $net,
                'status' => $status,
                'count' => $incCount + $expCount,
            ];
        }

        usort($days, fn ($a, $b) => strcmp($b['date'], $a['date']));

        return [
            'month_label' => $ref->format('F Y'),
            'total_days_active' => count($days),
            'days' => $days,
        ];
    }

    public function getCurrentMonthWeeklySummary(int $userId, ?Carbon $referenceDate = null, array $filters = []): array
    {
        $ref = $referenceDate ? $referenceDate->copy() : Carbon::now();
        $daysInMonth = $ref->copy()->daysInMonth;

        $weekRanges = [
            1 => [1, 7],
            2 => [8, 14],
            3 => [15, 21],
            4 => [22, 28],
            5 => [29, $daysInMonth],
        ];

        $weeks = [];

        foreach ($weekRanges as $wNum => [$startDay, $endDay]) {
            if ($startDay > $daysInMonth) {
                continue;
            }

            $wStart = $ref->copy()->day($startDay)->startOfDay();
            $wEnd = $ref->copy()->day(min($endDay, $daysInMonth))->endOfDay();

            $wStartStr = $wStart->toDateString();
            $wEndStr = $wEnd->toDateString();

            $query = Transaction::where('user_id', $userId)
                ->whereBetween('transaction_date', [$wStartStr, $wEndStr]);

            if (! empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }
            if (! empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true)) {
                $query->where('type', $filters['type']);
            }

            $totals = $query->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->keyBy('type');

            $inc = number_format((float) ($totals->get('income')->total ?? 0), 2, '.', '');
            $exp = number_format((float) ($totals->get('expense')->total ?? 0), 2, '.', '');
            $incCount = (int) ($totals->get('income')->count ?? 0);
            $expCount = (int) ($totals->get('expense')->count ?? 0);

            $net = bcsub($inc, $exp, 2);
            $cmp = bccomp($net, '0.00', 2);
            $status = $cmp > 0 ? 'positive' : ($cmp < 0 ? 'negative' : 'balanced');

            $weeks[] = [
                'week_number' => $wNum,
                'label' => "Week {$wNum} ({$ref->format('M')} ".sprintf('%02d', $startDay).' - '.sprintf('%02d', min($endDay, $daysInMonth)).')',
                'start_date' => $wStartStr,
                'end_date' => $wEndStr,
                'income' => $inc,
                'expense' => $exp,
                'net' => $net,
                'status' => $status,
                'count' => $incCount + $expCount,
            ];
        }

        return [
            'month_label' => $ref->format('F Y'),
            'weeks' => $weeks,
        ];
    }
}
