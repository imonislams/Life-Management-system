<?php

namespace App\Services\AI\Vector;

use App\Models\AiEmbedding;
use App\Services\AI\Contracts\VectorRepositoryInterface;

/**
 * MySQL-backed vector store (the default).
 *
 * Embeddings live in a normal table as JSON vectors and similarity is computed
 * in PHP over the user's own rows. This keeps the application runnable with no
 * extra server, while still honouring the VectorRepositoryInterface so a
 * dedicated vector database can be swapped in later.
 *
 * USER ISOLATION: every query below is wrapped in ownedBy($userId). No method
 * can ever read across users.
 */
class DatabaseVectorRepository implements VectorRepositoryInterface, \App\Services\AI\Vector\VectorSearchInterface
{
    public function isAvailable(): bool
    {
        // Always available: it is just MySQL, which the app already requires.
        return true;
    }

    public function healthCheck(): bool
    {
        // The MySQL-backed store is healthy as long as the database answers.
        try {
            AiEmbedding::query()->limit(1)->count();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function describe(): array
    {
        return [
            'driver' => 'database',
            'collection' => 'ai_embeddings',
            'url' => null,
            'reachable' => $this->healthCheck(),
        ];
    }

    public function ensureCollection(int $dimensions): void
    {
        // Nothing to provision: the table is created by migrations.
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
        AiEmbedding::updateOrCreate(
            [
                'user_id' => $userId,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
            ],
            [
                'vector_key' => AiEmbedding::vectorKeyFor($sourceType, $sourceId),
                'content' => $this->truncate($content),
                'embedding' => $vector,
                'embedding_model' => $model,
                'embedding_dimensions' => count($vector),
                'source_updated_at' => $sourceUpdatedAt,
            ]
        );
    }

    public function delete(int $userId, string $sourceType, int $sourceId): void
    {
        AiEmbedding::ownedBy($userId)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->delete();
    }

    public function deleteByType(int $userId, string $sourceType): void
    {
        AiEmbedding::ownedBy($userId)->where('source_type', $sourceType)->delete();
    }

    public function deleteByUser(int $userId): void
    {
        AiEmbedding::ownedBy($userId)->delete();
    }

    public function search(int $userId, array $vector, int $topK = 8, float $minScore = 0.0, ?array $sourceTypes = null): array
    {
        $query = AiEmbedding::ownedBy($userId)->whereNotNull('embedding');

        if ($sourceTypes) {
            $query->whereIn('source_type', $sourceTypes);
        }

        // The user's own candidate set only. Bounded so a very large personal
        // history cannot blow up the request.
        $candidates = $query->limit(2000)->get();

        $scored = [];

        foreach ($candidates as $candidate) {
            $embedding = $candidate->embedding;

            if (! is_array($embedding) || $embedding === []) {
                continue;
            }

            $score = $this->cosineSimilarity($vector, $embedding);

            if ($score < $minScore) {
                continue;
            }

            $scored[] = [
                'source_type' => $candidate->source_type,
                'source_id' => (int) $candidate->source_id,
                'content' => (string) $candidate->content,
                'score' => round($score, 4),
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, max(1, $topK));
    }

    public function count(int $userId, ?string $sourceType = null): int
    {
        $query = AiEmbedding::ownedBy($userId);

        if ($sourceType) {
            $query->where('source_type', $sourceType);
        }

        return $query->count();
    }

    public function getIndexedModel(int $userId): ?string
    {
        return AiEmbedding::ownedBy($userId)->whereNotNull('embedding_model')->value('embedding_model');
    }

    /**
     * Cosine similarity between two equal-or-unequal length vectors.
     * Shorter vectors are compared only over their shared prefix.
     *
     * @param  array<int, float>  $a
     * @param  array<int, float>  $b
     */
    protected function cosineSimilarity(array $a, array $b): float
    {
        $a = array_values($a);
        $b = array_values($b);

        $count = min(count($a), count($b));

        if ($count === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $av = (float) $a[$i];
            $bv = (float) $b[$i];

            $dot += $av * $bv;
            $normA += $av * $av;
            $normB += $bv * $bv;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    protected function truncate(string $content, int $limit = 2000): string
    {
        return mb_strlen($content) > $limit ? mb_substr($content, 0, $limit) : $content;
    }
}
