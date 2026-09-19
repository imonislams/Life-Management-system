<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoutineItem extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'paused', 'completed', 'skipped'];

    public const RECURRENCE_TYPES = [
        'one_time',
        'daily',
        'weekly',
        'monthly',
        'custom_days',
        'interval',
    ];

    /**
     * Day-of-week tokens used by custom_days routines (0 = Sunday).
     */
    public const WEEKDAYS = [
        0 => 'Sun',
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'recurrence_type',
        'interval_days',
        'days_of_week',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'string',
            'end_time' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
            'interval_days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(RoutineOccurrence::class);
    }

    /**
     * Day-of-week integers selected for a custom_days routine.
     *
     * @return array<int, int>
     */
    public function dayOfWeekList(): array
    {
        if (! $this->days_of_week) {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $this->days_of_week))));
    }

    /**
     * Whether this routine is scheduled on the given date, taking its
     * recurrence type, weekly interval and active window into account.
     */
    public function isDueOn(Carbon $date): bool
    {
        $date = $date->copy()->startOfDay();

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->start_date && $date->lessThan($this->start_date->copy()->startOfDay())) {
            return false;
        }

        if ($this->end_date && $date->greaterThan($this->end_date->copy()->startOfDay())) {
            return false;
        }

        switch ($this->recurrence_type) {
            case 'one_time':
                return $this->start_date
                    ? $date->isSameDay($this->start_date)
                    : false;

            case 'daily':
                return true;

            case 'weekly':
                if (! $this->start_date) {
                    return $date->isSameDay($date);
                }

                return $date->diffInDays($this->start_date->copy()->startOfDay(), false) % 7 === 0;

            case 'monthly':
                return $this->start_date
                    ? $date->day === $this->start_date->day
                    : false;

            case 'custom_days':
                $days = $this->dayOfWeekList();

                return in_array((int) $date->dayOfWeek, $days, true);

            case 'interval':
                $interval = max(1, (int) ($this->interval_days ?: 1));
                $base = $this->start_date ? $this->start_date->copy()->startOfDay() : null;

                if (! $base) {
                    return false;
                }

                $diff = $base->diffInDays($date, false);

                return $diff >= 0 && $diff % $interval === 0;

            default:
                return true;
        }
    }

    /**
     * Human-readable recurrence label, e.g. "Mon, Wed, Fri" or "Every 2 days".
     */
    public function recurrenceLabel(): string
    {
        return match ($this->recurrence_type) {
            'one_time' => 'One time',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'custom_days' => $this->customDaysLabel(),
            'interval' => 'Every ' . max(1, (int) $this->interval_days) . ' days',
            default => 'Daily',
        };
    }

    protected function customDaysLabel(): string
    {
        $days = $this->dayOfWeekList();

        if (empty($days)) {
            return 'Custom days';
        }

        $labels = array_map(fn ($d) => self::WEEKDAYS[$d] ?? (string) $d, $days);

        return implode(', ', $labels);
    }

    /**
     * Scope to routine items belonging to a specific user.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Formatted start time for display, e.g. "08:30 AM".
     */
    public function startTimeLabel(): string
    {
        return $this->formatTime($this->start_time);
    }

    /**
     * Formatted end time for display, e.g. "09:15 AM".
     */
    public function endTimeLabel(): string
    {
        return $this->formatTime($this->end_time);
    }

    /**
     * Whether the routine item's start time is still in the future today.
     */
    public function isUpcoming(): bool
    {
        if (! $this->start_time) {
            return false;
        }

        return Carbon::parse($this->start_time)->format('H:i') > Carbon::now()->format('H:i');
    }

    protected function formatTime(?string $value): string
    {
        if (! $value) {
            return '--:--';
        }

        // Honours the user's 12h / 24h preference from General settings.
        return \App\Support\UserPreference::formatTime($value);
    }
}
