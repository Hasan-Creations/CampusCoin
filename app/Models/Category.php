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

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'icon',
        'color',
        'is_default',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function savingTips(): HasMany
    {
        return $this->hasMany(SavingTip::class);
    }

    public function categoryLearnings(): HasMany
    {
        return $this->hasMany(CategoryLearning::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere('is_default', true)
                ->orWhereNull('user_id');
        });
    }

    public function scopePersonal(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSystemDefaults(Builder $query): Builder
    {
        return $query->where('is_default', true)->orWhereNull('user_id');
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isDefault(): bool
    {
        return (bool) $this->is_default || is_null($this->user_id);
    }

    public function isOwnedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->user_id === $user->id;
    }

    public function canBeDeletedBy(?User $user): bool
    {
        if (! $this->isOwnedBy($user)) {
            return false;
        }

        return $this->transactions()->count() === 0;
    }

    public function canBeSafelyDeleted(): bool
    {
        return $this->transactions()->count() === 0
            && $this->budgets()->count() === 0
            && $this->savingTips()->count() === 0
            && $this->categoryLearnings()->count() === 0;
    }
}
