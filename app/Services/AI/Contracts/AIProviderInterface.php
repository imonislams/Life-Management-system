<?php

namespace App\Services\AI\Contracts;

/**
 * Abstraction over the text-generation LLM so the provider can be replaced
 * without touching application code.
 *
 * The primary implementation is the fully local, 100% free OllamaAIProvider.
 * No paid AI API is involved anywhere in this application.
 */
interface AIProviderInterface
{
    /**
     * Whether this provider is usable (e.g. the local runtime is reachable).
     */
    public function isAvailable(): bool;

    /**
     * The model identifier currently in use.
     */
    public function model(): string;

    /**
     * Generate a completion.
     *
     * @param  string  $system  The trusted system/developer instruction block.
     * @param  array<int, array{role:string, content:string}>  $messages  Conversation turns.
     * @return string The assistant's plain-text answer.
     *
     * @throws \App\Services\AI\Exceptions\AIServiceException
     */
    public function generate(string $system, array $messages): string;

    /**
     * Alias for generate() kept for readability at call-sites that describe a
     * chat exchange. Both produce the same grounded answer.
     *
     * @param  array<int, array{role:string, content:string}>  $messages
     */
    public function chat(array $messages, string $system = ''): string;

    /**
     * Cheap reachability probe. Never throws.
     */
    public function healthCheck(): bool;

    /**
     * Names of every model installed in the local runtime. Empty on failure.
     *
     * @return array<int, string>
     */
    public function installedModels(): array;
}
