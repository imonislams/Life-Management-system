<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActivity extends Model
{
    use HasFactory;

    public const STATUSES = ['completed', 'in_progress', 'planned', 'skipped'];

    protected $fillable = [
        'user_id',
        'goal_id',
        'title',
        'description',
        'activity_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'category',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'start_time' => 'string',
            'end_time' => 'string',
            'duration_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The goal this activity contributes to, if any.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Effective duration: derived from the start/end times when both are given,
     * otherwise the manually entered value.
     */
    public function resolveDuration(): ?int
    {
        if ($this->start_time && $this->end_time) {
            try {
                $start = Carbon::parse($this->start_time);
                $end = Carbon::parse($this->end_time);

                if ($end->lessThan($start)) {
                    $end->addDay();
                }

                return (int) $start->diffInMinutes($end);
            } catch (\Throwable $e) {
                return $this->duration_minutes;
            }
        }

        return $this->duration_minutes;
    }

    /**
     * Human-readable duration, e.g. "2h 30m".
     */
    public function durationLabel(): string
    {
        $minutes = $this->resolveDuration();

        if ($minutes === null) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return $hours . 'h ' . $mins . 'm';
        }

        return $hours > 0 ? $hours . 'h' : $mins . 'm';
    }

    public function startTimeLabel(): string
    {
        return $this->start_time
            ? \App\Support\UserPreference::formatTime($this->start_time)
            : '—';
    }

    public function endTimeLabel(): string
    {
        return $this->end_time
            ? \App\Support\UserPreference::formatTime($this->end_time)
            : '—';
    }
}
