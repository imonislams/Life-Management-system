<?php

namespace App\Services\AI\Ollama;

use App\Services\AI\Hardware\HardwareDetector;
use Illuminate\Support\Facades\Log;

/**
 * Hardware-aware local model selection.
 *
 * The application must not assume a powerful GPU. Given the list of models a
 * user actually has installed in Ollama, this class picks the best one that
 * comfortably fits within the detected RAM budget; if nothing fits it falls back
 * to the smallest installed model.
 *
 * The chosen model always remains user-overridable through OLLAMA_MODEL in
 * .env, so the application is never permanently pinned to one model.
 */
class ModelSelector
{
    public function __construct(
        protected HardwareDetector $hardware,
        protected OllamaClient $client,
        protected array $config
    ) {}

    /**
     * Resolve the chat model to use.
     *
     * @param  string  $configured  The raw OLLAMA_MODEL value ("auto" or explicit).
     */
    public function selectChatModel(string $configured): string
    {
        if ($configured !== '' && strtolower($configured) !== 'auto') {
            return $configured;
        }

        if (! (bool) ($this->config['auto_select'] ?? true)) {
            return $this->firstInstalled($this->chatCandidates()) ?? $this->smallestInstalled() ?? 'llama3.2:3b';
        }

        return $this->bestFit($this->chatCandidates()) ?? $this->smallestInstalled() ?? 'llama3.2:3b';
    }

    /**
     * Resolve the embedding model to use.
     */
    public function selectEmbeddingModel(string $configured): string
    {
        if ($configured !== '' && strtolower($configured) !== 'auto') {
            return $configured;
        }

        return $this->firstInstalled($this->embeddingCandidates()) ?? 'nomic-embed-text';
    }

    /**
     * Reported dimensions for an embedding model, with a configured fallback.
     */
    public function embeddingDimensions(string $model): int
    {
        $map = (array) config('ai.hardware.embedding_dimensions', []);

        foreach ($map as $name => $dimensions) {
            if ($name === 'default') {
                continue;
            }

            // Match "nomic-embed-text" and "nomic-embed-text:latest" alike.
            if ($model === $name || str_starts_with($model, $name . ':')) {
                return (int) $dimensions;
            }
        }

        return (int) ($map['default'] ?? 768);
    }

    /**
     * Pick the largest installed candidate that fits in the RAM budget, stepping
     * down to lighter models when memory is tight. Candidates are ordered from
     * smallest to largest requirement.
     *
     * @param  array<string, int>  $candidates  name => min RAM (GB)
     */
    protected function bestFit(array $candidates): ?string
    {
        $installed = $this->installed();
        $ram = $this->hardware->detectRamGb();

        // Walk from the heaviest to the lightest and take the first that both
        // fits and is installed, so we prefer quality while staying safe.
        $ordered = $candidates;
        arsort($ordered);

        foreach ($ordered as $name => $required) {
            if (! $this->isInstalled($name, $installed)) {
                continue;
            }

            // Unknown RAM: accept the first installed candidate (prefer heaviest).
            if ($ram === null) {
                return $name;
            }

            // Leave headroom for the OS and the app itself.
            if ($ram >= $required + 2) {
                return $name;
            }
        }

        return null;
    }

    /**
     * @param  array<string, int>  $candidates
     */
    protected function firstInstalled(array $candidates): ?string
    {
        $installed = $this->installed();

        // Largest first for the best quality that is actually available.
        $ordered = $candidates;
        arsort($ordered);

        foreach ($ordered as $name => $required) {
            if ($this->isInstalled($name, $installed)) {
                return $name;
            }
        }

        return null;
    }

    protected function smallestInstalled(): ?string
    {
        // Only local models are considered: cloud models are excluded entirely
        // so automatic fallback never leaks data off the machine.
        $installed = array_values(array_filter(
            $this->installed(),
            fn (string $name) => ! $this->isCloudModel($name)
        ));

        if ($installed === []) {
            return null;
        }

        // Prefer a chat-style model name when the list contains embeddings too.
        foreach ($installed as $name) {
            if (stripos($name, 'embed') === false && stripos($name, 'minilm') === false) {
                return $name;
            }
        }

        return $installed[0];
    }

    /**
     * @return array<int, string>
     */
    protected function installed(): array
    {
        try {
            return $this->client->listModels();
        } catch (\Throwable $e) {
            Log::debug('ModelSelector: could not list Ollama models', ['error' => $e->getMessage()]);

            return [];
        }
    }

    protected function isInstalled(string $candidate, array $installed): bool
    {
        // Cloud-hosted Ollama models ("...:cloud") route data off the machine, so
        // they are NEVER eligible for automatic selection: the AI layer is strictly
        // 100% local. Such a model can still be chosen explicitly via OLLAMA_MODEL
        // if a user truly wants it, but the app never picks it on its own.
        if ($this->isCloudModel($candidate)) {
            return false;
        }

        foreach ($installed as $name) {
            if ($name === $candidate || str_starts_with($name, $candidate . ':')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether a model name refers to a cloud-hosted (non-local) model.
     */
    protected function isCloudModel(string $name): bool
    {
        $lower = strtolower($name);

        return str_contains($lower, ':cloud') || str_ends_with($lower, '-cloud');
    }

    /**
     * @return array<string, int>
     */
    protected function chatCandidates(): array
    {
        return (array) ($this->config['llm_candidates'] ?? []);
    }

    /**
     * @return array<string, int>
     */
    protected function embeddingCandidates(): array
    {
        return (array) ($this->config['embedding_candidates'] ?? []);
    }
}
