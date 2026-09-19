<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single message within an AI conversation.
 *
 * "metadata" holds a compact trace of what was retrieved (source types, ids and
 * scores) so an answer can always be audited back to real MySQL rows.
 */
class AiMessage extends Model
{
    use HasFactory;

    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    public const ROLE_SYSTEM = 'system';

    protected $fillable = [
        'ai_conversation_id',
        'user_id',
        'role',
        'content',
        'metadata',
        'token_estimate',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'token_estimate' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function isFromUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }
}
