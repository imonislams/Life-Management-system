<?php

namespace App\Services\AI\Contracts;

/**
 * Abstraction over the text-generation LLM so the provider can be replaced
 * without touching application code.
 */
interface LLMProviderInterface
{
    /**
     * Whether this provider is usable (e.g. has credentials configured).
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
    public function complete(string $system, array $messages): string;
}
