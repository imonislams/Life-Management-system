<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\EmbeddingServiceInterface;
use App\Services\AI\Ollama\ModelSelector;
use App\Services\AI\Ollama\OllamaClient;

/**
 * Fully local embedding provider backed by Ollama.
 *
 * Embeddings are computed on the user's own machine using a local model such as
 * "nomic-embed-text". No paid embedding API is ever contacted.
 */
class OllamaEmbeddingProvider implements EmbeddingServiceInterface
{
    public function __construct(
        protected OllamaClient $client,
        protected ModelSelector $selector,
        protected array $config
    ) {}

    public function isAvailable(): bool
    {
        return $this->client->isReachable();
    }

    public function model(): string
    {
        return $this->selector->selectEmbeddingModel((string) ($this->config['embedding_model'] ?? 'auto'));
    }

    public function dimensions(): int
    {
        return $this->selector->embeddingDimensions($this->model());
    }

    public function embed(string $text): array
    {
        return $this->client->embed($this->model(), $text);
    }

    public function embedMany(array $texts): array
    {
        if ($texts === []) {
            return [];
        }

        $vectors = [];

        foreach (array_values($texts) as $text) {
            $vectors[] = $this->embed((string) $text);
        }

        return $vectors;
    }
}
