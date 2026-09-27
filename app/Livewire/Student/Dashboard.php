<?php

namespace App\Livewire\Student;

use App\Models\Budget;
use App\Models\SavingTip;
use App\Models\SystemTipTemplate;
use App\Models\Transaction;
use App\Services\FinancialCalculationService;
use App\Services\SavingTipsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public string $timePeriod = 'this_month';

    public function setTimePeriod(string $period): void
    {
        if (in_array($period, ['this_month', 'last_3_months', 'last_6_months', 'year'], true)) {
            $this->timePeriod = $period;
        }
    }

    public function pinTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->pin();
    }

    public function unpinTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->unpin();
    }

    public function dismissTip(int $tipId): void
    {
        $tip = SavingTip::where('user_id', Auth::id())->findOrFail($tipId);
        $tip->dismiss();
    }

    public function render(FinancialCalculationService $calculationService, SavingTipsService $savingTipsService)
    {
        $userId = Auth::id();
        $user = Auth::user();
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $currentMonthYear = $now->format('Y-m');

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

        $allTimeIncome = Transaction::where('user_id', $userId)->where('type', 'income')->sum('amount');
        $allTimeExpense = Transaction::where('user_id', $userId)->where('type', 'expense')->sum('amount');
        $totalCount = Transaction::where('user_id', $userId)->count();

        $topCategory = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->first();

        $expenseBreakdown = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->with('category')
            ->limit(5)
            ->get();

        $recentTransactions = Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        $savingsGoal = (float) ($user->savings_goal ?? 0);
        $savingsProgress = $savingsGoal > 0
            ? min(100, round(($savedAmount / $savingsGoal) * 100, 1))
            : 0;

        $allowance = (float) ($user->monthly_allowance ?? 0);
        $safeToSpend = max(0, $allowance - (float) $monthlyExpense);

        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $lastMonthExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $expenseDelta = $lastMonthExpense > 0
            ? round((((float) $monthlyExpense - (float) $lastMonthExpense) / (float) $lastMonthExpense) * 100, 1)
            : null;

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

        $sixMonthTrends = $calculationService->getSixMonthCashFlow($userId, $now);
        $categoryComparisons = $calculationService->getCategoryComparisons($userId, $this->timePeriod, $now);

        if ($user && SavingTip::where('user_id', $userId)->count() === 0) {
            $savingTipsService->syncTips($user, $now);
        }
        $topSavingTips = SavingTip::where('user_id', $userId)
            ->whereIn('status', ['active', 'pinned'])
            ->with('category')
            ->orderByRaw("CASE WHEN status = 'pinned' THEN 0 ELSE 1 END")
            ->orderByDesc('estimated_savings')
            ->limit(3)
            ->get();

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
            'topSavingTips' => $topSavingTips,
            'systemTemplates' => SystemTipTemplate::where('is_active', true)->orderBy('type')->get(),
        ])->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}
