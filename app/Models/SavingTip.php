<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rule_key',
        'category_id',
        'title',
        'message',
        'suggestion',
        'trigger_data',
        'estimated_savings',
        'status',
        'dismissed_at',
        'pinned_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger_data' => 'array',
            'estimated_savings' => 'decimal:2',
            'dismissed_at' => 'datetime',
            'pinned_at' => 'datetime',
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

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('status', 'pinned');
    }

    public function scopeDismissed(Builder $query): Builder
    {
        return $query->where('status', 'dismissed');
    }

    public function pin(): bool
    {
        $this->status = 'pinned';
        $this->pinned_at = Carbon::now();
        $this->dismissed_at = null;

        return $this->save();
    }

    public function unpin(): bool
    {
        $this->status = 'active';
        $this->pinned_at = null;

        return $this->save();
    }

    public function dismiss(): bool
    {
        $this->status = 'dismissed';
        $this->dismissed_at = Carbon::now();
        $this->pinned_at = null;

        return $this->save();
    }

    public function unDismiss(): bool
    {
        $this->status = 'active';
        $this->dismissed_at = null;

        return $this->save();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPinned(): bool
    {
        return $this->status === 'pinned';
    }

    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    public function formattedEstimatedSavings(): string
    {
        return '$'.number_format((float) $this->estimated_savings, 2);
    }
}
