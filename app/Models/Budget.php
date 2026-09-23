<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'month_year',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * The student owner of this budget goal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The expense category associated with this budget.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to budgets of a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to budgets for a specific month (YYYY-MM).
     */
    public function scopeForMonth(Builder $query, string $monthYear): Builder
    {
        return $query->where('month_year', $monthYear);
    }

    /**
     * Calculate or retrieve the total spent in this budget's category for this month.
     * Only considers actual expense transactions for this student.
     */
    public function getSpentAmount(?string $preloadedSpent = null): string
    {
        if ($preloadedSpent !== null) {
            return number_format((float) $preloadedSpent, 2, '.', '');
        }

        try {
            $date = Carbon::createFromFormat('Y-m', $this->month_year);
            $startDate = $date->copy()->startOfMonth()->toDateString();
            $endDate = $date->copy()->endOfMonth()->toDateString();
        } catch (\Throwable) {
            $startDate = $this->month_year.'-01';
            $endDate = $this->month_year.'-31';
        }

        $spent = Transaction::where('user_id', $this->user_id)
            ->where('category_id', $this->category_id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');

        return number_format((float) $spent, 2, '.', '');
    }

    /**
     * Remaining budget amount using BCMath (can be negative if over budget).
     */
    public function getRemainingAmount(?string $spent = null): string
    {
        $spent = $spent ?? $this->getSpentAmount();

        return bcsub((string) $this->amount, (string) $spent, 2);
    }

    /**
     * Percentage of budget consumed (can exceed 100%).
     */
    public function getPercentageConsumed(?string $spent = null): float
    {
        $spent = $spent ?? $this->getSpentAmount();
        $limit = (float) $this->amount;

        if ($limit <= 0) {
            return 0.0;
        }

        return round(((float) $spent / $limit) * 100, 1);
    }

    /**
     * Determine if spending has strictly exceeded the budget limit.
     */
    public function isOverBudget(?string $spent = null): bool
    {
        $spent = $spent ?? $this->getSpentAmount();

        return bccomp((string) $spent, (string) $this->amount, 2) > 0;
    }

    /**
     * Determine if spending is near the limit (between 75% and 100% inclusive).
     */
    public function isNearLimit(?string $spent = null): bool
    {
        $spent = $spent ?? $this->getSpentAmount();
        $pct = $this->getPercentageConsumed($spent);

        return $pct >= 75.0 && $pct <= 100.0;
    }

    /**
     * Determine if spending is safely on track (under 75%).
     */
    public function isOnTrack(?string $spent = null): bool
    {
        $spent = $spent ?? $this->getSpentAmount();

        return $this->getPercentageConsumed($spent) < 75.0;
    }

    /**
     * Determine status token ('on_track', 'near_limit', 'over_budget').
     */
    public function getStatus(?string $spent = null): string
    {
        $spent = $spent ?? $this->getSpentAmount();

        if ($this->isOverBudget($spent)) {
            return 'over_budget';
        }

        if ($this->isNearLimit($spent)) {
            return 'near_limit';
        }

        return 'on_track';
    }

    /**
     * Human-readable status label.
     */
    public function getStatusLabel(?string $spent = null): string
    {
        return match ($this->getStatus($spent)) {
            'over_budget' => 'Over Budget',
            'near_limit' => 'Near Limit',
            default => 'On Track',
        };
    }

    /**
     * CSS token / styling class for status badge.
     */
    public function getStatusBadgeClass(?string $spent = null): string
    {
        return match ($this->getStatus($spent)) {
            'over_budget' => 'border border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/60 dark:text-rose-400',
            'near_limit' => 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/60 dark:text-amber-400',
            default => 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/50 dark:bg-emerald-950/60 dark:text-emerald-400',
        };
    }

    /**
     * Progress bar color token.
     */
    public function getProgressBarColor(?string $spent = null): string
    {
        return match ($this->getStatus($spent)) {
            'over_budget' => 'var(--danger)',
            'near_limit' => 'var(--gold)',
            default => 'var(--accent-primary)',
        };
    }

    /**
     * Check if this budget is owned by the given student.
     */
    public function isOwnedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->user_id === $user->id;
    }
}
