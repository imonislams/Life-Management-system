<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\LLMProviderInterface;
use App\Services\AI\Exceptions\AIServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * OpenAI-backed LLM provider (chat completions).
 *
 * All network calls happen server-side. The API key is read from configuration
 * (which is sourced from the protected server environment) and is never logged,
 * serialized or exposed to the browser.
 */
class OpenAIProvider implements LLMProviderInterface, AIProviderInterface
{
    public function __construct(protected array $config) {}

    public function isAvailable(): bool
    {
        return ! empty($this->config['api_key']);
    }

    public function model(): string
    {
        return (string) ($this->config['model'] ?? 'gpt-4o-mini');
    }

    // -----------------------------------------------------------------
    // LLM
    // -----------------------------------------------------------------

    public function complete(string $system, array $messages): string
    {
        $this->assertConfigured();

        $payload = [
            'model' => $this->model(),
            'messages' => array_merge(
                [['role' => 'system', 'content' => $system]],
                $messages
            ),
            'temperature' => 0.2,
        ];

        $response = $this->post('/chat/completions', $payload);

        $content = data_get($response, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new AIServiceException(AIServiceException::CODE_MALFORMED_RESPONSE);
        }

        return trim($content);
    }

    // -----------------------------------------------------------------
    // AIProviderInterface (generate / chat / healthCheck / installedModels)
    // -----------------------------------------------------------------

    public function generate(string $system, array $messages): string
    {
        return $this->complete($system, $messages);
    }

    public function chat(array $messages, string $system = ''): string
    {
        return $this->complete($system, $messages);
    }

    public function healthCheck(): bool
    {
        return $this->isAvailable();
    }

    public function installedModels(): array
    {
        return $this->isAvailable() ? [$this->model()] : [];
    }

    // -----------------------------------------------------------------
    // Internals
    // -----------------------------------------------------------------

    public function assertConfigured(): void
    {
        if (! $this->isAvailable()) {
            throw new AIServiceException(AIServiceException::CODE_NOT_CONFIGURED);
        }
    }

    /**
     * Perform a provider request and translate every failure mode into a safe
     * AIServiceException.
     */
    protected function post(string $path, array $payload): array
    {
        $baseUrl = rtrim((string) ($this->config['base_url'] ?? 'https://api.openai.com/v1'), '/');
        $timeout = (int) ($this->config['timeout'] ?? 30);

        try {
            $response = Http::withToken((string) $this->config['api_key'])
                ->acceptJson()
                ->asJson()
                ->timeout($timeout)
                ->post($baseUrl . $path, $payload);
        } catch (Throwable $e) {
            // Connection / DNS / timeout failures. Never surface the exception
            // message to the user.
            Log::warning('AI provider request failed', ['path' => $path, 'error' => $e->getMessage()]);

            throw new AIServiceException(
                $this->isTimeout($e) ? AIServiceException::CODE_TIMEOUT : AIServiceException::CODE_NETWORK,
                '',
                $e
            );
        }

        if ($response->status() === 401 || $response->status() === 403) {
            Log::warning('AI provider rejected credentials', ['status' => $response->status()]);

            throw new AIServiceException(AIServiceException::CODE_UNAUTHORIZED);
        }

        if ($response->status() === 429) {
            Log::info('AI provider rate limited the request');

            throw new AIServiceException(AIServiceException::CODE_RATE_LIMITED);
        }

        if ($response->serverError()) {
            Log::warning('AI provider server error', ['status' => $response->status()]);

            throw new AIServiceException(AIServiceException::CODE_PROVIDER_ERROR);
        }

        if (! $response->successful()) {
            Log::warning('AI provider returned an unsuccessful response', ['status' => $response->status()]);

            throw new AIServiceException(AIServiceException::CODE_PROVIDER_ERROR);
        }

        $json = $response->json();

        if (! is_array($json)) {
            throw new AIServiceException(AIServiceException::CODE_MALFORMED_RESPONSE);
        }

        return $json;
    }

    protected function isTimeout(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'timed out') || str_contains($message, 'timeout');
    }
}
