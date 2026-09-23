<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
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
        'type',
        'amount',
        'merchant',
        'description',
        'transaction_date',
        'payment_method',
        'is_recurring',
        'ai_suggested',
        'ai_confidence',
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
            'transaction_date' => 'date',
            'is_recurring' => 'boolean',
            'ai_suggested' => 'boolean',
            'ai_confidence' => 'decimal:2',
        ];
    }

    /**
     * The student owner of this transaction record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The assigned category for this transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to transactions of a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to income transactions.
     */
    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope to expense transactions.
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

    /**
     * Search transactions by merchant or description.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('merchant', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Filter by category ID.
     */
    public function scopeByCategory(Builder $query, ?int $categoryId): Builder
    {
        if (blank($categoryId)) {
            return $query;
        }

        return $query->where('category_id', $categoryId);
    }

    /**
     * Determine if this is an income transaction.
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Determine if this is an expense transaction.
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    /**
     * Formatted monetary string with directional sign.
     */
    public function formattedAmount(): string
    {
        $prefix = $this->isIncome() ? '+' : '-';

        return $prefix.'$'.number_format((float) $this->amount, 2);
    }
}
