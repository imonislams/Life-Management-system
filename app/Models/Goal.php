<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goal extends Model
{
    use HasFactory;

    public const PRIORITIES = ['low', 'medium', 'high'];

    public const STATUSES = ['not_started', 'in_progress', 'completed', 'paused', 'cancelled'];

    /**
     * Progress tracking modes: measurable (numeric target) or qualitative
     * (progress measured only from logged updates).
     */
    public const PROGRESS_TYPES = ['measurable', 'qualitative'];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_date',
        'target_value',
        'target_date',
        'priority',
        'status',
        'progress_type',
        'target_amount',
        'current_amount',
        'progress',
        'notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_date' => 'date',
            'completed_at' => 'datetime',
            'progress' => 'integer',
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dated progress updates logged by the user (history is preserved).
     */
    public function progressUpdates(): HasMany
    {
        return $this->hasMany(GoalProgressUpdate::class)->orderByDesc('date')->orderByDesc('id');
    }

    /**
     * Daily activities linked to this goal.
     */
    public function dailyActivities(): HasMany
    {
        return $this->hasMany(DailyActivity::class)->orderByDesc('activity_date')->orderByDesc('id');
    }

    /**
     * The goal's progress percentage derived from real stored data.
     *
     * - Measurable goals: current_amount / target_amount.
     * - Qualitative goals: the stored progress percentage.
     * Returns null when progress cannot be meaningfully computed.
     */
    public function progressPercentage(): ?float
    {
        if ($this->progress_type === 'measurable' && (float) $this->target_amount > 0) {
            return min(100, round(((float) $this->current_amount / (float) $this->target_amount) * 100, 2));
        }

        return $this->progress !== null ? (float) $this->progress : null;
    }

    /**
     * Total goal duration in days (start_date -> target_date).
     */
    public function durationInDays(): ?int
    {
        if (! $this->start_date || ! $this->target_date) {
            return null;
        }

        return (int) $this->start_date->startOfDay()->diffInDays($this->target_date->startOfDay());
    }

    /**
     * Days elapsed since the start date (0 before it starts).
     */
    public function daysElapsed(): ?int
    {
        if (! $this->start_date) {
            return null;
        }

        $today = now()->startOfDay();

        if ($today->lessThan($this->start_date->copy()->startOfDay())) {
            return 0;
        }

        return (int) $this->start_date->startOfDay()->diffInDays($today);
    }

    /**
     * Days remaining until the target date (0 once passed).
     */
    public function daysRemaining(): ?int
    {
        if (! $this->target_date) {
            return null;
        }

        $today = now()->startOfDay();

        if ($today->greaterThan($this->target_date->copy()->startOfDay())) {
            return 0;
        }

        return (int) $today->diffInDays($this->target_date->copy()->startOfDay());
    }

    /**
     * Total time logged toward this goal, in minutes.
     */
    public function totalTimeSpentMinutes(): int
    {
        return (int) $this->progressUpdates()->sum('time_spent_minutes');
    }

    /**
     * Scope to goals belonging to a specific user.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to active goals (anything not completed).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['not_started', 'in_progress', 'paused']);
    }

    /**
     * Scope to overdue goals: past target date and not completed.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('target_date')
            ->whereDate('target_date', '<', now()->toDateString())
            ->where('status', '!=', 'completed');
    }

    /**
     * Whether this goal is overdue.
     */
    public function isOverdue(): bool
    {
        if (! $this->target_date || $this->status === 'completed') {
            return false;
        }

        return $this->target_date->isPast()
            && ! $this->target_date->isToday();
    }

    /**
     * Human readable status label.
     */
    public function statusLabel(): string
    {
        return match ($this->status) {
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'paused' => 'Paused',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
