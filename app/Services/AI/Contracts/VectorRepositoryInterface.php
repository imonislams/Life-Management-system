<?php

namespace App\Services\AI\Contracts;

/**
 * Abstraction over the vector store so the application is never tightly coupled
 * to a single vector database (MySQL-backed store today, Milvus or another
 * store tomorrow).
 *
 * CRITICAL CONTRACT: every read operation MUST be filtered by the supplied
 * user id. Implementations must never perform a global semantic search.
 */
interface VectorRepositoryInterface
{
    /**
     * Whether the underlying vector infrastructure is reachable.
     *
     * Returning false must never throw: the AI layer degrades gracefully and
     * MySQL-backed features keep working.
     */
    public function isAvailable(): bool;

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
     * Change the recorded embedding model (used when a provider/model changes).
     */
    public function getIndexedModel(int $userId): ?string;
}
