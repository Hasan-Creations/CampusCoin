<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

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
        'recurrence_frequency',
        'next_occurrence_date',
        'recurring_source_id',
        'ai_suggested',
        'ai_confidence',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
            'is_recurring' => 'boolean',
            'next_occurrence_date' => 'date',
            'ai_suggested' => 'boolean',
            'ai_confidence' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TransactionHistory::class);
    }

    public function preserveHistory(string $action): void
    {
        $snapshot = $this->only([
            'type',
            'amount',
            'merchant',
            'description',
            'category_id',
            'transaction_date',
            'payment_method',
            'is_recurring',
            'recurrence_frequency',
            'next_occurrence_date',
            'ai_suggested',
            'ai_confidence',
        ]);
        $snapshot['category_name'] = $this->category?->name;
        $snapshot['transaction_date'] = $this->transaction_date->format('Y-m-d');
        $snapshot['next_occurrence_date'] = $this->next_occurrence_date?->format('Y-m-d');

        $this->history()->create([
            'user_id' => $this->user_id,
            'action' => $action,
            'snapshot' => $snapshot,
        ]);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeIncome(Builder $query): Builder
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('type', 'expense');
    }

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

    public function scopeByCategory(Builder $query, ?int $categoryId): Builder
    {
        if (blank($categoryId)) {
            return $query;
        }

        return $query->where('category_id', $categoryId);
    }

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function formattedAmount(): string
    {
        $prefix = $this->isIncome() ? '+' : '-';

        return $prefix.'$'.number_format((float) $this->amount, 2);
    }
}
