<?php

namespace App\Services\AI\Vector;

use App\Services\AI\Contracts\VectorRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Qdrant vector-database adapter (the default store).
 *
 * Qdrant is free and self-hosted (default http://127.0.0.1:6333): no paid hosted
 * account is ever required. The official PHP SDK is a heavy separate dependency,
 * so this adapter talks to Qdrant's stable REST API through Laravel's HTTP
 * client and therefore needs no extra Composer package.
 *
 * Design notes:
 *   - Every point id is a deterministic per-user key so re-indexing is idempotent.
 *   - Every write/delete ALSO mirrors into the MySQL `ai_embeddings` table, so
 *     retrieval keeps working (and Qdrant can be fully rebuilt) if Qdrant is lost.
 *   - All reads are filtered by user_id: a user can never search another user's
 *     vectors. The payload always carries user_id / source_type / source_id.
 *   - If Qdrant is unreachable every operation degrades to the MySQL mirror and
 *     never throws into the calling application code.
 */
class QdrantVectorSearch implements VectorSearchInterface, VectorRepositoryInterface
{
    public function __construct(
        protected array $config,
        protected DatabaseVectorRepository $fallback
    ) {}

    public function isAvailable(): bool
    {
        return $this->healthCheck();
    }

    public function healthCheck(): bool
    {
        try {
            return Http::timeout(3)->get($this->baseUrl() . '/healthz')->successful()
                || Http::timeout(3)->get($this->baseUrl() . '/')->successful();
        } catch (Throwable $e) {
            // Expected whenever Qdrant is simply not running locally.
            Log::debug('Qdrant health probe failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    public function describe(): array
    {
        return [
            'driver' => 'qdrant',
            'collection' => (string) ($this->config['collection'] ?? 'life_management'),
            'url' => $this->baseUrl(),
            'reachable' => $this->healthCheck(),
        ];
    }

    public function ensureCollection(int $dimensions): void
    {
        if (! $this->healthCheck()) {
            return;
        }

        try {
            // Exists already? Nothing to do.
            $existing = Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->get($this->collectionUrl());

            if ($existing->successful()) {
                return;
            }

            Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->put($this->collectionUrl(), [
                    'vectors' => [
                        'size' => $dimensions,
                        'distance' => (string) ($this->config['distance'] ?? 'Cosine'),
                    ],
                ]);
        } catch (Throwable $e) {
            Log::warning('Qdrant ensureCollection failed', ['error' => $e->getMessage()]);
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
        // Always mirror locally so retrieval keeps working if Qdrant drops, and
        // so the index can be rebuilt from MySQL at any time.
        if ((bool) ($this->config['mirror_to_database'] ?? true)) {
            $this->fallback->upsert($userId, $sourceType, $sourceId, $content, $vector, $model, $sourceUpdatedAt);
        }

        if (! $this->healthCheck()) {
            return;
        }

        try {
            $this->ensureCollection(count($vector));

            Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->put($this->baseUrl() . '/collections/' . $this->collection() . '/points?wait=true', [
                    'points' => [[
                        'id' => $this->pointId($userId, $sourceType, $sourceId),
                        'vector' => array_values($vector),
                        'payload' => [
                            'user_id' => $userId,
                            'source_type' => $sourceType,
                            'source_id' => $sourceId,
                            'content' => $this->truncate($content),
                            'model' => $model,
                            'source_updated_at' => $sourceUpdatedAt,
                        ],
                    ]],
                ]);
        } catch (Throwable $e) {
            Log::warning('Qdrant upsert failed; local mirror retained', ['error' => $e->getMessage()]);
        }
    }

    public function delete(int $userId, string $sourceType, int $sourceId): void
    {
        if ((bool) ($this->config['mirror_to_database'] ?? true)) {
            $this->fallback->delete($userId, $sourceType, $sourceId);
        }

        if (! $this->healthCheck()) {
            return;
        }

        try {
            Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->post($this->baseUrl() . '/collections/' . $this->collection() . '/points/delete?wait=true', [
                    'points' => [$this->pointId($userId, $sourceType, $sourceId)],
                ]);
        } catch (Throwable $e) {
            Log::warning('Qdrant delete failed', ['error' => $e->getMessage()]);
        }
    }

    public function deleteByType(int $userId, string $sourceType): void
    {
        if ((bool) ($this->config['mirror_to_database'] ?? true)) {
            $this->fallback->deleteByType($userId, $sourceType);
        }

        $this->deleteByFilter($userId, $sourceType);
    }

    public function deleteByUser(int $userId): void
    {
        if ((bool) ($this->config['mirror_to_database'] ?? true)) {
            $this->fallback->deleteByUser($userId);
        }

        $this->deleteByFilter($userId, null);
    }

    protected function deleteByFilter(int $userId, ?string $sourceType): void
    {
        if (! $this->healthCheck()) {
            return;
        }

        try {
            $must = [['key' => 'user_id', 'match' => ['value' => $userId]]];

            if ($sourceType !== null) {
                $must[] = ['key' => 'source_type', 'match' => ['value' => $sourceType]];
            }

            Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->post($this->baseUrl() . '/collections/' . $this->collection() . '/points/delete?wait=true', [
                    'filter' => ['must' => $must],
                ]);
        } catch (Throwable $e) {
            Log::warning('Qdrant filtered delete failed', ['error' => $e->getMessage()]);
        }
    }

    public function search(int $userId, array $vector, int $topK = 8, float $minScore = 0.0, ?array $sourceTypes = null): array
    {
        if (! $this->healthCheck()) {
            // Degrade to the local mirror rather than failing the request.
            return $this->fallback->search($userId, $vector, $topK, $minScore, $sourceTypes);
        }

        try {
            $must = [['key' => 'user_id', 'match' => ['value' => $userId]]];

            if ($sourceTypes) {
                $must[] = ['key' => 'source_type', 'match' => ['any' => array_values($sourceTypes)]];
            }

            $response = Http::timeout((int) ($this->config['timeout'] ?? 5))
                ->acceptJson()
                ->asJson()
                ->post($this->baseUrl() . '/collections/' . $this->collection() . '/points/search', [
                    'vector' => array_values($vector),
                    'limit' => max(1, $topK),
                    'with_payload' => true,
                    'filter' => ['must' => $must],
                ]);

            if (! $response->successful()) {
                return $this->fallback->search($userId, $vector, $topK, $minScore, $sourceTypes);
            }

            $results = [];

            foreach ((array) $response->json('result', []) as $row) {
                $score = (float) ($row['score'] ?? 0);

                if ($score < $minScore) {
                    continue;
                }

                $payload = (array) ($row['payload'] ?? []);

                // Defensive re-check: a Qdrant filter is authoritative, but we
                // never trust a payload row that claims a different owner.
                if ((int) ($payload['user_id'] ?? 0) !== $userId) {
                    continue;
                }

                $results[] = [
                    'source_type' => (string) ($payload['source_type'] ?? ''),
                    'source_id' => (int) ($payload['source_id'] ?? 0),
                    'content' => (string) ($payload['content'] ?? ''),
                    'score' => round($score, 4),
                ];
            }

            return $results !== []
                ? $results
                : $this->fallback->search($userId, $vector, $topK, $minScore, $sourceTypes);
        } catch (Throwable $e) {
            Log::warning('Qdrant search failed; using local mirror', ['error' => $e->getMessage()]);

            return $this->fallback->search($userId, $vector, $topK, $minScore, $sourceTypes);
        }
    }

    public function count(int $userId, ?string $sourceType = null): int
    {
        // The MySQL mirror is the authoritative count (Qdrant is only an index).
        return $this->fallback->count($userId, $sourceType);
    }

    public function getIndexedModel(int $userId): ?string
    {
        return $this->fallback->getIndexedModel($userId);
    }

    // ---------------------------------------------------------------------
    // Internals
    // ---------------------------------------------------------------------

    protected function baseUrl(): string
    {
        return rtrim((string) ($this->config['url'] ?? 'http://127.0.0.1:6333'), '/');
    }

    protected function collection(): string
    {
        return (string) ($this->config['collection'] ?? 'life_management');
    }

    protected function collectionUrl(): string
    {
        return $this->baseUrl() . '/collections/' . $this->collection();
    }

    /**
     * A stable, collision-free numeric point id derived from the source record
     * and the owning user, so re-indexing the same row never duplicates it.
     */
    protected function pointId(int $userId, string $sourceType, int $sourceId): string
    {
        $key = $userId . ':' . $sourceType . ':' . $sourceId;

        // Qdrant accepts unsigned integers or UUIDs as ids. Use a UUIDv5-style
        // hash so ids stay stable across reindex runs.
        $hash = md5($key);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }

    protected function truncate(string $content, int $limit = 2000): string
    {
        return mb_strlen($content) > $limit ? mb_substr($content, 0, $limit) : $content;
    }
}
