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
    public const MIN_DOLLAR_DIFF_ABOVE_AVERAGE = '15.00';

    public const MIN_TOTAL_EXPENSES_FOR_SHARE = '50.00';

    public const MIN_DELTA_MOM_GROWTH = '50.00';

    public function evaluate(User $user, ?Carbon $referenceDate = null): array
    {
        $date = $referenceDate?->copy() ?? now();

        $tips = array_merge(
            $this->evaluateCategoryAboveAverage($user, $date),
            $this->evaluateCategoryBudgetAlert($user, $date),
            $this->evaluateHighSpendingShare($user, $date),
            $this->evaluateMonthOverMonthGrowth($user, $date),
            $this->evaluateSavingsGoalLagging($user, $date)
        );

        usort($tips, fn ($a, $b) => bccomp((string) $b['estimated_savings'], (string) $a['estimated_savings'], 2));

        return $tips;
    }

    public function syncTips(User $user, ?Carbon $referenceDate = null): Collection
    {
        $tips = $this->evaluate($user, $referenceDate);
        $savedIds = [];

        foreach ($tips as $item) {
            $existing = SavingTip::where('user_id', $user->id)
                ->where('rule_key', $item['rule_key'])
                ->when(
                    $item['category_id'] !== null,
                    fn ($q) => $q->where('category_id', $item['category_id']),
                    fn ($q) => $q->whereNull('category_id')
                )
                ->first();

            if ($existing) {
                $existing->update([
                    'title' => $item['title'],
                    'message' => $item['message'],
                    'suggestion' => $item['suggestion'],
                    'trigger_data' => $item['trigger_data'],
                    'estimated_savings' => $item['estimated_savings'],
                ]);
                $savedIds[] = $existing->id;
            } else {
                $created = SavingTip::create([
                    'user_id' => $user->id,
                    'rule_key' => $item['rule_key'],
                    'category_id' => $item['category_id'],
                    'title' => $item['title'],
                    'message' => $item['message'],
                    'suggestion' => $item['suggestion'],
                    'trigger_data' => $item['trigger_data'],
                    'estimated_savings' => $item['estimated_savings'],
                    'status' => 'active',
                ]);
                $savedIds[] = $created->id;
            }
        }

        // Clean up stale unpinned active tips
        SavingTip::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNotIn('id', $savedIds)
            ->delete();

        return SavingTip::where('user_id', $user->id)
            ->with('category')
            ->orderByDesc('estimated_savings')
            ->get();
    }

    public function evaluateCategoryAboveAverage(User $user, Carbon $now): array
    {
        $tips = [];

        $start = $now->copy()->startOfMonth()->toDateString();
        $end = $now->copy()->endOfMonth()->toDateString();

        // 3 preceding calendar months
        $histStart = $now->copy()->subMonths(3)->startOfMonth()->toDateString();
        $histEnd = $now->copy()->subMonths(1)->endOfMonth()->toDateString();

        $currentSpent = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        if ($currentSpent->isEmpty()) {
            return [];
        }

        $history = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$histStart, $histEnd])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $categories = Category::whereIn('id', $currentSpent->keys())->get()->keyBy('id');

        foreach ($currentSpent as $catId => $rawSpent) {
            $cat = $categories->get($catId);
            if (! $cat) {
                continue;
            }

            $spent = number_format((float) $rawSpent, 2, '.', '');
            $rawHist = $history->get($catId, 0);

            if ((float) $rawHist <= 0) {
                continue;
            }

            $avg = bcdiv(number_format((float) $rawHist, 2, '.', ''), '3', 2);
            if (bccomp($avg, '0.00', 2) <= 0) {
                continue;
            }

            $threshold = bcmul($avg, '1.20', 2);
            $diff = bcsub($spent, $avg, 2);

            if (bccomp($spent, $threshold, 2) > 0 && bccomp($diff, self::MIN_DOLLAR_DIFF_ABOVE_AVERAGE, 2) >= 0) {
                $pct = round((((float) $spent - (float) $avg) / (float) $avg) * 100, 1);
                $savings = $diff;

                $tips[] = [
                    'rule_key' => 'category_above_average',
                    'category_id' => $cat->id,
                    'title' => "{$cat->name}: Spending is +{$pct}% above 3-month average",
                    'message' => "You have spent \${$spent} on {$cat->name} this month, which is \${$diff} ({$pct}%) higher than your 3-month average of \${$avg}.",
                    'suggestion' => "Pacing your {$cat->name} spending back toward your typical \${$avg}/mo could save you up to \${$savings}.",
                    'trigger_data' => [
                        'current_spending' => $spent,
                        'historical_average' => $avg,
                        'percentage_increase' => $pct,
                        'delta' => $diff,
                    ],
                    'estimated_savings' => $savings,
                ];
            }
        }

        return $tips;
    }

    public function evaluateCategoryBudgetAlert(User $user, Carbon $now): array
    {
        $tips = [];
        $month = $now->format('Y-m');
        $start = $now->copy()->startOfMonth()->toDateString();
        $end = $now->copy()->endOfMonth()->toDateString();

        $budgets = Budget::where('user_id', $user->id)
            ->where('month_year', $month)
            ->with('category')
            ->get();

        if ($budgets->isEmpty()) {
            return [];
        }

        $expenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        foreach ($budgets as $b) {
            $limit = number_format((float) $b->amount, 2, '.', '');
            if (bccomp($limit, '0.00', 2) <= 0) {
                continue;
            }

            $cat = $b->category;
            $catName = $cat ? $cat->name : 'Category';
            $spent = number_format((float) $expenses->get($b->category_id, 0), 2, '.', '');
            $usedPct = round(((float) $spent / (float) $limit) * 100, 1);

            if (bccomp($spent, $limit, 2) > 0) {
                $overrun = bcsub($spent, $limit, 2);

                $tips[] = [
                    'rule_key' => 'category_budget_alert',
                    'category_id' => $b->category_id,
                    'title' => "{$catName}: Exceeded monthly budget cap by \${$overrun}",
                    'message' => "You have spent \${$spent} on {$catName}, exceeding your \${$limit} budget cap by \${$overrun} ({$usedPct}% consumed).",
                    'suggestion' => "Pause non-essential purchases in {$catName} for the rest of the month to eliminate this \${$overrun} overrun.",
                    'trigger_data' => [
                        'status' => 'exceeded',
                        'budget_limit' => $limit,
                        'current_spent' => $spent,
                        'overrun' => $overrun,
                        'percent_consumed' => $usedPct,
                    ],
                    'estimated_savings' => $overrun,
                ];
            } elseif ($usedPct >= 80.0) {
                $left = bcsub($limit, $spent, 2);
                $minBuffer = bcmul($limit, '0.10', 2);
                $savings = bccomp($left, $minBuffer, 2) > 0 ? $left : $minBuffer;

                $tips[] = [
                    'rule_key' => 'category_budget_alert',
                    'category_id' => $b->category_id,
                    'title' => "{$catName}: Nearing monthly budget cap ({$usedPct}% consumed)",
                    'message' => "You have spent \${$spent} of your \${$limit} budget on {$catName}, leaving \${$left} remaining.",
                    'suggestion' => "Slow down {$catName} spending to protect your remaining \${$left} and stay within your planned cap.",
                    'trigger_data' => [
                        'status' => 'approaching',
                        'budget_limit' => $limit,
                        'current_spent' => $spent,
                        'remaining' => $left,
                        'percent_consumed' => $usedPct,
                    ],
                    'estimated_savings' => $savings,
                ];
            }
        }

        return $tips;
    }

    public function evaluateHighSpendingShare(User $user, Carbon $now): array
    {
        $tips = [];
        $start = $now->copy()->startOfMonth()->toDateString();
        $end = $now->copy()->endOfMonth()->toDateString();

        $expenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $total = '0.00';
        foreach ($expenses as $amt) {
            $total = bcadd($total, number_format((float) $amt, 2, '.', ''), 2);
        }

        if (bccomp($total, self::MIN_TOTAL_EXPENSES_FOR_SHARE, 2) < 0) {
            return [];
        }

        $categories = Category::whereIn('id', $expenses->keys())->get()->keyBy('id');

        foreach ($expenses as $catId => $rawAmt) {
            $cat = $categories->get($catId);
            if (! $cat) {
                continue;
            }

            $spent = number_format((float) $rawAmt, 2, '.', '');
            $share = round(((float) $spent / (float) $total) * 100, 1);

            if ($share > 40.0) {
                $target40Pct = bcmul($total, '0.40', 2);
                $excess = bcsub($spent, $target40Pct, 2);
                $savings = bccomp($excess, '10.00', 2) >= 0 ? $excess : bcmul($spent, '0.10', 2);

                $tips[] = [
                    'rule_key' => 'high_spending_share',
                    'category_id' => $cat->id,
                    'title' => "{$cat->name}: Dominates {$share}% of your total spending",
                    'message' => "Spending on {$cat->name} (\${$spent}) accounts for {$share}% of your total \${$total} outflow this month.",
                    'suggestion' => "Rebalancing {$cat->name} closer to a 40% share could preserve up to \${$savings} in discretionary funds.",
                    'trigger_data' => [
                        'category_spent' => $spent,
                        'total_expenses' => $total,
                        'share_percent' => $share,
                        'target_40_percent' => $target40Pct,
                        'excess' => $excess,
                    ],
                    'estimated_savings' => $savings,
                ];
            }
        }

        return $tips;
    }

    public function evaluateMonthOverMonthGrowth(User $user, Carbon $now): array
    {
        $currentStart = $now->copy()->startOfMonth()->toDateString();
        $currentEnd = $now->copy()->endOfMonth()->toDateString();

        $prevStart = $now->copy()->subMonth()->startOfMonth()->toDateString();
        $prevEnd = $now->copy()->subMonth()->endOfMonth()->toDateString();

        $currentExpenseRaw = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->sum('amount');

        $prevExpenseRaw = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->sum('amount');

        $curr = number_format((float) $currentExpenseRaw, 2, '.', '');
        $prev = number_format((float) $prevExpenseRaw, 2, '.', '');

        if (bccomp($prev, '0.00', 2) <= 0) {
            return [];
        }

        $delta = bcsub($curr, $prev, 2);

        if (bccomp($delta, self::MIN_DELTA_MOM_GROWTH, 2) <= 0) {
            return [];
        }

        $growth = round((((float) $curr - (float) $prev) / (float) $prev) * 100, 1);

        if ($growth <= 25.0) {
            return [];
        }

        return [
            [
                'rule_key' => 'month_over_month_growth',
                'category_id' => null,
                'title' => "Overall spending is up {$growth}% compared to last month",
                'message' => "Your monthly expenses have reached \${$curr}, an increase of \${$delta} ({$growth}%) compared to last month's \${$prev}.",
                'suggestion' => "Curtailing discretionary purchases to match last month's spending pace could save you \${$delta}.",
                'trigger_data' => [
                    'current_expenses' => $curr,
                    'previous_expenses' => $prev,
                    'percentage_growth' => $growth,
                    'delta' => $delta,
                ],
                'estimated_savings' => $delta,
            ],
        ];
    }

    public function evaluateSavingsGoalLagging(User $user, Carbon $now): array
    {
        $goal = number_format((float) ($user->savings_goal ?? 0), 2, '.', '');

        if (bccomp($goal, '0.00', 2) <= 0) {
            return [];
        }

        $start = $now->copy()->startOfMonth()->toDateString();
        $end = $now->copy()->endOfMonth()->toDateString();

        $inc = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $exp = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $net = bcsub(
            number_format((float) $inc, 2, '.', ''),
            number_format((float) $exp, 2, '.', ''),
            2
        );

        $saved = max(0.0, (float) $net);
        $savedStr = number_format($saved, 2, '.', '');
        $deficit = bcsub($goal, $savedStr, 2);

        if (bccomp($deficit, '0.00', 2) <= 0) {
            return [];
        }

        $progress = round(($saved / (float) $goal) * 100, 1);

        return [
            [
                'rule_key' => 'savings_goal_lagging',
                'category_id' => null,
                'title' => "Monthly savings target is lagging by \${$deficit}",
                'message' => "You have saved \${$savedStr} toward your \${$goal} monthly goal ({$progress}% achieved), leaving a \${$deficit} gap.",
                'suggestion' => "Trimming non-essential dining or entertainment purchases could close this \${$deficit} deficit before month end.",
                'trigger_data' => [
                    'savings_goal' => $goal,
                    'current_savings' => $savedStr,
                    'deficit' => $deficit,
                    'progress_percent' => $progress,
                ],
                'estimated_savings' => $deficit,
            ],
        ];
    }
}
