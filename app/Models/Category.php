<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'icon',
        'color',
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * The student owner of this personal category, if not a system default.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Transactions logged under this category.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Budget goals set for this category.
     */
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Saving tips associated with this category.
     */
    public function savingTips(): HasMany
    {
        return $this->hasMany(SavingTip::class);
    }

    /**
     * Scope to categories accessible by a specific user (their personal categories + system defaults).
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_default', true)
                ->orWhereNull('user_id');
        });
    }

    /**
     * Scope to strictly personal categories of a student.
     */
    public function scopePersonal(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to system-wide default categories.
     */
    public function scopeSystemDefaults(Builder $query): Builder
    {
        return $query->where('is_default', true)->orWhereNull('user_id');
    }

    /**
     * Scope to income categories.
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope to expense categories.
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    /**
     * Check if this is a system-wide default category.
     */
    public function isDefault(): bool
    {
        return (bool) $this->is_default || is_null($this->user_id);
    }

    /**
     * Check if this category can be edited/deleted by the given user.
     */
    public function isOwnedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->user_id === $user->id;
    }

    /**
     * Determine if this category can be safely deleted.
     */
    public function canBeDeletedBy(?User $user): bool
    {
        if (! $this->isOwnedBy($user)) {
            return false;
        }

        return $this->transactions()->count() === 0;
    }
}
