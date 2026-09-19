<?php

namespace App\Services\AI\Vector;

use App\Services\AI\Contracts\VectorRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Milvus vector-store adapter (optional).
 *
 * This adapter is intentionally defensive: if the Milvus service is unreachable
 * (the common case in local development) `isAvailable()` returns false and the
 * AI layer reports that semantic search is temporarily unavailable. The rest of
 * the Personal Workspace is completely unaffected.
 *
 * It also mirrors every write into the MySQL `ai_embeddings` table through the
 * DatabaseVectorRepository, so:
 *   - retrieval always has a local fallback, and
 *   - switching AI_VECTOR_DRIVER fully rebuilds from MySQL if Milvus is lost.
 *
 * Because the official PHP Milvus SDK is a separate, heavy dependency, network
 * calls go through the Milvus REST v2 API which requires no extra Composer
 * package. The endpoint paths are configuration-driven.
 */
class MilvusVectorRepository implements VectorRepositoryInterface
{
    public function __construct(
        protected array $config,
        protected DatabaseVectorRepository $fallback
    ) {}

    /**
     * Cheap availability probe. Never throws.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout((int) ($this->config['timeout'] ?? 3))
                ->get($this->endpoint('/healthz'));

            return $response->successful();
        } catch (Throwable $e) {
            // Expected when Milvus is simply not running locally.
            Log::debug('Milvus availability probe failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function upsert(
        int $userId,
        string $sourceType,
        int $sourceId,
        string $content,
        array $vector,
        ?string $model = null,
        ?string $sourceUpdatedAt = null
    ): void {
        // Always mirror locally so retrieval keeps working if Milvus drops.
        $this->fallback->upsert($userId, $sourceType, $sourceId, $content, $vector, $model, $sourceUpdatedAt);

        if (! $this->isAvailable()) {
            return;
        }

        try {
            Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->post($this->endpoint('/v2/vectordb/entities/upsert'), [
                    'collectionName' => $this->config['collection'],
                    'data' => [[
                        'id' => $this->key($sourceType, $sourceId),
                        'user_id' => $userId,
                        'vector' => $vector,
                        'text' => $content,
                        'source_type' => $sourceType,
                        'source_id' => $sourceId,
                    ]],
                ]);
        } catch (Throwable $e) {
            Log::warning('Milvus upsert failed; local mirror retained', ['error' => $e->getMessage()]);
        }
    }

    public function delete(int $userId, string $sourceType, int $sourceId): void
    {
        $this->fallback->delete($userId, $sourceType, $sourceId);

        if (! $this->isAvailable()) {
            return;
        }

        try {
            Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->post($this->endpoint('/v2/vectordb/entities/delete'), [
                    'collectionName' => $this->config['collection'],
                    'filter' => sprintf('id == "%s"', $this->key($sourceType, $sourceId)),
                ]);
        } catch (Throwable $e) {
            Log::warning('Milvus delete failed', ['error' => $e->getMessage()]);
        }
    }

    public function deleteByType(int $userId, string $sourceType): void
    {
        $this->fallback->deleteByType($userId, $sourceType);
    }

    public function deleteByUser(int $userId): void
    {
        $this->fallback->deleteByUser($userId);
    }

    public function search(int $userId, array $vector, int $topK = 8, float $minScore = 0.0, ?array $sourceTypes = null): array
    {
        if (! $this->isAvailable()) {
            // Degrade to the local mirror rather than failing the request.
            return $this->fallback->search($userId, $vector, $topK, $minScore, $sourceTypes);
        }

        try {
            $filter = sprintf('user_id == %d', $userId);

            if ($sourceTypes) {
                $quoted = implode('", "', array_map('strval', $sourceTypes));
                $filter .= sprintf(' and source_type in ["%s"]', $quoted);
            }

            $response = Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->post($this->endpoint('/v2/vectordb/entities/search'), [
                    'collectionName' => $this->config['collection'],
                    'data' => [$vector],
                    'limit' => $topK,
                    'filter' => $filter,
                    'outputFields' => ['text', 'source_type', 'source_id'],
                ]);

            $rows = $response->json('data.0') ?? [];

            $results = [];

            foreach ($rows as $row) {
                $score = (float) ($row['distance'] ?? 0);

                if ($score < $minScore) {
                    continue;
                }

                $results[] = [
                    'source_type' => (string) ($row['source_type'] ?? ''),
                    'source_id' => (int) ($row['source_id'] ?? 0),
                    'content' => (string) ($row['text'] ?? ''),
                    'score' => round($score, 4),
                ];
            }

            return $results !== []
                ? $results
                : $this->fallback->search($userId, $vector, $topK, $minScore, $sourceTypes);
        } catch (Throwable $e) {
            Log::warning('Milvus search failed; using local mirror', ['error' => $e->getMessage()]);

            return $this->fallback->search($userId, $vector, $topK, $minScore, $sourceTypes);
        }
    }

    public function count(int $userId, ?string $sourceType = null): int
    {
        return $this->fallback->count($userId, $sourceType);
    }

    public function getIndexedModel(int $userId): ?string
    {
        return $this->fallback->getIndexedModel($userId);
    }

    protected function endpoint(string $path): string
    {
        $host = $this->config['host'] ?? '127.0.0.1';
        $port = $this->config['port'] ?? 19530;

        return sprintf('http://%s:%d%s', $host, $port, $path);
    }

    protected function key(string $sourceType, int $sourceId): string
    {
        return $sourceType . ':' . $sourceId;
    }
}
