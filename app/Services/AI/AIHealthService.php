<?php

namespace App\Services\AI;

use App\Models\AiEmbedding;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\EmbeddingServiceInterface;
use App\Services\AI\Hardware\HardwareDetector;
use App\Services\AI\Vector\VectorSearchInterface;
use Throwable;

/**
 * AI layer health/status service.
 *
 * Reports whether each moving part of the LOCAL stack is ready:
 *   AI enabled -> Ollama reachable -> LLM model available -> embedding model
 *   available -> vector DB reachable -> vector collection available.
 *
 * Nothing here is secret: only model names, URLs from config and record counts
 * are surfaced (never API keys).
 */
class AIHealthService
{
    public function __construct(
        protected AIProviderInterface $provider,
        protected EmbeddingServiceInterface $embeddings,
        protected VectorSearchInterface $vector,
        protected HardwareDetector $hardware
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function report(?int $userId = null): array
    {
        $enabled = (bool) config('ai.enabled');

        try {
            $ollamaReachable = $this->provider->healthCheck();
        } catch (Throwable $e) {
            $ollamaReachable = false;
        }

        $installed = $ollamaReachable ? $this->safeModels() : [];

        $llmModel = $ollamaReachable ? $this->safe(fn() => $this->provider->model(), '') : '';
        $embeddingModel = $ollamaReachable ? $this->safe(fn() => $this->embeddings->model(), '') : '';

        $vectorReachable = $this->safe(fn() => $this->vector->healthCheck(), false);

        $recordCount = $userId ? $this->safe(fn() => $this->vector->count($userId), 0) : null;

        $status = $this->status($enabled, $ollamaReachable, $llmModel, $embeddingModel, $vectorReachable);

        return [
            'status' => $status,
            'enabled' => $enabled,
            'provider' => (string) config('ai.provider', 'ollama'),
            'ollama' => [
                'reachable' => $ollamaReachable,
                'base_url' => (string) config('ai.providers.' . config('ai.provider') . '.base_url', ''),
                'installed_models' => $installed,
            ],
            'llm_model' => $llmModel,
            'llm_model_available' => $llmModel !== '' && $this->modelInstalled($llmModel, $installed),
            'embedding_model' => $embeddingModel,
            'embedding_model_available' => $embeddingModel !== '' && $this->modelInstalled($embeddingModel, $installed),
            'vector' => [
                'reachable' => $vectorReachable,
                'record_count' => $recordCount,
                'describe' => $this->safe(fn() => $this->vector->describe(), []),
            ],
            'last_reindex' => $userId ? $this->lastReindex($userId) : null,
            'hardware' => $this->safe(fn() => $this->hardware->detect(), []),
        ];
    }

    /**
     * Healthy | Warning | Unavailable.
     */
    protected function status(bool $enabled, bool $ollama, string $llm, string $embedding, bool $vector): string
    {
        if (! $enabled) {
            return 'Unavailable';
        }

        if (! $ollama) {
            return 'Unavailable';
        }

        // Ollama is up but a model or the vector store is missing: degraded.
        if ($llm === '' || $embedding === '' || ! $vector) {
            return 'Warning';
        }

        return 'Healthy';
    }

    /**
     * A user-facing, secret-free hint describing the current status.
     */
    public function hint(array $report): string
    {
        return match ($report['status']) {
            'Healthy' => 'Local AI is ready.',
            'Warning' => 'Local AI is partially available. Semantic search or a model may be missing — check the details below.',
            default => $report['enabled']
                ? 'Local AI is currently unavailable. Please start Ollama (and Qdrant) and try again.'
                : 'The AI assistant is disabled. Set AI_ENABLED=true to turn it on.',
        };
    }

    /**
     * Timestamp of the most recent indexed vector for a user, or null.
     */
    protected function lastReindex(int $userId): ?string
    {
        try {
            $latest = AiEmbedding::ownedBy($userId)->max('updated_at');

            return $latest ? (string) $latest : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    protected function modelInstalled(string $model, array $installed): bool
    {
        foreach ($installed as $name) {
            if ($name === $model || str_starts_with($name, $model . ':')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    protected function safeModels(): array
    {
        try {
            return $this->provider->installedModels();
        } catch (Throwable $e) {
            return [];
        }
    }

    protected function safe(callable $callback, mixed $default): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            return $default;
        }
    }
}
