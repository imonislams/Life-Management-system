<?php

namespace App\Services\AI\Ollama;

use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin HTTP client for the local Ollama runtime.
 *
 * This is the ONLY place that speaks Ollama's wire protocol. Both the LLM
 * provider and the embedding service depend on it, so the protocol is defined
 * once and can evolve in a single spot.
 *
 * The runtime is 100% local (default http://127.0.0.1:11434): no API key, no
 * paid service, no data leaving the machine.
 */
class OllamaClient
{
    public function __construct(protected array $config) {}

    public function baseUrl(): string
    {
        return rtrim((string) ($this->config['base_url'] ?? 'http://127.0.0.1:11434'), '/');
    }

    public function timeout(): int
    {
        return (int) ($this->config['timeout'] ?? 120);
    }

    public function embeddingTimeout(): int
    {
        return (int) ($this->config['embedding_timeout'] ?? 60);
    }

    /**
     * Cheap reachability probe. Never throws.
     */
    public function isReachable(): bool
    {
        try {
            return Http::timeout(3)->get($this->baseUrl() . '/api/tags')->successful();
        } catch (Throwable $e) {
            Log::debug('Ollama reachability probe failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Every model installed locally. Empty array when the runtime is offline.
     *
     * @return array<int, string>
     */
    public function listModels(): array
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl() . '/api/tags');

            if (! $response->successful()) {
                return [];
            }

            $models = data_get($response->json(), 'models', []);

            if (! is_array($models)) {
                return [];
            }

            return array_values(array_filter(array_map(
                fn($model) => is_array($model) ? (string) ($model['name'] ?? '') : '',
                $models
            )));
        } catch (Throwable $e) {
            Log::debug('Ollama list models failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Generate a chat completion.
     *
     * @param  array<int, array{role:string, content:string}>  $messages
     * @throws AIServiceException
     */
    public function chat(string $model, array $messages, array $options = []): string
    {
        $payload = array_filter([
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
            'keep_alive' => $this->config['keep_alive'] ?? null,
            'options' => $options ?: null,
        ], fn($value) => $value !== null);

        $json = $this->post('/api/chat', $payload, $this->timeout());

        $content = data_get($json, 'message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new AIServiceException(AIServiceException::CODE_MALFORMED_RESPONSE);
        }

        return trim($content);
    }

    /**
     * Produce an embedding vector for a single input.
     *
     * @return array<int, float>
     * @throws AIServiceException
     */
    public function embed(string $model, string $input): array
    {
        $json = $this->post('/api/embeddings', [
            'model' => $model,
            'prompt' => $input,
        ], $this->embeddingTimeout());

        $vector = data_get($json, 'embedding');

        if (! is_array($vector) || $vector === []) {
            // Newer Ollama exposes the same result under /api/embed.
            $json = $this->post('/api/embed', [
                'model' => $model,
                'input' => $input,
            ], $this->embeddingTimeout());

            $vector = data_get($json, 'embeddings.0');
        }

        if (! is_array($vector) || $vector === []) {
            throw new AIServiceException(AIServiceException::CODE_MALFORMED_RESPONSE);
        }

        return array_map('floatval', $vector);
    }

    /**
     * @throws AIServiceException
     */
    protected function post(string $path, array $payload, int $timeout): array
    {
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout($timeout)
                ->post($this->baseUrl() . $path, $payload);
        } catch (Throwable $e) {
            Log::warning('Ollama request failed', ['path' => $path, 'error' => $e->getMessage()]);

            throw new AIServiceException(
                $this->isTimeout($e) ? AIServiceException::CODE_TIMEOUT : AIServiceException::CODE_NETWORK,
                '',
                $e
            );
        }

        if ($response->status() === 404) {
            // Model not pulled locally.
            throw new AIServiceException(AIServiceException::CODE_MODEL_MISSING);
        }

        if (! $response->successful()) {
            Log::warning('Ollama returned an unsuccessful response', ['path' => $path, 'status' => $response->status()]);

            throw new AIServiceException(AIServiceException::CODE_PROVIDER_ERROR);
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new AIServiceException(AIServiceException::CODE_MALFORMED_RESPONSE);
        }

        // Ollama reports per-request errors inside a 200 body.
        if (isset($json['error'])) {
            throw new AIServiceException(AIServiceException::CODE_PROVIDER_ERROR);
        }

        return $json;
    }

    protected function isTimeout(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'timed out') || str_contains($message, 'timeout');
    }
}
