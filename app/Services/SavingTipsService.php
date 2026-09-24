<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingTip;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class SavingTipsService
{
    /**
     * Minimum dollar difference required to trigger category_above_average rule.
     */
    public const MIN_DOLLAR_DIFF_ABOVE_AVERAGE = '15.00';

    /**
     * Minimum total monthly expenses required before high_spending_share evaluates.
     */
    public const MIN_TOTAL_EXPENSES_FOR_SHARE = '50.00';

    /**
     * Minimum dollar delta required to trigger month_over_month_growth rule.
     */
    public const MIN_DELTA_MOM_GROWTH = '50.00';

    /**
     * Evaluate all deterministic saving tip rules for a given user without mutating database.
     *
     * @return array<int, array{
     *     rule_key: string,
     *     category_id: ?int,
     *     title: string,
     *     message: string,
     *     suggestion: string,
     *     trigger_data: array,
     *     estimated_savings: string
     * }>
     */
    public function evaluate(User $user, ?Carbon $referenceDate = null): array
    {
        $now = $referenceDate ? $referenceDate->copy() : Carbon::now();

        $tips = array_merge(
            $this->evaluateCategoryAboveAverage($user, $now),
            $this->evaluateCategoryBudgetAlert($user, $now),
            $this->evaluateHighSpendingShare($user, $now),
            $this->evaluateMonthOverMonthGrowth($user, $now),
            $this->evaluateSavingsGoalLagging($user, $now)
        );

        // Rank generated tips by calculated potential savings impact descending
        usort($tips, function ($a, $b) {
            return bccomp((string) $b['estimated_savings'], (string) $a['estimated_savings'], 2);
        });

        return $tips;
    }

    /**
     * Evaluate rules and synchronize with the saving_tips table.
     * Preserves pinned and dismissed statuses while updating live metrics.
     *
     * @return Collection<int, SavingTip>
     */
    public function syncTips(User $user, ?Carbon $referenceDate = null): Collection
    {
        $candidateTips = $this->evaluate($user, $referenceDate);
        $matchedTipIds = [];

        foreach ($candidateTips as $tipData) {
            $existing = SavingTip::where('user_id', $user->id)
                ->where('rule_key', $tipData['rule_key'])
                ->when(
                    $tipData['category_id'] !== null,
                    fn ($q) => $q->where('category_id', $tipData['category_id']),
                    fn ($q) => $q->whereNull('category_id')
                )
                ->first();

            if ($existing) {
                $existing->update([
                    'title' => $tipData['title'],
                    'message' => $tipData['message'],
                    'suggestion' => $tipData['suggestion'],
                    'trigger_data' => $tipData['trigger_data'],
                    'estimated_savings' => $tipData['estimated_savings'],
                ]);
                $matchedTipIds[] = $existing->id;
            } else {
                $created = SavingTip::create([
                    'user_id' => $user->id,
                    'rule_key' => $tipData['rule_key'],
                    'category_id' => $tipData['category_id'],
                    'title' => $tipData['title'],
                    'message' => $tipData['message'],
                    'suggestion' => $tipData['suggestion'],
                    'trigger_data' => $tipData['trigger_data'],
                    'estimated_savings' => $tipData['estimated_savings'],
                    'status' => 'active',
                ]);
                $matchedTipIds[] = $created->id;
            }
        }

        // Clean up active unpinned tips that are no longer triggered by current ledger data
        SavingTip::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNotIn('id', $matchedTipIds)
            ->delete();

        return SavingTip::where('user_id', $user->id)
            ->with('category')
            ->orderByDesc('estimated_savings')
            ->get();
    }

    /**
     * Rule 1: Category spending significantly above historical average (>20% over 3-month average, with min dollar diff).
     *
     * @return array<int, array{rule_key: string, category_id: int, title: string, message: string, suggestion: string, trigger_data: array, estimated_savings: string}>
     */
    public function evaluateCategoryAboveAverage(User $user, Carbon $now): array
    {
        $tips = [];

        $currentMonthStart = $now->copy()->startOfMonth()->toDateString();
        $currentMonthEnd = $now->copy()->endOfMonth()->toDateString();

        // 3 preceding calendar months (e.g. if now is October, months July, August, September)
        $historyStart = $now->copy()->subMonths(3)->startOfMonth()->toDateString();
        $historyEnd = $now->copy()->subMonths(1)->endOfMonth()->toDateString();

        // Current month expense spending per category
        $currentExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        if ($currentExpenses->isEmpty()) {
            return [];
        }

        // 3-month historical expense spending per category
        $historicalExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$historyStart, $historyEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $categories = Category::whereIn('id', $currentExpenses->keys())->get()->keyBy('id');

        foreach ($currentExpenses as $categoryId => $currentSpentRaw) {
            $cat = $categories->get($categoryId);
            if (! $cat) {
                continue;
            }

            $currentSpent = number_format((float) $currentSpentRaw, 2, '.', '');
            $historyTotalRaw = $historicalExpenses->get($categoryId, 0);

            // Need positive historical baseline to establish an average
            if ((float) $historyTotalRaw <= 0) {
                continue;
            }

            // 3-month average
            $historicalAvg = bcdiv(number_format((float) $historyTotalRaw, 2, '.', ''), '3', 2);
            if (bccomp($historicalAvg, '0.00', 2) <= 0) {
                continue;
            }

            // 20% over threshold: historicalAvg * 1.20
            $threshold = bcmul($historicalAvg, '1.20', 2);
            $dollarDiff = bcsub($currentSpent, $historicalAvg, 2);

            // Condition: > 20% above average AND dollarDiff >= min dollar threshold
            if (bccomp($currentSpent, $threshold, 2) > 0 && bccomp($dollarDiff, self::MIN_DOLLAR_DIFF_ABOVE_AVERAGE, 2) >= 0) {
                $pctIncrease = round((((float) $currentSpent - (float) $historicalAvg) / (float) $historicalAvg) * 100, 1);
                $estimatedSavings = $dollarDiff;

                $tips[] = [
                    'rule_key' => 'category_above_average',
                    'category_id' => $cat->id,
                    'title' => "{$cat->name}: Spending is +{$pctIncrease}% above 3-month average",
                    'message' => "You have spent \${$currentSpent} on {$cat->name} this month, which is \${$dollarDiff} ({$pctIncrease}%) higher than your 3-month average of \${$historicalAvg}.",
                    'suggestion' => "Pacing your {$cat->name} spending back toward your typical \${$historicalAvg}/mo could save you up to \${$estimatedSavings}.",
                    'trigger_data' => [
                        'current_spending' => $currentSpent,
                        'historical_average' => $historicalAvg,
                        'percentage_increase' => $pctIncrease,
                        'delta' => $dollarDiff,
                    ],
                    'estimated_savings' => $estimatedSavings,
                ];
            }
        }

        return $tips;
    }

    /**
     * Rule 2: Category approaching or exceeding monthly budget (>=80% or >100% of budget).
     *
     * @return array<int, array{rule_key: string, category_id: int, title: string, message: string, suggestion: string, trigger_data: array, estimated_savings: string}>
     */
    public function evaluateCategoryBudgetAlert(User $user, Carbon $now): array
    {
        $tips = [];
        $currentMonthYear = $now->format('Y-m');
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();

        $budgets = Budget::where('user_id', $user->id)
            ->where('month_year', $currentMonthYear)
            ->with('category')
            ->get();

        if ($budgets->isEmpty()) {
            return [];
        }

        $expenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        foreach ($budgets as $budget) {
            $limit = number_format((float) $budget->amount, 2, '.', '');
            if (bccomp($limit, '0.00', 2) <= 0) {
                continue;
            }

            $cat = $budget->category;
            $catName = $cat ? $cat->name : 'Category';
            $spent = number_format((float) $expenses->get($budget->category_id, 0), 2, '.', '');
            $pctConsumed = round(((float) $spent / (float) $limit) * 100, 1);

            // Subcase 1: Over-budget (> 100%)
            if (bccomp($spent, $limit, 2) > 0) {
                $overrun = bcsub($spent, $limit, 2);
                $estimatedSavings = $overrun;

                $tips[] = [
                    'rule_key' => 'category_budget_alert',
                    'category_id' => $budget->category_id,
                    'title' => "{$catName}: Exceeded monthly budget cap by \${$overrun}",
                    'message' => "You have spent \${$spent} on {$catName}, exceeding your \${$limit} budget cap by \${$overrun} ({$pctConsumed}% consumed).",
                    'suggestion' => "Pause non-essential purchases in {$catName} for the rest of the month to eliminate this \${$overrun} overrun.",
                    'trigger_data' => [
                        'status' => 'exceeded',
                        'budget_limit' => $limit,
                        'current_spent' => $spent,
                        'overrun' => $overrun,
                        'percent_consumed' => $pctConsumed,
                    ],
                    'estimated_savings' => $estimatedSavings,
                ];
            }
            // Subcase 2: Approaching budget (>= 80% and <= 100%)
            elseif ($pctConsumed >= 80.0) {
                $remaining = bcsub($limit, $spent, 2);
                // Potential savings protects the remaining cap before exhaustion, with minimum 10% of budget
                $minBuffer = bcmul($limit, '0.10', 2);
                $estimatedSavings = bccomp($remaining, $minBuffer, 2) > 0 ? $remaining : $minBuffer;

                $tips[] = [
                    'rule_key' => 'category_budget_alert',
                    'category_id' => $budget->category_id,
                    'title' => "{$catName}: Nearing monthly budget cap ({$pctConsumed}% consumed)",
                    'message' => "You have spent \${$spent} of your \${$limit} budget on {$catName}, leaving \${$remaining} remaining.",
                    'suggestion' => "Slow down {$catName} spending to protect your remaining \${$remaining} and stay within your planned cap.",
                    'trigger_data' => [
                        'status' => 'approaching',
                        'budget_limit' => $limit,
                        'current_spent' => $spent,
                        'remaining' => $remaining,
                        'percent_consumed' => $pctConsumed,
                    ],
                    'estimated_savings' => $estimatedSavings,
                ];
            }
        }

        return $tips;
    }

    /**
     * Rule 3: High share of total spending (single category taking >40% of all expenses).
     *
     * @return array<int, array{rule_key: string, category_id: int, title: string, message: string, suggestion: string, trigger_data: array, estimated_savings: string}>
     */
    public function evaluateHighSpendingShare(User $user, Carbon $now): array
    {
        $tips = [];
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();

        $expenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $totalExpenses = '0.00';
        foreach ($expenses as $amt) {
            $totalExpenses = bcadd($totalExpenses, number_format((float) $amt, 2, '.', ''), 2);
        }

        // Only evaluate if overall spending exceeds minimum baseline
        if (bccomp($totalExpenses, self::MIN_TOTAL_EXPENSES_FOR_SHARE, 2) < 0) {
            return [];
        }

        $categories = Category::whereIn('id', $expenses->keys())->get()->keyBy('id');

        foreach ($expenses as $categoryId => $amtRaw) {
            $cat = $categories->get($categoryId);
            if (! $cat) {
                continue;
            }

            $catSpent = number_format((float) $amtRaw, 2, '.', '');
            $sharePct = round(((float) $catSpent / (float) $totalExpenses) * 100, 1);

            if ($sharePct > 40.0) {
                // Potential savings calculated as reduction toward healthy 40% threshold
                $target40Pct = bcmul($totalExpenses, '0.40', 2);
                $excess = bcsub($catSpent, $target40Pct, 2);
                $estimatedSavings = bccomp($excess, '10.00', 2) >= 0 ? $excess : bcmul($catSpent, '0.10', 2);

                $tips[] = [
                    'rule_key' => 'high_spending_share',
                    'category_id' => $cat->id,
                    'title' => "{$cat->name}: Dominates {$sharePct}% of your total spending",
                    'message' => "Spending on {$cat->name} (\${$catSpent}) accounts for {$sharePct}% of your total \${$totalExpenses} outflow this month.",
                    'suggestion' => "Rebalancing {$cat->name} closer to a 40% share could preserve up to \${$estimatedSavings} in discretionary funds.",
                    'trigger_data' => [
                        'category_spent' => $catSpent,
                        'total_expenses' => $totalExpenses,
                        'share_percent' => $sharePct,
                        'target_40_percent' => $target40Pct,
                        'excess' => $excess,
                    ],
                    'estimated_savings' => $estimatedSavings,
                ];
            }
        }

        return $tips;
    }

    /**
     * Rule 4: Month-over-month overall spending growth (>25% increase with >$50 delta).
     *
     * @return array<int, array{rule_key: string, category_id: null, title: string, message: string, suggestion: string, trigger_data: array, estimated_savings: string}>
     */
    public function evaluateMonthOverMonthGrowth(User $user, Carbon $now): array
    {
        $currentMonthStart = $now->copy()->startOfMonth()->toDateString();
        $currentMonthEnd = $now->copy()->endOfMonth()->toDateString();

        $prevMonthStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $prevMonthEnd = $now->copy()->subMonth()->endOfMonth()->toDateString();

        $currentExpenseRaw = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');

        $prevExpenseRaw = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevMonthStart, $prevMonthEnd])
            ->sum('amount');

        $currentExpenses = number_format((float) $currentExpenseRaw, 2, '.', '');
        $prevExpenses = number_format((float) $prevExpenseRaw, 2, '.', '');

        // Need previous month baseline
        if (bccomp($prevExpenses, '0.00', 2) <= 0) {
            return [];
        }

        $delta = bcsub($currentExpenses, $prevExpenses, 2);

        // Condition: delta > $50.00
        if (bccomp($delta, self::MIN_DELTA_MOM_GROWTH, 2) <= 0) {
            return [];
        }

        $growthPct = round((((float) $currentExpenses - (float) $prevExpenses) / (float) $prevExpenses) * 100, 1);

        // Condition: growth > 25%
        if ($growthPct <= 25.0) {
            return [];
        }

        $estimatedSavings = $delta;

        return [
            [
                'rule_key' => 'month_over_month_growth',
                'category_id' => null,
                'title' => "Overall spending is up {$growthPct}% compared to last month",
                'message' => "Your monthly expenses have reached \${$currentExpenses}, an increase of \${$delta} ({$growthPct}%) compared to last month's \${$prevExpenses}.",
                'suggestion' => "Curtailing discretionary purchases to match last month's spending pace could save you \${$estimatedSavings}.",
                'trigger_data' => [
                    'current_expenses' => $currentExpenses,
                    'previous_expenses' => $prevExpenses,
                    'percentage_growth' => $growthPct,
                    'delta' => $delta,
                ],
                'estimated_savings' => $estimatedSavings,
            ],
        ];
    }

    /**
     * Rule 5: Savings goal lagging (current savings / net balance falling behind user's monthly savings_goal).
     *
     * @return array<int, array{rule_key: string, category_id: null, title: string, message: string, suggestion: string, trigger_data: array, estimated_savings: string}>
     */
    public function evaluateSavingsGoalLagging(User $user, Carbon $now): array
    {
        $savingsGoal = number_format((float) ($user->savings_goal ?? 0), 2, '.', '');

        if (bccomp($savingsGoal, '0.00', 2) <= 0) {
            return [];
        }

        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();

        $incomeRaw = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $expenseRaw = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $netBalance = bcsub(
            number_format((float) $incomeRaw, 2, '.', ''),
            number_format((float) $expenseRaw, 2, '.', ''),
            2
        );

        $savedAmount = max(0.0, (float) $netBalance);
        $savedAmountStr = number_format($savedAmount, 2, '.', '');

        $deficit = bcsub($savingsGoal, $savedAmountStr, 2);

        // Condition: current savings is less than savings_goal
        if (bccomp($deficit, '0.00', 2) <= 0) {
            return [];
        }

        $progressPct = round(($savedAmount / (float) $savingsGoal) * 100, 1);
        $estimatedSavings = $deficit;

        return [
            [
                'rule_key' => 'savings_goal_lagging',
                'category_id' => null,
                'title' => "Monthly savings target is lagging by \${$deficit}",
                'message' => "You have saved \${$savedAmountStr} toward your \${$savingsGoal} monthly goal ({$progressPct}% achieved), leaving a \${$deficit} gap.",
                'suggestion' => "Trimming non-essential dining or entertainment purchases could close this \${$deficit} deficit before month end.",
                'trigger_data' => [
                    'savings_goal' => $savingsGoal,
                    'current_savings' => $savedAmountStr,
                    'deficit' => $deficit,
                    'progress_percent' => $progressPct,
                ],
                'estimated_savings' => $estimatedSavings,
            ],
        ];
    }
}
