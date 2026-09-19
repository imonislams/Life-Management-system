<?php

namespace App\Services\AI\Contracts;

/**
 * Abstraction over the embedding model. Kept separate from the LLM provider so
 * the embedding provider/model can be swapped independently.
 */
interface EmbeddingServiceInterface
{
    /**
     * Whether embeddings can currently be produced.
     */
    public function isAvailable(): bool;

    /**
     * The embedding model identifier in use.
     */
    public function model(): string;

    /**
     * The dimensionality of the vectors this service produces.
     */
    public function dimensions(): int;

    /**
     * Embed a single piece of text into a float vector.
     *
     * @return array<int, float>
     *
     * @throws \App\Services\AI\Exceptions\AIServiceException
     */
    public function embed(string $text): array;

    /**
     * Embed many texts. Returns one vector per input, in order.
     *
     * @param  array<int, string>  $texts
     * @return array<int, array<int, float>>
     *
     * @throws \App\Services\AI\Exceptions\AIServiceException
     */
    public function embedMany(array $texts): array;
}
