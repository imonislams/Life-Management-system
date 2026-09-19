<?php

namespace App\Services\AI\Vector;

/**
 * Provider-neutral contract for the semantic vector index.
 *
 * This interface is the one the RAG pipeline depends on, so a different local
 * vector engine can be introduced later without touching any controller or
 * service. The default implementation is QdrantVectorSearch (a free,
 * self-hosted vector database).
 *
 * CRITICAL CONTRACT: every read operation MUST be scoped to a single user id.
 * Implementations must never perform a global semantic search over private
 * personal records.
 */
interface VectorSearchInterface
{
    /**
     * Whether the underlying vector database is reachable.
     *
     * Returning false must never throw: the AI layer degrades gracefully and
     * MySQL-backed features keep working.
     */
    public function isAvailable(): bool;

    /**
     * Detail of the underlying store, for the Settings -> AI status panel.
     *
     * @return array{driver:string, collection:string|null, url:string|null, reachable:bool}
     */
    public function describe(): array;

    /**
     * Insert or replace the vector for a source record.
     *
     * @param  array<int, float>  $vector
     */
    public function upsert(
        int $userId,
        string $sourceType,
        int $sourceId,
        string $content,
        array $vector,
        ?string $model = null,
        ?string $sourceUpdatedAt = null
    ): void;

    /**
     * Remove the vector for a single source record.
     */
    public function delete(int $userId, string $sourceType, int $sourceId): void;

    /**
     * Remove every vector belonging to a source type for one user.
     */
    public function deleteByType(int $userId, string $sourceType): void;

    /**
     * Remove every vector owned by one user.
     */
    public function deleteByUser(int $userId): void;

    /**
     * Semantic search, ALWAYS scoped to one user.
     *
     * @param  array<int, float>  $vector  The query vector.
     * @param  array<int, string>|null  $sourceTypes  Optional type filter.
     * @return array<int, array{source_type:string, source_id:int, content:string, score:float}>
     */
    public function search(int $userId, array $vector, int $topK = 8, float $minScore = 0.0, ?array $sourceTypes = null): array;

    /**
     * Count indexed vectors for a user (optionally per source type).
     */
    public function count(int $userId, ?string $sourceType = null): int;

    /**
     * The embedding model recorded for this user's index, when known.
     */
    public function getIndexedModel(int $userId): ?string;

    /**
     * Ensure the backing collection exists and matches the given dimensions.
     */
    public function ensureCollection(int $dimensions): void;

    /**
     * Cheap reachability probe. Never throws.
     */
    public function healthCheck(): bool;
}
