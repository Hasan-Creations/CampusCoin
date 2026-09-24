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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'trigger_data' => 'array',
            'estimated_savings' => 'decimal:2',
            'dismissed_at' => 'datetime',
            'pinned_at' => 'datetime',
        ];
    }

    /**
     * The user (student) this saving tip belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The optional category associated with this saving tip.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope to saving tips of a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to active tips.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to pinned tips.
     */
    public function scopePinned(Builder $query): Builder
    {
        return $query->where('status', 'pinned');
    }

    /**
     * Scope to dismissed tips.
     */
    public function scopeDismissed(Builder $query): Builder
    {
        return $query->where('status', 'dismissed');
    }

    /**
     * Pin this saving tip.
     */
    public function pin(): bool
    {
        $this->status = 'pinned';
        $this->pinned_at = Carbon::now();
        $this->dismissed_at = null;

        return $this->save();
    }

    /**
     * Unpin this saving tip, returning it to active.
     */
    public function unpin(): bool
    {
        $this->status = 'active';
        $this->pinned_at = null;

        return $this->save();
    }

    /**
     * Dismiss this saving tip.
     */
    public function dismiss(): bool
    {
        $this->status = 'dismissed';
        $this->dismissed_at = Carbon::now();
        $this->pinned_at = null;

        return $this->save();
    }

    /**
     * Restore/unDismiss this saving tip back to active.
     */
    public function unDismiss(): bool
    {
        $this->status = 'active';
        $this->dismissed_at = null;

        return $this->save();
    }

    /**
     * Check if the tip is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the tip is pinned.
     */
    public function isPinned(): bool
    {
        return $this->status === 'pinned';
    }

    /**
     * Check if the tip is dismissed.
     */
    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    /**
     * Format estimated potential savings for display.
     */
    public function formattedEstimatedSavings(): string
    {
        return '$'.number_format((float) $this->estimated_savings, 2);
    }
}
