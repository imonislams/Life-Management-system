<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsTransaction extends Model
{
    use HasFactory;

    public const TYPES = ['deposit', 'withdrawal'];

    protected $fillable = [
        'user_id',
        'savings_goal_id',
        'type',
        'amount',
        'date',
        'note',
        'currency_id',
        'currency_code',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(SavingsGoal::class, 'savings_goal_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeDeposits(Builder $query): Builder
    {
        return $query->where('type', 'deposit');
    }

    public function scopeWithdrawals(Builder $query): Builder
    {
        return $query->where('type', 'withdrawal');
    }

    /**
     * Signed effect of this transaction on the goal balance.
     */
    public function signedAmount(): float
    {
        return $this->type === 'withdrawal'
            ? -1 * (float) $this->amount
            : (float) $this->amount;
    }
}
