<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutineOccurrence extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'completed', 'skipped'];

    protected $fillable = [
        'user_id',
        'routine_item_id',
        'occurrence_date',
        'status',
        'notes',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'occurrence_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function routine(): BelongsTo
    {
        return $this->belongsTo(RoutineItem::class, 'routine_item_id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
