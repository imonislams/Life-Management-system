<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A derived semantic-index row.
 *
 * This is NOT a source of truth: it is a rebuildable projection of a MySQL
 * record (daily activity, goal, goal progress, habit, event or finance note).
 * Every row carries user_id and all search is filtered by it.
 */
class AiEmbedding extends Model
{
    use HasFactory;

    public const SOURCE_DAILY_ACTIVITY = 'daily_activity';

    public const SOURCE_GOAL = 'goal';

    public const SOURCE_GOAL_PROGRESS = 'goal_progress';

    public const SOURCE_HABIT = 'habit';

    public const SOURCE_EVENT = 'event';

    public const SOURCE_FINANCE_NOTE = 'finance_note';

    /**
     * All source types that may be embedded.
     *
     * @var list<string>
     */
    public const SOURCE_TYPES = [
        self::SOURCE_DAILY_ACTIVITY,
        self::SOURCE_GOAL,
        self::SOURCE_GOAL_PROGRESS,
        self::SOURCE_HABIT,
        self::SOURCE_EVENT,
        self::SOURCE_FINANCE_NOTE,
    ];

    protected $fillable = [
        'user_id',
        'source_type',
        'source_id',
        'vector_key',
        'content',
        'embedding',
        'embedding_model',
        'embedding_dimensions',
        'source_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'embedding_dimensions' => 'integer',
            'source_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType(Builder $query, string|array $type): Builder
    {
        return is_array($type)
            ? $query->whereIn('source_type', $type)
            : $query->where('source_type', $type);
    }

    /**
     * Deterministic, stable key for a source record, used by external vector
     * stores so the same record always maps to the same indexed vector.
     */
    public static function vectorKeyFor(string $sourceType, int $sourceId): string
    {
        return $sourceType . ':' . $sourceId;
    }
}
