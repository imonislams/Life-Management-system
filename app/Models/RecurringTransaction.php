<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    use HasFactory;

    public const TYPES = ['income', 'expense'];

    public const RECURRENCE_TYPES = ['daily', 'weekly', 'monthly', 'yearly', 'custom'];

    public const STATUSES = ['active', 'paused', 'completed'];

    protected $fillable = [
        'user_id',
        'currency_id',
        'currency_code',
        'type',
        'title',
        'amount',
        'recurrence_type',
        'interval_days',
        'start_date',
        'end_date',
        'next_due_date',
        'description',
        'is_active',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'next_due_date' => 'date',
            'is_active' => 'boolean',
            'interval_days' => 'integer',
        ];

        public function user()
        {
            return $this->belongsTo(User::class);
        }

        public function scopeOwnedBy($query, int $userId)
        {
            return $query->where('user_id', $userId);
        }

        /**
         * Effective status, falling back to the legacy is_active flag for rows
         * created before the status column existed.
         */
        public function effectiveStatus(): string
        {
            if ($this->status) {
                return $this->status;
            }

            return $this->is_active ? 'active' : 'paused';
        }

        public function isRunning(): bool
        {
            return $this->effectiveStatus() === 'active';
        }

    /**
     * The currency this recurring entry is denominated in.
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
