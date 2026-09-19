<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    use HasFactory;

    public const FREQUENCIES = ['daily', 'weekly', 'custom'];

    public const STATUSES = ['active', 'paused', 'completed'];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'frequency',
        'start_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(HabitCompletion::class);
    }

    /**
     * Independently trackable activities belonging to this habit.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(HabitActivity::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Whether this habit uses per-activity tracking.
     */
    public function hasActivities(): bool
    {
        if ($this->relationLoaded('activities')) {
            return $this->activities->isNotEmpty();
        }

        return $this->activities()->exists();
    }

    /**
     * Scope to habits belonging to a specific user.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Whether this habit has been completed on the given date.
     */
    public function isCompletedOn($date): bool
    {
        $date = $date instanceof \DateTimeInterface
            ? $date->format('Y-m-d')
            : (string) $date;

        if ($this->relationLoaded('completions')) {
            return $this->completions->contains(function ($completion) use ($date) {
                return optional($completion->completed_date)->format('Y-m-d') === $date;
            });
        }

        return $this->completions()->whereDate('completed_date', $date)->exists();
    }

    /**
     * Number of days between the start date and today (inclusive), used as a
     * simple progress measure for the habit.
     */
    public function daysTracked(): int
    {
        if (! $this->start_date) {
            return 0;
        }

        $start = $this->start_date->copy()->startOfDay();
        $today = now()->startOfDay();

        if ($start->greaterThan($today)) {
            return 0;
        }

        return $start->diffInDays($today) + 1;
    }
}
