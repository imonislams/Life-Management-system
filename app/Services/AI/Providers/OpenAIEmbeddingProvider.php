<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\EmbeddingServiceInterface;
use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * OpenAI embeddings provider.
 *
 * Kept separate from the chat provider so the embedding model can be changed
 * (or replaced by a different vendor) entirely independently.
 */
class OpenAIEmbeddingProvider implements EmbeddingServiceInterface
{
    public function __construct(protected array $config) {}

    public function isAvailable(): bool
    {
        return ! empty($this->config['api_key']);
    }

    public function model(): string
    {
        return (string) ($this->config['embedding_model'] ?? 'text-embedding-3-small');
    }

    public function dimensions(): int
    {
        return (int) ($this->config['embedding_dimensions'] ?? 1536);
    }

    public function embed(string $text): array
    {
        $vectors = $this->embedMany([$text]);

        return $vectors[0];
    }

    public function embedMany(array $texts): array
    {
        if (! $this->isAvailable()) {
            throw new AIServiceException(AIServiceException::CODE_NOT_CONFIGURED);
        }

        if ($texts === []) {
            return [];
        }

        $baseUrl = rtrim((string) ($this->config['base_url'] ?? 'https://api.openai.com/v1'), '/');
        $timeout = (int) ($this->config['timeout'] ?? 30);

        try {
            $response = Http::withToken((string) $this->config['api_key'])
                ->acceptJson()
                ->asJson()
                ->timeout($timeout)
                ->post($baseUrl . '/embeddings', [
                    'model' => $this->model(),
                    'input' => array_values($texts),
                ]);
        } catch (Throwable $e) {
            Log::warning('AI embedding request failed', ['error' => $e->getMessage()]);

            throw new AIServiceException(
                str_contains(strtolower($e->getMessage()), 'timed out')
                    ? AIServiceException::CODE_TIMEOUT
                    : AIServiceException::CODE_NETWORK,
                '',
                $e
            );
        }

        if ($response->status() === 401 || $response->status() === 403) {
            throw new AIServiceException(AIServiceException::CODE_UNAUTHORIZED);
        }

        if ($response->status() === 429) {
            throw new AIServiceException(AIServiceException::CODE_RATE_LIMITED);
        }

        if (! $response->successful()) {
            throw new AIServiceException(AIServiceException::CODE_PROVIDER_ERROR);
        }

        $rows = data_get($response->json(), 'data');

        if (! is_array($rows) || count($rows) !== count($texts)) {
            throw new AIServiceException(AIServiceException::CODE_MALFORMED_RESPONSE);
        }

        $vectors = [];

        foreach ($rows as $row) {
            $vector = data_get($row, 'embedding');

            if (! is_array($vector) || $vector === []) {
                throw new AIServiceException(AIServiceException::CODE_MALFORMED_RESPONSE);
            }

            $vectors[] = array_map('floatval', $vector);
        }

        return $vectors;
    }
}
