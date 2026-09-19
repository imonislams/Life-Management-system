<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Per-user AI preferences. API keys are NEVER stored here (server .env only).
 */
class AiSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ai_enabled',
        'provider',
        'model',
        'semantic_indexing_enabled',
    ];

    protected function casts(): array
    {
        return [
            'ai_enabled' => 'boolean',
            'semantic_indexing_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The settings row for a user, created with safe defaults on first access.
     *
     * Explicit defaults are supplied (rather than relying only on DB column
     * defaults) so the returned model reflects them immediately. Without this the
     * freshly-created instance's attributes would be null until re-loaded, which
     * would make the first indexing/semantic check wrongly report "disabled".
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'ai_enabled' => true,
                'semantic_indexing_enabled' => true,
            ]
        );
    }
}
