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
    /**
     * Retrieve aggregate operational platform metrics and usage telemetry.
     *
     * @return array<string, mixed>
     */
    public function getPlatformOverviewMetrics(): array
    {
        // 1. Student Account Telemetry
        $totalStudents = User::where('role', 'student')->count();
        $activeStudents = User::where('role', 'student')->where('status', 'active')->count();
        $disabledStudents = User::where('role', 'student')->where('status', 'disabled')->count();
        $activePercentage = $totalStudents > 0
            ? round(($activeStudents / $totalStudents) * 100, 1)
            : 0.0;

        // 2. Transaction & Volume Telemetry
        $totalTransactions = Transaction::count();
        $totalVolume = Transaction::sum('amount') ?? '0.00';
        $expenseVolume = Transaction::where('type', 'expense')->sum('amount') ?? '0.00';
        $incomeVolume = Transaction::where('type', 'income')->sum('amount') ?? '0.00';
        $expenseCount = Transaction::where('type', 'expense')->count();
        $incomeCount = Transaction::where('type', 'income')->count();
        $avgTransactionAmount = $totalTransactions > 0
            ? (float) (Transaction::avg('amount') ?? 0.00)
            : 0.00;

        // 3. Category System Telemetry
        $totalCategories = Category::count();
        $globalCategories = Category::where('is_default', true)->orWhereNull('user_id')->count();
        $personalCategories = Category::where('is_default', false)->whereNotNull('user_id')->count();
        $activeCategories = Category::where('is_active', true)->count();
        $inactiveCategories = Category::where('is_active', false)->count();

        // 4. Budget & Financial Goals Telemetry
        $totalBudgets = Budget::count();
        $totalBudgetedAmount = Budget::sum('amount') ?? '0.00';
        $uniqueStudentsWithBudgets = Budget::distinct('user_id')->count('user_id');

        // 5. Intelligent Advisory Telemetry
        $totalTipsGenerated = SavingTip::count();
        $pinnedTipsCount = SavingTip::where('status', 'pinned')->count();
        $totalLearnedCorrections = CategoryLearning::count();

        // 6. Most-Used Categories (Top 5 by transaction frequency)
        $mostUsedCategories = Transaction::select('category_id', DB::raw('COUNT(*) as tx_count'), DB::raw('SUM(amount) as total_volume'))
            ->groupBy('category_id')
            ->orderByDesc('tx_count')
            ->limit(5)
            ->with('category')
            ->get()
            ->map(function ($row) use ($totalTransactions) {
                $count = (int) $row->tx_count;
                $pct = $totalTransactions > 0 ? round(($count / $totalTransactions) * 100, 1) : 0.0;

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

        // 7. Student Cohort Distribution
        $cohortDistribution = User::where('role', 'student')
            ->select(DB::raw('COALESCE(academic_year, "Unspecified") as cohort'), DB::raw('COUNT(*) as student_count'))
            ->groupBy('academic_year')
            ->orderByDesc('student_count')
            ->pluck('student_count', 'cohort')
            ->toArray();

        // 8. Recent 30-Day Activity
        $recentThirtyDaysSignups = User::where('role', 'student')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        $recentThirtyDaysTransactions = Transaction::where('transaction_date', '>=', now()->subDays(30)->toDateString())
            ->count();

        // 9. Total Monthly Baseline Commitment
        $totalMonthlyAllowance = User::where('role', 'student')->sum('monthly_allowance') ?? '0.00';
        $totalSavingsGoal = User::where('role', 'student')->sum('savings_goal') ?? '0.00';

        return [
            'students' => [
                'total' => $totalStudents,
                'active' => $activeStudents,
                'disabled' => $disabledStudents,
                'active_percentage' => $activePercentage,
                'recent_signups_30d' => $recentThirtyDaysSignups,
                'total_monthly_allowance' => number_format((float) $totalMonthlyAllowance, 2, '.', ''),
                'total_savings_goal' => number_format((float) $totalSavingsGoal, 2, '.', ''),
                'cohort_distribution' => $cohortDistribution,
            ],
            'transactions' => [
                'total_count' => $totalTransactions,
                'total_volume' => number_format((float) $totalVolume, 2, '.', ''),
                'expense_count' => $expenseCount,
                'expense_volume' => number_format((float) $expenseVolume, 2, '.', ''),
                'income_count' => $incomeCount,
                'income_volume' => number_format((float) $incomeVolume, 2, '.', ''),
                'avg_amount' => number_format($avgTransactionAmount, 2, '.', ''),
                'recent_30d_count' => $recentThirtyDaysTransactions,
            ],
            'categories' => [
                'total' => $totalCategories,
                'global' => $globalCategories,
                'personal' => $personalCategories,
                'active' => $activeCategories,
                'inactive' => $inactiveCategories,
                'most_used' => $mostUsedCategories,
            ],
            'budgets' => [
                'total_budgets' => $totalBudgets,
                'total_budgeted_amount' => number_format((float) $totalBudgetedAmount, 2, '.', ''),
                'participating_students' => $uniqueStudentsWithBudgets,
            ],
            'intelligence' => [
                'total_tips' => $totalTipsGenerated,
                'pinned_tips' => $pinnedTipsCount,
                'learned_corrections' => $totalLearnedCorrections,
            ],
        ];
    }

    /**
     * Retrieve recent registered student accounts for admin dashboard overview.
     *
     * @return Collection<int, User>
     */
    public function getRecentStudentAccounts(int $limit = 5): Collection
    {
        return User::where('role', 'student')
            ->withCount(['transactions', 'budgets'])
            ->latest()
            ->limit($limit)
            ->get();
    }
}
