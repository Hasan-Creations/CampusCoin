<?php

namespace App\Livewire\Student;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\FinancialCalculationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Active time period for category spending comparison and analytics.
     * Allowed: 'this_month', 'last_3_months', 'last_6_months', 'year'.
     */
    public string $timePeriod = 'this_month';

    /**
     * Set the active time period filter dynamically.
     */
    public function setTimePeriod(string $period): void
    {
        if (in_array($period, ['this_month', 'last_3_months', 'last_6_months', 'year'], true)) {
            $this->timePeriod = $period;
        }
    }

    /**
     * Render the live dashboard with real financial KPIs, 6-month cash flow trends, and comparative analytics.
     */
    public function render(FinancialCalculationService $calculationService)
    {
        $userId = Auth::id();
        $user = Auth::user();
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $currentMonthYear = $now->format('Y-m');

        // --- Current Month Aggregates ---
        $monthlyIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthlyExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $netBalance = bcsub((string) $monthlyIncome, (string) $monthlyExpense, 2);
        $savedAmount = max(0, (float) $netBalance);

        // --- All-time Totals ---
        $allTimeIncome = Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount');
        $allTimeExpense = Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $totalCount = Transaction::where('user_id', $userId)->count();

        // --- Top Spending Category this Month ---
        $topCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        // --- Category Breakdown for current month expenses ---
        $expenseBreakdown = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->limit(5)
            ->get();

        // --- Recent Transactions ---
        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // --- Savings Goal Progress ---
        $savingsGoal = (float) ($user->savings_goal ?? 0);
        $savingsProgress = $savingsGoal > 0
            ? min(100, round(($savedAmount / $savingsGoal) * 100, 1))
            : 0;

        // --- Safe-to-Spend (remaining allowance for the month) ---
        $allowance = (float) ($user->monthly_allowance ?? 0);
        $safeToSpend = max(0, $allowance - (float) $monthlyExpense);

        // --- Month-over-month change (last month) ---
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $lastMonthExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $expenseDelta = $lastMonthExpense > 0
            ? round((((float) $monthlyExpense - (float) $lastMonthExpense) / (float) $lastMonthExpense) * 100, 1)
            : null;

        // --- Current Month Budget Goals & Alerts ---
        $expensesByCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->groupBy('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->pluck('total', 'category_id')
            ->map(fn ($val) => number_format((float) $val, 2, '.', ''));

        $budgets = Budget::where('user_id', $userId)
            ->where('month_year', $currentMonthYear)
            ->with('category')
            ->get();

        $totalBudgeted = '0.00';
        $decoratedBudgets = $budgets->map(function ($b) use ($expensesByCategory, &$totalBudgeted) {
            $spent = $expensesByCategory->get($b->category_id, '0.00');
            $remaining = $b->getRemainingAmount($spent);
            $pct = $b->getPercentageConsumed($spent);
            $status = $b->getStatus($spent);
            $statusLabel = $b->getStatusLabel($spent);
            $badgeClass = $b->getStatusBadgeClass($spent);
            $barColor = $b->getProgressBarColor($spent);

            $totalBudgeted = bcadd($totalBudgeted, (string) $b->amount, 2);

            return [
                'model' => $b,
                'category' => $b->category,
                'limit' => $b->amount,
                'spent' => $spent,
                'remaining' => $remaining,
                'pct' => $pct,
                'status' => $status,
                'statusLabel' => $statusLabel,
                'badgeClass' => $badgeClass,
                'barColor' => $barColor,
            ];
        });

        $overBudgets = $decoratedBudgets->filter(fn ($item) => $item['status'] === 'over_budget');
        $nearLimitBudgets = $decoratedBudgets->filter(fn ($item) => $item['status'] === 'near_limit');

        // --- Six-Month Cash Flow Trends & Advanced Analytics (Phase 3) ---
        $sixMonthTrends = $calculationService->getSixMonthCashFlow($userId, $now);
        $categoryComparisons = $calculationService->getCategoryComparisons($userId, $this->timePeriod, $now);

        return view('livewire.student.dashboard', [
            'user' => $user,
            'monthlyIncome' => (float) $monthlyIncome,
            'monthlyExpense' => (float) $monthlyExpense,
            'netBalance' => (float) $netBalance,
            'savedAmount' => $savedAmount,
            'safeToSpend' => $safeToSpend,
            'savingsGoal' => $savingsGoal,
            'savingsProgress' => $savingsProgress,
            'allowance' => $allowance,
            'allTimeIncome' => (float) $allTimeIncome,
            'allTimeExpense' => (float) $allTimeExpense,
            'totalCount' => $totalCount,
            'topCategory' => $topCategory,
            'expenseBreakdown' => $expenseBreakdown,
            'recentTransactions' => $recentTransactions,
            'expenseDelta' => $expenseDelta,
            'currentMonth' => $now->format('F Y'),
            'currentMonthYear' => $currentMonthYear,
            'decoratedBudgets' => $decoratedBudgets,
            'totalBudgeted' => $totalBudgeted,
            'overBudgets' => $overBudgets,
            'nearLimitBudgets' => $nearLimitBudgets,
            'sixMonthTrends' => $sixMonthTrends,
            'categoryComparisons' => $categoryComparisons,
            'timePeriod' => $this->timePeriod,
        ])->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}
