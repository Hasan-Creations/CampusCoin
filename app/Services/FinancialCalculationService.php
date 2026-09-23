<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialCalculationService
{
    /**
     * Compute the 6-month cash flow trends for a specific student.
     * Generates a 6-month chronological sequence ending with the reference date.
     *
     * @return array{
     *     months: array<int, array{
     *         month_key: string,
     *         month_label: string,
     *         short_label: string,
     *         income: string,
     *         expense: string,
     *         net: string,
     *         status: string,
     *         savings_rate: float,
     *         is_current: bool
     *     }>,
     *     total_income: string,
     *     total_expense: string,
     *     total_net: string,
     *     average_monthly_expense: string,
     *     average_monthly_income: string,
     *     max_volume: float,
     *     highest_expense_month: ?string,
     *     highest_income_month: ?string
     * }
     */
    public function getSixMonthCashFlow(int $userId, ?Carbon $referenceDate = null): array
    {
        $ref = $referenceDate ? $referenceDate->copy() : Carbon::now();
        $refMonth = $ref->copy()->startOfMonth();

        // 6 calendar months in chronological order (from 5 months ago to current month)
        $monthCarbons = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthCarbons[] = $refMonth->copy()->subMonths($i);
        }

        $startDate = $monthCarbons[0]->copy()->startOfMonth()->toDateString();
        $endDate = $refMonth->copy()->endOfMonth()->toDateString();

        // Aggregate income and expenses by month and type (driver-agnostic)
        $driver = DB::connection()->getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', transaction_date)"
            : "DATE_FORMAT(transaction_date, '%Y-%m')";

        $aggregates = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw("{$dateExpr} as month_key, type, SUM(amount) as total")
            ->groupBy('month_key', 'type')
            ->get();

        $lookup = [];
        foreach ($aggregates as $row) {
            $lookup[$row->month_key][$row->type] = number_format((float) $row->total, 2, '.', '');
        }

        $months = [];
        $totalIncome = '0.00';
        $totalExpense = '0.00';
        $maxVolume = 100.00; // Baseline floor for SVG chart scaling
        $highestExpenseAmount = -1.0;
        $highestExpenseMonth = null;
        $highestIncomeAmount = -1.0;
        $highestIncomeMonth = null;

        $currentMonthKey = $ref->format('Y-m');

        foreach ($monthCarbons as $m) {
            $key = $m->format('Y-m');
            $income = $lookup[$key]['income'] ?? '0.00';
            $expense = $lookup[$key]['expense'] ?? '0.00';
            $net = bcsub($income, $expense, 2);

            $cmp = bccomp($net, '0.00', 2);
            if ($cmp > 0) {
                $status = 'positive';
            } elseif ($cmp < 0) {
                $status = 'negative';
            } else {
                $status = 'balanced';
            }

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

            if ((float) $expense > $highestExpenseAmount && (float) $expense > 0) {
                $highestExpenseAmount = (float) $expense;
                $highestExpenseMonth = $m->format('M Y');
            }
            if ((float) $income > $highestIncomeAmount && (float) $income > 0) {
                $highestIncomeAmount = (float) $income;
                $highestIncomeMonth = $m->format('M Y');
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
            'highest_expense_month' => $highestExpenseMonth,
            'highest_income_month' => $highestIncomeMonth,
        ];
    }

    /**
     * Compute comparative category spending across selected time periods.
     *
     * @return array{
     *     period_key: string,
     *     period_label: string,
     *     comparison_label: string,
     *     current_total: string,
     *     previous_total: string,
     *     total_delta: string,
     *     total_change: array{pct: float, formatted: string, is_new: bool, direction: string},
     *     categories: array<int, array{
     *         category_id: int,
     *         name: string,
     *         color: string,
     *         icon: string,
     *         current_spent: string,
     *         previous_spent: string,
     *         delta: string,
     *         direction: string,
     *         pct_change: float,
     *         pct_formatted: string,
     *         is_new: bool,
     *         share_pct: float
     *     }>
     * }
     */
    public function getCategoryComparisons(int $userId, string $period = 'this_month', ?Carbon $referenceDate = null): array
    {
        $ref = $referenceDate ? $referenceDate->copy() : Carbon::now();

        [$currentStart, $currentEnd, $prevStart, $prevEnd, $periodLabel, $comparisonLabel] = $this->resolvePeriodRanges($period, $ref);

        $currentExpenses = Transaction::where('user_id', $userId)
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

        $allCategoryIds = $currentExpenses->keys()
            ->merge($prevExpenses->keys())
            ->unique()
            ->filter()
            ->values();

        $currentTotal = '0.00';
        $prevTotal = '0.00';

        foreach ($currentExpenses as $amount) {
            $currentTotal = bcadd($currentTotal, (string) $amount, 2);
        }
        foreach ($prevExpenses as $amount) {
            $prevTotal = bcadd($prevTotal, (string) $amount, 2);
        }

        $totalDelta = bcsub($currentTotal, $prevTotal, 2);
        $totalChange = $this->calculateSafePercentageChange($currentTotal, $prevTotal);

        if ($allCategoryIds->isEmpty()) {
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

        $categoriesModels = Category::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        $rows = [];
        foreach ($allCategoryIds as $catId) {
            $cat = $categoriesModels->get($catId);
            $current = number_format((float) ($currentExpenses->get($catId, 0)), 2, '.', '');
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

        // Sort categories by current_spent descending, then delta descending
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

    /**
     * Determine period boundaries and descriptive labels.
     *
     * @return array{0: Carbon, 1: Carbon, 2: Carbon, 3: Carbon, 4: string, 5: string}
     */
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

    /**
     * Compute deterministic percentage change safely guarding against divide-by-zero.
     *
     * @return array{pct: float, formatted: string, is_new: bool, direction: string}
     */
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

    /**
     * Compute comprehensive financial report summary for a custom date range and filters.
     *
     * @param  array{category_id?: ?int, type?: ?string}  $filters
     * @return array{
     *     start_date: string,
     *     end_date: string,
     *     period_label: string,
     *     total_income: string,
     *     total_expense: string,
     *     net_movement: string,
     *     status: string,
     *     savings_rate: float,
     *     total_count: int,
     *     income_count: int,
     *     expense_count: int,
     *     prev_income: string,
     *     prev_expense: string,
     *     prev_net: string,
     *     expense_delta: string,
     *     expense_change: array{pct: float, formatted: string, is_new: bool, direction: string},
     *     income_delta: string,
     *     income_change: array{pct: float, formatted: string, is_new: bool, direction: string},
     *     net_delta: string
     * }
     */
    public function getReportSummary(int $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $startStr = $startDate->toDateString();
        $endStr = $endDate->toDateString();

        $baseQuery = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startStr, $endStr]);

        if (! empty($filters['category_id'])) {
            $baseQuery->where('category_id', $filters['category_id']);
        }
        if (! empty($filters['type']) && in_array($filters['type'], ['income', 'expense'], true)) {
            $baseQuery->where('type', $filters['type']);
        }

        $typeTotals = (clone $baseQuery)
            ->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->keyBy('type');

        $totalIncome = number_format((float) ($typeTotals->get('income')->total ?? 0), 2, '.', '');
        $totalExpense = number_format((float) ($typeTotals->get('expense')->total ?? 0), 2, '.', '');
        $incomeCount = (int) ($typeTotals->get('income')->count ?? 0);
        $expenseCount = (int) ($typeTotals->get('expense')->count ?? 0);
        $totalCount = $incomeCount + $expenseCount;

        $netMovement = bcsub($totalIncome, $totalExpense, 2);
        $cmp = bccomp($netMovement, '0.00', 2);
        $status = $cmp > 0 ? 'positive' : ($cmp < 0 ? 'negative' : 'balanced');

        $savingsRate = bccomp($totalIncome, '0.00', 2) > 0 && $cmp > 0
            ? min(100.0, round(((float) $netMovement / (float) $totalIncome) * 100, 1))
            : 0.0;

        // Preceding equal-length period
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

    /**
     * Compute category-wise breakdown report with volume, counts, averages, and prior-period comparison.
     *
     * @param  array{category_id?: ?int, type?: ?string}  $filters
     * @return array{
     *     total_spent: string,
     *     category_count: int,
     *     categories: array<int, array{
     *         category_id: int,
     *         name: string,
     *         color: string,
     *         icon: string,
     *         type: string,
     *         spent: string,
     *         count: int,
     *         average_amount: string,
     *         percentage_of_total: float,
     *         prev_spent: string,
     *         delta: string,
     *         direction: string,
     *         pct_change: float,
     *         pct_formatted: string,
     *         is_new: bool
     *     }>
     * }
     */
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

        $currentRows = $query
            ->selectRaw('category_id, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        // Preceding period
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

        $allCategoryIds = $currentRows->keys()->merge($prevRows->keys())->unique()->filter()->values();

        $totalSpent = '0.00';
        foreach ($currentRows as $r) {
            $totalSpent = bcadd($totalSpent, (string) $r->total, 2);
        }

        if ($allCategoryIds->isEmpty()) {
            return [
                'total_spent' => $totalSpent,
                'category_count' => 0,
                'categories' => [],
            ];
        }

        $categoryModels = Category::whereIn('id', $allCategoryIds)->get()->keyBy('id');

        $rows = [];
        foreach ($allCategoryIds as $catId) {
            $cat = $categoryModels->get($catId);
            $spent = number_format((float) ($currentRows->get($catId)->total ?? 0), 2, '.', '');
            $count = (int) ($currentRows->get($catId)->count ?? 0);
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

    /**
     * Compute current month daily summary showing actual transaction dates without database fabrication.
     *
     * @param  array{category_id?: ?int, type?: ?string}  $filters
     * @return array{
     *     month_label: string,
     *     total_days_active: int,
     *     days: array<int, array{
     *         date: string,
     *         formatted_date: string,
     *         income: string,
     *         expense: string,
     *         net: string,
     *         status: string,
     *         count: int
     *     }>
     * }
     */
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

        // Sort descending by date
        usort($days, fn ($a, $b) => strcmp($b['date'], $a['date']));

        return [
            'month_label' => $ref->format('F Y'),
            'total_days_active' => count($days),
            'days' => $days,
        ];
    }

    /**
     * Compute current month weekly summary grouped into calendar periods.
     *
     * @param  array{category_id?: ?int, type?: ?string}  $filters
     * @return array{
     *     month_label: string,
     *     weeks: array<int, array{
     *         week_number: int,
     *         label: string,
     *         start_date: string,
     *         end_date: string,
     *         income: string,
     *         expense: string,
     *         net: string,
     *         status: string,
     *         count: int
     *     }>
     * }
     */
    public function getCurrentMonthWeeklySummary(int $userId, ?Carbon $referenceDate = null, array $filters = []): array
    {
        $ref = $referenceDate ? $referenceDate->copy() : Carbon::now();
        $daysInMonth = $ref->copy()->daysInMonth;

        $weekDefinitions = [
            1 => [1, 7],
            2 => [8, 14],
            3 => [15, 21],
            4 => [22, 28],
            5 => [29, $daysInMonth],
        ];

        $weeks = [];

        foreach ($weekDefinitions as $wNum => [$startDay, $endDay]) {
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

            $typeTotals = $query->selectRaw('type, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->keyBy('type');

            $inc = number_format((float) ($typeTotals->get('income')->total ?? 0), 2, '.', '');
            $exp = number_format((float) ($typeTotals->get('expense')->total ?? 0), 2, '.', '');
            $incCount = (int) ($typeTotals->get('income')->count ?? 0);
            $expCount = (int) ($typeTotals->get('expense')->count ?? 0);

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
