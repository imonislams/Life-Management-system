<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsGoal extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'completed', 'paused'];

    protected $fillable = [
        'user_id',
        'currency_id',
        'currency_code',
        'name',
        'description',
        'target_amount',
        'current_amount',
        'start_date',
        'target_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'start_date' => 'date',
            'target_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The currency this savings goal is measured in.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SavingsTransaction::class, 'savings_goal_id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Progress toward the target as a percentage (0-100).
     */
    public function progressPercentage(): float
    {
        $target = (float) $this->target_amount;

        if ($target <= 0) {
            return 0.0;
        }

        return min(100, round(((float) $this->current_amount / $target) * 100, 2));
    }

    /**
     * Remaining amount to reach the target.
     */
    public function remainingAmount(): float
    {
        return max(0, (float) $this->target_amount - (float) $this->current_amount);
    }
}
