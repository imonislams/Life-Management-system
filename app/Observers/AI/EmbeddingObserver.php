<?php

namespace App\Observers\AI;

use App\Jobs\AI\SyncEmbeddingJob;
use App\Services\AI\IndexingService;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Keeps the vector index in sync with MySQL.
 *
 * Whenever an indexable personal record is created, updated or deleted the
 * vector is refreshed — asynchronously by default, or inline when synchronous
 * sync is enabled. In every case failures are swallowed so the primary CRUD
 * operation always succeeds even if the local AI stack is offline.
 */
class EmbeddingObserver
{
    public function created(Model $model): void
    {
        $this->dispatch($model, delete: false);
    }

    public function updated(Model $model): void
    {
        $this->dispatch($model, delete: false);
    }

    public function deleted(Model $model): void
    {
        $this->dispatch($model, delete: true);
    }

    protected function dispatch(Model $model, bool $delete): void
    {
        // Nothing to do when indexing is impossible: skip cheaply, no exceptions.
        if (! $this->shouldSync()) {
            return;
        }

        try {
            if ((bool) config('ai.sync.synchronous_fallback', false)) {
                // Inline path, still fully guarded.
                $indexing = app(IndexingService::class);

                if (! $indexing->isIndexingAllowed((int) ($model->user_id ?? 0))) {
                    return;
                }

                $delete ? $indexing->removeModel($model) : $indexing->indexModel($model);

                return;
            }

            SyncEmbeddingJob::dispatch($model::class, (int) $model->getKey(), $delete);
        } catch (Throwable $e) {
            // Last line of defence: never let AI sync break a CRUD write.
            report($e);
        }
    }

    protected function shouldSync(): bool
    {
        return (bool) config('ai.enabled') && (bool) config('ai.sync.queue');
    }
}
