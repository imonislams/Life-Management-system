<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HabitActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'habit_id',
        'user_id',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completions(): HasMany
    {
        return $this->hasMany(HabitCompletion::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Whether this activity was completed on the given date.
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
}
