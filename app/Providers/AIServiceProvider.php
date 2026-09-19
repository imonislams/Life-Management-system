<?php

namespace App\Providers;

use App\Observers\AI\EmbeddingObserver;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\EmbeddingServiceInterface;
use App\Services\AI\Contracts\LLMProviderInterface;
use App\Services\AI\Contracts\VectorRepositoryInterface;
use App\Services\AI\Hardware\HardwareDetector;
use App\Services\AI\IndexingService;
use App\Services\AI\Ollama\OllamaClient;
use App\Services\AI\Ollama\ModelSelector;
use App\Services\AI\Providers\NullEmbeddingProvider;
use App\Services\AI\Providers\NullLLMProvider;
use App\Services\AI\Providers\OllamaAIProvider;
use App\Services\AI\Providers\OllamaEmbeddingProvider;
use App\Services\AI\Providers\OpenAIEmbeddingProvider;
use App\Services\AI\Providers\OpenAIProvider;
use App\Services\AI\Vector\DatabaseVectorRepository;
use App\Services\AI\Vector\MilvusVectorRepository;
use App\Services\AI\Vector\QdrantVectorSearch;
use App\Services\AI\Vector\VectorSearchInterface;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the whole LOCAL AI layer into the container.
 *
 * Everything is resolved through the abstractions (AIProviderInterface,
 * EmbeddingServiceInterface, VectorSearchInterface) so no controller ever
 * contains vendor-specific (Ollama/Qdrant/OpenAI) logic, and the stack can be
 * swapped from configuration alone.
 *
 * Also registers the model observers that keep the vector index in sync, so a
 * record's embedding is refreshed on create/update/delete without any controller
 * being aware of the AI layer.
 */
class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // --- Shared, stateless infrastructure -------------------------------
        $this->app->singleton(HardwareDetector::class);

        // --- Local Ollama runtime ------------------------------------------
        $this->app->singleton(OllamaClient::class, function () {
            return new OllamaClient((array) config('ai.providers.ollama', []));
        });

        $this->app->singleton(ModelSelector::class, function ($app) {
            return new ModelSelector(
                $app->make(HardwareDetector::class),
                $app->make(OllamaClient::class),
                (array) config('ai.hardware', [])
            );
        });

        // --- LLM provider abstraction --------------------------------------
        $this->app->singleton(AIProviderInterface::class, fn($app) => $this->makeProvider($app));

        // Legacy alias kept so any existing code resolving the old interface
        // keeps working unchanged.
        $this->app->alias(AIProviderInterface::class, LLMProviderInterface::class);

        // --- Embedding abstraction -----------------------------------------
        $this->app->singleton(EmbeddingServiceInterface::class, fn($app) => $this->makeEmbeddings($app));

        // --- Vector store abstraction --------------------------------------
        $this->app->singleton(VectorSearchInterface::class, fn($app) => $this->makeVectorStore($app));

        // Existing code may depend on the older repository contract; point it at
        // the same resolved instance.
        $this->app->alias(VectorSearchInterface::class, VectorRepositoryInterface::class);
    }

    public function boot(): void
    {
        // Register the embedding-sync observers for every indexable model, but
        // only when the AI layer is enabled (otherwise the app behaves exactly
        // as before, with zero AI overhead).
        if (! config('ai.enabled')) {
            return;
        }

        foreach (IndexingService::indexableModels() as $modelClass) {
            $modelClass::observe(EmbeddingObserver::class);
        }
    }

    /**
     * Resolve the configured LLM provider.
     */
    protected function makeProvider($app): AIProviderInterface
    {
        $name = (string) config('ai.provider', 'ollama');
        $config = (array) config("ai.providers.{$name}", []);

        return match ($config['driver'] ?? $name) {
            'ollama' => new OllamaAIProvider(
                $app->make(OllamaClient::class),
                $app->make(ModelSelector::class),
                (array) config('ai.providers.ollama', [])
            ),
            'openai' => new OpenAIProvider($config),
            default => new NullLLMProvider($config),
        };
    }

    /**
     * Resolve the configured embedding provider.
     */
    protected function makeEmbeddings($app): EmbeddingServiceInterface
    {
        $name = (string) config('ai.provider', 'ollama');
        $config = (array) config("ai.providers.{$name}", []);

        return match ($config['driver'] ?? $name) {
            'ollama' => new OllamaEmbeddingProvider(
                $app->make(OllamaClient::class),
                $app->make(ModelSelector::class),
                (array) config('ai.providers.ollama', [])
            ),
            'openai' => new OpenAIEmbeddingProvider($config),
            default => new NullEmbeddingProvider($config),
        };
    }

    /**
     * Resolve the configured vector store. Every external store keeps a MySQL
     * mirror as a safe fallback.
     */
    protected function makeVectorStore($app): VectorSearchInterface
    {
        $driver = (string) config('ai.vector.driver', 'qdrant');
        $config = (array) config("ai.vector.stores.{$driver}", []);

        return match ($driver) {
            'qdrant' => new QdrantVectorSearch(
                $config,
                new DatabaseVectorRepository()
            ),
            'milvus' => new MilvusVectorRepository(
                $config,
                new DatabaseVectorRepository()
            ),
            default => new DatabaseVectorRepository(),
        };
    }
}
