<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\CategoryLearning;
use App\Models\SavingTip;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminMetricsService
{
    public function getPlatformOverviewMetrics(): array
    {
        $studentsCount = User::where('role', 'student')->count();
        $activeCount = User::where('role', 'student')->where('status', 'active')->count();
        $disabledCount = User::where('role', 'student')->where('status', 'disabled')->count();
        $activePct = $studentsCount > 0
            ? round(($activeCount / $studentsCount) * 100, 1)
            : 0.0;

        $txCount = Transaction::count();
        $totalVol = Transaction::sum('amount') ?? '0.00';
        $expVol = Transaction::where('type', 'expense')->sum('amount') ?? '0.00';
        $incVol = Transaction::where('type', 'income')->sum('amount') ?? '0.00';
        $expCount = Transaction::where('type', 'expense')->count();
        $incCount = Transaction::where('type', 'income')->count();
        $avgAmount = $txCount > 0
            ? (float) (Transaction::avg('amount') ?? 0.00)
            : 0.00;

        $catCount = Category::count();
        $globalCount = Category::where('is_default', true)->orWhereNull('user_id')->count();
        $personalCount = Category::where('is_default', false)->whereNotNull('user_id')->count();
        $activeCats = Category::where('is_active', true)->count();
        $inactiveCats = Category::where('is_active', false)->count();

        $budgetCount = Budget::count();
        $budgetTotal = Budget::sum('amount') ?? '0.00';
        $studentsWithBudgets = Budget::distinct('user_id')->count('user_id');

        $tipsCount = SavingTip::count();
        $pinnedCount = SavingTip::where('status', 'pinned')->count();
        $correctionsCount = CategoryLearning::count();

        $topCategories = Transaction::select('category_id', DB::raw('COUNT(*) as tx_count'), DB::raw('SUM(amount) as total_volume'))
            ->groupBy('category_id')
            ->orderByDesc('tx_count')
            ->limit(5)
            ->with('category')
            ->get()
            ->map(function ($row) use ($txCount) {
                $count = (int) $row->tx_count;
                $pct = $txCount > 0 ? round(($count / $txCount) * 100, 1) : 0.0;

                return [
                    'category_id' => $row->category_id,
                    'name' => $row->category?->name ?? 'Uncategorized',
                    'type' => $row->category?->type ?? 'expense',
                    'icon' => $row->category?->icon ?? 'tag',
                    'color' => $row->category?->color ?? '#64748B',
                    'is_default' => $row->category?->isDefault() ?? true,
                    'is_active' => $row->category?->is_active ?? true,
                    'count' => $count,
                    'volume' => number_format((float) ($row->total_volume ?? 0.00), 2, '.', ''),
                    'percentage' => $pct,
                ];
            });

        $cohorts = User::where('role', 'student')
            ->select(DB::raw('COALESCE(academic_year, "Unspecified") as cohort'), DB::raw('COUNT(*) as student_count'))
            ->groupBy('academic_year')
            ->orderByDesc('student_count')
            ->pluck('student_count', 'cohort')
            ->toArray();

        $recentSignups = User::where('role', 'student')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $recentTxCount = Transaction::where('transaction_date', '>=', now()->subDays(30)->toDateString())
            ->count();

        $allowanceSum = User::where('role', 'student')->sum('monthly_allowance') ?? '0.00';
        $savingsSum = User::where('role', 'student')->sum('savings_goal') ?? '0.00';

        return [
            'students' => [
                'total' => $studentsCount,
                'active' => $activeCount,
                'disabled' => $disabledCount,
                'active_percentage' => $activePct,
                'recent_signups_30d' => $recentSignups,
                'total_monthly_allowance' => number_format((float) $allowanceSum, 2, '.', ''),
                'total_savings_goal' => number_format((float) $savingsSum, 2, '.', ''),
                'cohort_distribution' => $cohorts,
            ],
            'transactions' => [
                'total_count' => $txCount,
                'total_volume' => number_format((float) $totalVol, 2, '.', ''),
                'expense_count' => $expCount,
                'expense_volume' => number_format((float) $expVol, 2, '.', ''),
                'income_count' => $incCount,
                'income_volume' => number_format((float) $incVol, 2, '.', ''),
                'avg_amount' => number_format($avgAmount, 2, '.', ''),
                'recent_30d_count' => $recentTxCount,
            ],
            'categories' => [
                'total' => $catCount,
                'global' => $globalCount,
                'personal' => $personalCount,
                'active' => $activeCats,
                'inactive' => $inactiveCats,
                'most_used' => $topCategories,
            ],
            'budgets' => [
                'total_budgets' => $budgetCount,
                'total_budgeted_amount' => number_format((float) $budgetTotal, 2, '.', ''),
                'participating_students' => $studentsWithBudgets,
            ],
            'intelligence' => [
                'total_tips' => $tipsCount,
                'pinned_tips' => $pinnedCount,
                'learned_corrections' => $correctionsCount,
            ],
        ];
    }

    public function getRecentStudentAccounts(int $limit = 5): Collection
    {
        return User::where('role', 'student')
            ->withCount(['transactions', 'budgets'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
