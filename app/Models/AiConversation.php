<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A persisted AI conversation owned by exactly one user.
 */
class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'context_type',
        'context_ref_type',
        'context_ref_id',
        'message_count',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'message_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'ai_conversation_id')->orderBy('id');
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * A safe, human friendly title derived from the first user message.
     */
    public function displayTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        return 'New conversation';
    }

    /**
     * Record a message against this conversation and keep the counters in sync.
     */
    public function recordMessage(string $role, string $content, array $metadata = []): AiMessage
    {
        $message = $this->messages()->create([
            'user_id' => $this->user_id,
            'role' => $role,
            'content' => $content,
            'metadata' => $metadata ?: null,
        ]);

        $this->forceFill([
            'message_count' => $this->messages()->count(),
            'last_message_at' => now(),
        ])->save();

        return $message;
    }
}
