<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Fine-tuning scaffolding only.
 *
 * Rows exist purely as curated candidates for a FUTURE, explicitly approved
 * fine-tuning phase. They are collected only when
 * config('ai.fine_tuning.collect_samples') is enabled and are never
 * transmitted to a provider automatically.
 */
class AiTrainingSample extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'user_input',
        'expected_output',
        'context_type',
        'quality_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
