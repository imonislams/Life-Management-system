<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory;

    public const STATUSES = ['upcoming', 'completed', 'cancelled'];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'start_time' => 'string',
            'end_time' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to events belonging to a specific user.
     */
    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to events occurring today or later.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('event_date', '>=', now()->toDateString());
    }

    /**
     * Scope to events that already happened.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate('event_date', '<', now()->toDateString());
    }

    /**
     * Human readable status label.
     */
    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Formatted start time for display.
     */
    public function startTimeLabel(): string
    {
        return $this->formatTime($this->start_time);
    }

    /**
     * Formatted end time for display.
     */
    public function endTimeLabel(): string
    {
        return $this->formatTime($this->end_time);
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
