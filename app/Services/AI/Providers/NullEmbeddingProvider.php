<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\EmbeddingServiceInterface;

/**
 * Deterministic, offline embedding provider.
 *
 * Produces a stable hashed bag-of-words vector. It is NOT semantically as rich
 * as a transformer embedding, but it is:
 *   - dependency free (no model download, no network),
 *   - deterministic (identical text -> identical vector),
 *   - good enough to prove and test the full semantic retrieval pipeline.
 *
 * Production deployments should select a real embedding provider (e.g. openai).
 */
class NullEmbeddingProvider implements EmbeddingServiceInterface
{
    public function __construct(protected array $config = []) {}

    public function isAvailable(): bool
    {
        return true;
    }

    public function model(): string
    {
        return (string) ($this->config['embedding_model'] ?? 'null-embedding');
    }

    public function dimensions(): int
    {
        return (int) ($this->config['embedding_dimensions'] ?? 1536);
    }

    public function embed(string $text): array
    {
        $dimensions = $this->dimensions();
        $vector = array_fill(0, $dimensions, 0.0);

        foreach ($this->tokenize($text) as $token) {
            // Hash each token into a stable bucket and accumulate.
            $bucket = crc32($token) % $dimensions;
            $vector[$bucket] += 1.0;
        }

        return $this->normalize($vector);
    }

    public function embedMany(array $texts): array
    {
        return array_map(fn(string $text) => $this->embed($text), array_values($texts));
    }

    /**
     * Lowercase word tokens, split on anything non-alphanumeric.
     *
     * @return array<int, string>
     */
    protected function tokenize(string $text): array
    {
        $text = mb_strtolower($text);
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        return $parts === false ? [] : $parts;
    }

    /**
     * L2-normalise so cosine similarity behaves like a dot product.
     *
     * @param  array<int, float>  $vector
     * @return array<int, float>
     */
    protected function normalize(array $vector): array
    {
        $sumSquares = 0.0;

        foreach ($vector as $value) {
            $sumSquares += $value * $value;
        }

        $norm = sqrt($sumSquares);

        if ($norm <= 0.0) {
            return $vector;
        }

        return array_map(fn(float $value) => $value / $norm, $vector);
    }
}
