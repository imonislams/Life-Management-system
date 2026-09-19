<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Ollama\ModelSelector;
use App\Services\AI\Ollama\OllamaClient;

/**
 * Fully local, 100% free LLM provider backed by the Ollama runtime.
 *
 * Every completion is generated on the user's own machine: no API key, no paid
 * API, no data leaving the host. The concrete model is resolved through the
 * hardware-aware ModelSelector, so the application is never permanently pinned
 * to a single model.
 */
class OllamaAIProvider implements AIProviderInterface
{
    public function __construct(
        protected OllamaClient $client,
        protected ModelSelector $selector,
        protected array $config
    ) {}

    /**
     * The provider is "available" when Ollama is reachable. It deliberately does
     * NOT require any credential.
     */
    public function isAvailable(): bool
    {
        return $this->client->isReachable();
    }

    public function model(): string
    {
        return $this->selector->selectChatModel((string) ($this->config['model'] ?? 'auto'));
    }

    public function generate(string $system, array $messages): string
    {
        $conversation = [];

        if (trim($system) !== '') {
            $conversation[] = ['role' => 'system', 'content' => $system];
        }

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $content = (string) ($message['content'] ?? '');

            if ($content === '') {
                continue;
            }

            // Ollama understands system/user/assistant roles; anything else is
            // treated as a user turn so no content is silently dropped.
            $conversation[] = [
                'role' => in_array($role, ['system', 'user', 'assistant'], true) ? $role : 'user',
                'content' => $content,
            ];
        }

        // Low temperature keeps grounded, factual answers stable.
        return $this->client->chat($this->model(), $conversation, ['temperature' => 0.2]);
    }

    public function chat(array $messages, string $system = ''): string
    {
        return $this->generate($system, $messages);
    }

    public function healthCheck(): bool
    {
        return $this->client->isReachable();
    }

    public function installedModels(): array
    {
        return $this->client->listModels();
    }
}
