<?php

namespace App\Jobs\AI;

use App\Models\AiEmbedding;
use App\Services\AI\IndexingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Refresh a single record's vector when the underlying MySQL row changes.
 *
 * Design guarantees:
 *   - The job is queued, so the CRUD request that triggered it is never blocked.
 *   - Every failure is swallowed: an AI/vector outage must not surface to the
 *     user or retry forever against a dead Ollama/Qdrant.
 *   - The record is re-loaded by class+id so a delete that races the job is safe.
 */
class SyncEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * A single attempt only: a missing local runtime should not create a retry
     * storm. The reindex command is the recovery path.
     */
    public int $tries = 1;

    public int $timeout = 120;

    /**
     * @param  string  $modelClass  Fully-qualified Eloquent model class.
     * @param  int  $modelId  Primary key of the changed record.
     * @param  bool  $delete  True when the record was removed.
     */
    public function __construct(
        public string $modelClass,
        public int $modelId,
        public bool $delete = false
    ) {
        $this->onQueue((string) config('ai.sync.queue_name', 'default'));
    }

    public function handle(IndexingService $indexing): void
    {
        try {
            /** @var \Illuminate\Database\Eloquent\Model|null $model */
            $model = $this->modelClass::query()->find($this->modelId);

            if ($this->delete || $model === null) {
                // Already gone: nothing to index. Best-effort cleanup by id.
                if ($model === null) {
                    return;
                }

                $indexing->removeModel($model);

                return;
            }

            $indexing->indexModel($model);
        } catch (Throwable $e) {
            Log::warning('SyncEmbeddingJob failed', [
                'model' => $this->modelClass,
                'id' => $this->modelId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Swallow failures: a queue-level failure must never mark the user's CRUD as
     * failed or spam the failed-jobs table.
     */
    public function failed(Throwable $e): void
    {
        Log::warning('SyncEmbeddingJob permanently failed', [
            'model' => $this->modelClass,
            'id' => $this->modelId,
            'error' => $e->getMessage(),
        ]);
    }
}
