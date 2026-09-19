<?php

namespace App\Services\AI;

use App\Models\AiEmbedding;
use App\Models\DailyActivity;
use App\Models\Event;
use App\Models\Goal;
use App\Models\GoalProgressUpdate;
use App\Models\Habit;
use App\Models\User;
use App\Services\AI\Contracts\EmbeddingServiceInterface;
use App\Services\AI\Vector\VectorSearchInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Builds the semantic index from MySQL (the source of truth).
 *
 * For each indexable record it composes a compact, human-readable text blob,
 * generates a LOCAL embedding, and upserts it into the vector store together
 * with ownership metadata (user_id / source_type / source_id).
 *
 * This is the ONLY place that knows how a source record becomes text, so the
 * indexed shape can evolve in one spot. It is used by both the model observers
 * (incremental sync) and the ai:reindex command (full rebuild).
 */
class IndexingService
{
    public function __construct(
        protected EmbeddingServiceInterface $embeddings,
        protected VectorSearchInterface $vector,
        protected AIPrivacyService $privacy
    ) {}

    /**
     * The model classes that participate in semantic indexing.
     *
     * @return array<int, class-string<Model>>
     */
    public static function indexableModels(): array
    {
        return [
            DailyActivity::class,
            Goal::class,
            GoalProgressUpdate::class,
            Habit::class,
            Event::class,
            \App\Models\IncomeRecord::class,
            \App\Models\ExpenseRecord::class,
            \App\Models\SavingsGoal::class,
        ];
    }

    /**
     * Map a model class to its AiEmbedding source_type constant.
     */
    public static function sourceTypeFor(string $modelClass): ?string
    {
        return match ($modelClass) {
            DailyActivity::class => AiEmbedding::SOURCE_DAILY_ACTIVITY,
            Goal::class => AiEmbedding::SOURCE_GOAL,
            GoalProgressUpdate::class => AiEmbedding::SOURCE_GOAL_PROGRESS,
            Habit::class => AiEmbedding::SOURCE_HABIT,
            Event::class => AiEmbedding::SOURCE_EVENT,
            \App\Models\IncomeRecord::class,
            \App\Models\ExpenseRecord::class,
            \App\Models\SavingsGoal::class,
            \App\Models\RecurringTransaction::class => AiEmbedding::SOURCE_FINANCE_NOTE,
            default => null,
        };
    }

    /**
     * Whether the AI layer can currently index for a user.
     */
    public function isIndexingAllowed(?int $userId): bool
    {
        return $this->privacy->canIndex($userId)
            && $this->embeddings->isAvailable();
    }

    /**
     * Index (or refresh) a single record. Failures are swallowed: indexing must
     * never break the CRUD path that triggered it.
     */
    public function indexModel(Model $model): bool
    {
        try {
            $userId = (int) ($model->user_id ?? 0);
            $sourceType = static::sourceTypeFor($model::class);

            if (! $userId || ! $sourceType) {
                return false;
            }

            if (! $this->isIndexingAllowed($userId)) {
                return false;
            }

            $content = $this->composeText($model, $sourceType);

            if (trim($content) === '') {
                return false;
            }

            $vector = $this->embeddings->embed($this->privacy->sanitizeUntrusted($content, 2000));

            $this->vector->upsert(
                $userId,
                $sourceType,
                (int) $model->getKey(),
                $this->privacy->sanitizeUntrusted($content, 2000),
                $vector,
                $this->embeddings->model(),
                optional($model->updated_at)->toDateTimeString()
            );

            return true;
        } catch (Throwable $e) {
            Log::warning('AI indexing failed for a record', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Remove a record from the vector index. Never throws.
     */
    public function removeModel(Model $model): bool
    {
        try {
            $userId = (int) ($model->user_id ?? 0);
            $sourceType = static::sourceTypeFor($model::class);

            if (! $userId || ! $sourceType) {
                return false;
            }

            // Cleanup must still work when the AI layer is disabled.
            $this->vector->delete($userId, $sourceType, (int) $model->getKey());

            return true;
        } catch (Throwable $e) {
            Log::warning('AI de-indexing failed for a record', [
                'model' => $model::class,
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Rebuild the entire index for a user (or every user when null).
     *
     * @return array{indexed:int, skipped:int, users:int}
     */
    public function reindex(?int $userId = null): array
    {
        $userIds = $userId
            ? [$userId]
            : User::query()->pluck('id')->all();

        $indexed = 0;
        $skipped = 0;

        foreach ($userIds as $id) {
            if (! $this->isIndexingAllowed((int) $id)) {
                $skipped++;

                continue;
            }

            foreach (static::indexableModels() as $modelClass) {
                $modelClass::query()
                    ->where('user_id', $id)
                    ->orderBy('id')
                    ->chunkById(100, function ($records) use (&$indexed) {
                        foreach ($records as $record) {
                            if ($this->indexModel($record)) {
                                $indexed++;
                            }
                        }
                    });
            }
        }

        return [
            'indexed' => $indexed,
            'skipped' => $skipped,
            'users' => count($userIds),
        ];
    }

    /**
     * Compose the embeddable text for a record.
     */
    public function composeText(Model $model, string $sourceType): string
    {
        return match ($sourceType) {
            AiEmbedding::SOURCE_DAILY_ACTIVITY => $this->dailyActivityText($model),
            AiEmbedding::SOURCE_GOAL => $this->goalText($model),
            AiEmbedding::SOURCE_GOAL_PROGRESS => $this->goalProgressText($model),
            AiEmbedding::SOURCE_HABIT => $this->habitText($model),
            AiEmbedding::SOURCE_EVENT => $this->eventText($model),
            AiEmbedding::SOURCE_FINANCE_NOTE => $this->financeText($model),
            default => '',
        };
    }

    // ------------------------------------------------------------------
    // Per-source text builders (only MEANINGFUL textual context is indexed).
    // ------------------------------------------------------------------

    protected function dailyActivityText(Model $model): string
    {
        /** @var DailyActivity $model */
        return trim(implode(' ', array_filter([
            'Activity: ' . $model->title,
            $model->description ? 'Notes: ' . $model->description : null,
            $model->category ? 'Category: ' . $model->category : null,
            $model->activity_date ? 'Date: ' . $model->activity_date->toDateString() : null,
            $model->status ? 'Status: ' . $model->status : null,
            $model->resolveDuration() ? 'Duration: ' . $model->resolveDuration() . ' minutes' : null,
            $model->goal_id ? 'Related goal: ' . optional($model->goal)->title : null,
        ])));
    }

    protected function goalText(Model $model): string
    {
        /** @var Goal $model */
        return trim(implode(' ', array_filter([
            'Goal: ' . $model->title,
            $model->description ? 'Description: ' . $model->description : null,
            $model->status ? 'Status: ' . $model->status : null,
            $model->progress_type ? 'Progress type: ' . $model->progress_type : null,
            $model->start_date ? 'Start: ' . $model->start_date->toDateString() : null,
            $model->target_date ? 'Target: ' . $model->target_date->toDateString() : null,
            $model->notes ? 'Notes: ' . $model->notes : null,
        ])));
    }

    protected function goalProgressText(Model $model): string
    {
        /** @var GoalProgressUpdate $model */
        return trim(implode(' ', array_filter([
            'Goal progress for: ' . optional($model->goal)->title,
            $model->date ? 'Date: ' . $model->date->toDateString() : null,
            $model->description ? 'Update: ' . $model->description : null,
            $model->progress_value ? 'Progress value: ' . $model->progress_value : null,
            $model->time_spent_minutes ? 'Time spent: ' . $model->time_spent_minutes . ' minutes' : null,
            $model->notes ? 'Notes: ' . $model->notes : null,
        ])));
    }

    protected function habitText(Model $model): string
    {
        /** @var Habit $model */
        return trim(implode(' ', array_filter([
            'Habit: ' . $model->title,
            $model->description ? 'Description: ' . $model->description : null,
            $model->frequency ? 'Frequency: ' . $model->frequency : null,
            $model->status ? 'Status: ' . $model->status : null,
            $model->start_date ? 'Start: ' . $model->start_date->toDateString() : null,
        ])));
    }

    protected function eventText(Model $model): string
    {
        /** @var Event $model */
        return trim(implode(' ', array_filter([
            'Event: ' . $model->title,
            $model->description ? 'Description: ' . $model->description : null,
            $model->event_date ? 'Date: ' . $model->event_date->toDateString() : null,
            $model->location ? 'Location: ' . $model->location : null,
            $model->status ? 'Status: ' . $model->status : null,
            $model->notes ? 'Notes: ' . $model->notes : null,
        ])));
    }

    /**
     * Only contextual/textual finance information is indexed (notes, source,
     * description, category). NEVER totals — those are SQL-only.
     */
    protected function financeText(Model $model): string
    {
        $parts = [class_basename($model) . ' note'];

        foreach (['description', 'notes', 'source', 'category', 'name'] as $field) {
            if (! empty($model->{$field})) {
                $parts[] = ucfirst($field) . ': ' . $model->{$field};
            }
        }

        foreach (['date', 'target_date'] as $field) {
            if (! empty($model->{$field}) && $model->{$field} instanceof \DateTimeInterface) {
                $parts[] = ucfirst($field) . ': ' . $model->{$field}->format('Y-m-d');
            }
        }

        return trim(implode(' ', array_filter($parts)));
    }
}
