<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\LLMProviderInterface;

/**
 * Deterministic, offline LLM provider.
 *
 * Used by the automated test-suite and for local development without an API
 * key. It performs NO network calls and always returns a grounded, clearly
 * labelled answer so the pipeline (retrieval -> context -> response) can be
 * exercised end to end.
 */
class NullLLMProvider implements LLMProviderInterface, AIProviderInterface
{
    public function __construct(protected array $config = []) {}

    public function isAvailable(): bool
    {
        return true;
    }

    public function model(): string
    {
        return (string) ($this->config['model'] ?? 'null-model');
    }

    public function complete(string $system, array $messages): string
    {
        // Return the last user turn echoed within a grounded frame. This makes
        // tests deterministic while still proving context was assembled.
        $lastUser = '';

        foreach (array_reverse($messages) as $message) {
            if (($message['role'] ?? null) === 'user') {
                $lastUser = (string) ($message['content'] ?? '');
                break;
            }
        }

        $hasContext = str_contains($system, 'RETRIEVED CONTEXT');

        return $hasContext
            ? 'Based on your recorded data, here is what I found regarding: ' . trim($lastUser)
            : 'I do not have enough recorded data to answer that yet.';
    }

    // -----------------------------------------------------------------
    // AIProviderInterface
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
        return true;
    }

    public function installedModels(): array
    {
        return [$this->model()];
    }
}
