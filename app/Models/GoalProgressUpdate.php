<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoalProgressUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'date',
        'description',
        'progress_value',
        'time_spent_minutes',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'progress_value' => 'decimal:2',
            'time_spent_minutes' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Human-readable time spent, e.g. "1h 30m".
     */
    public function timeSpentLabel(): string
    {
        $minutes = $this->time_spent_minutes;

        if (! $minutes) {
            return '—';
        }

        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        if ($hours > 0 && $mins > 0) {
            return $hours . 'h ' . $mins . 'm';
        }

        return $hours > 0 ? $hours . 'h' : $mins . 'm';
    }
}
