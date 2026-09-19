<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Layer Master Switch
    |--------------------------------------------------------------------------
    |
    | The AI assistant is an optional application layer on top of the Personal
    | Workspace. When disabled, every existing (non-AI) feature keeps working
    | exactly as before and the AI screens show a configuration notice.
    |
    */

    'enabled' => (bool) env('AI_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | LLM Provider
    |--------------------------------------------------------------------------
    |
    | The provider is resolved through the AIProviderInterface so it can be
    | swapped without touching the application code.
    |
    | The DEFAULT and RECOMMENDED provider is "ollama": a fully local, 100% free
    | LLM runtime. No OpenAI/Anthropic/Gemini key is ever required. The "null"
    | provider is a deterministic offline stub used by the test-suite.
    |
    | The "openai" provider is retained only for backwards compatibility; it is
    | never the default and the application works completely without it.
    |
    */

    'provider' => env('AI_PROVIDER', 'ollama'),

    'providers' => [

        // -------------------------------------------------------------------
        // Local Ollama runtime (default, 100% free, no API key)
        // -------------------------------------------------------------------
        'ollama' => [
            'driver' => 'ollama',
            'base_url' => env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'),
            // "auto" lets the application pick a sensible locally-installed
            // model based on the detected hardware at request time.
            'model' => env('OLLAMA_MODEL', 'auto'),
            'embedding_model' => env('OLLAMA_EMBEDDING_MODEL', 'auto'),
            // Generation can be slow on CPU-only machines; allow a generous
            // default so the first token of a small model is not cut off.
            'timeout' => (int) env('AI_TIMEOUT', 120),
            'embedding_timeout' => (int) env('OLLAMA_EMBEDDING_TIMEOUT', 60),
            'keep_alive' => env('OLLAMA_KEEP_ALIVE', '5m'),
        ],

        // A deterministic, offline provider used by the test-suite and by anyone
        // developing without a runtime installed. It never calls the network.
        'null' => [
            'driver' => 'null',
            'model' => 'null-model',
            'embedding_model' => 'null-embedding',
            // Kept in sync with the default Ollama embedding model so switching
            // between null and ollama does not change vector dimensions.
            'embedding_dimensions' => (int) env('AI_EMBEDDING_DIMENSIONS', 768),
            'timeout' => 5,
        ],

        // -------------------------------------------------------------------
        // OpenAI (OPTIONAL, legacy). Never required.
        // -------------------------------------------------------------------
        'openai' => [
            'driver' => 'openai',
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
            'embedding_dimensions' => (int) env('OPENAI_EMBEDDING_DIMENSIONS', 1536),
            'timeout' => (int) env('AI_TIMEOUT', 30),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Hardware-aware Local Model Selection
    |--------------------------------------------------------------------------
    |
    | The application never assumes a GPU. When OLLAMA_MODEL is "auto" the
    | ModelSelector probes RAM/CPU and picks a model from the ordered list below
    | that (a) is actually installed in Ollama and (b) fits the detected budget.
    |
    | Each entry: name => minimum RAM in GB recommended to run it comfortably.
    |
    */

    'hardware' => [
        'auto_select' => (bool) env('AI_AUTO_SELECT_MODEL', true),
        'llm_candidates' => [
            'llama3.2:1b' => 4,
            'qwen2.5:1.5b' => 4,
            'llama3.2:3b' => 8,
            'qwen2.5:3b' => 8,
            'phi3:mini' => 8,
            'mistral:7b' => 16,
            'llama3.1:8b' => 16,
            'qwen2.5:7b' => 16,
        ],
        'embedding_candidates' => [
            'nomic-embed-text' => 4,
            'mxbai-embed-large' => 8,
            'all-minilm' => 4,
            'snowflake-arctic-embed' => 8,
        ],
        // Dimensions reported per known embedding model (used for the Qdrant
        // collection schema and the local mirror). Unknown models fall back to
        // the configured default below.
        'embedding_dimensions' => [
            'nomic-embed-text' => 768,
            'mxbai-embed-large' => 1024,
            'all-minilm' => 384,
            'snowflake-arctic-embed' => 1024,
            'snowflake-arctic-embed:137m' => 384,
            'snowflake-arctic-embed:335m' => 768,
            'default' => 768,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vector Store
    |--------------------------------------------------------------------------
    |
    | Semantic retrieval is provider-agnostic. The default store is Qdrant, a
    | free, self-hosted vector database. A "database" store keeps embeddings in a
    | normal MySQL table so the app still runs with zero extra infrastructure and
    | acts as an offline fallback for the Qdrant adapter.
    |
    | If vector infrastructure is unreachable the AI layer degrades gracefully and
    | MySQL features keep working untouched.
    |
    */

    'vector' => [
        'driver' => env('AI_VECTOR_DRIVER', 'qdrant'),

        'stores' => [
            'qdrant' => [
                'driver' => 'qdrant',
                'url' => env('QDRANT_URL', 'http://127.0.0.1:6333'),
                'api_key' => env('QDRANT_API_KEY'),
                'collection' => env('QDRANT_COLLECTION', 'life_management'),
                'timeout' => (int) env('QDRANT_TIMEOUT', 5),
                'distance' => env('QDRANT_DISTANCE', 'Cosine'),
                // Deletes/reads always flow through the MySQL mirror too so the
                // app can rebuild Qdrant from MySQL at any time.
                'mirror_to_database' => (bool) env('QDRANT_MIRROR_DATABASE', true),
            ],

            'database' => [
                'driver' => 'database',
                'table' => 'ai_embeddings',
            ],

            'milvus' => [
                'driver' => 'milvus',
                'host' => env('MILVUS_HOST', '127.0.0.1'),
                'port' => (int) env('MILVUS_PORT', 19530),
                'collection' => env('MILVUS_COLLECTION', 'life_ai_embeddings'),
                'timeout' => (int) env('MILVUS_TIMEOUT', 5),
                'dimensions' => (int) env('AI_EMBEDDING_DIMENSIONS', 768),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Retrieval Tuning
    |--------------------------------------------------------------------------
    |
    | "top_k" and "min_score" cap semantic retrieval so the LLM never receives an
    | unbounded slice of the user's data. All numeric caps are byte/row budgets,
    | never "the whole database".
    |
    */

    'retrieval' => [
        'top_k' => (int) env('AI_TOP_K', env('AI_RETRIEVAL_TOP_K', 8)),
        'min_score' => (float) env('AI_RETRIEVAL_MIN_SCORE', 0.15),
        'max_context_items' => (int) env('AI_MAX_CONTEXT_ITEMS', 20),
        'max_context_chars' => (int) env('AI_MAX_CONTEXT_CHARS', 12000),
        'structured_row_limit' => (int) env('AI_STRUCTURED_ROW_LIMIT', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Usage Controls
    |--------------------------------------------------------------------------
    |
    | Hard limits protecting the provider from accidental abuse: message length,
    | response length and per-minute request throttling.
    |
    */

    'limits' => [
        'max_question_chars' => (int) env('AI_MAX_QUESTION_CHARS', 2000),
        'max_response_chars' => (int) env('AI_MAX_RESPONSE_CHARS', 8000),
        'rate_limit_per_minute' => (int) env('AI_RATE_LIMIT_PER_MINUTE', 20),
        'history_messages' => (int) env('AI_HISTORY_MESSAGES', 6),
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding Sync
    |--------------------------------------------------------------------------
    |
    | When true, model events queue embedding refresh jobs. When a queue worker
    | is not running the jobs stay queued (safe, non-blocking). If synchronous
    | sync is enabled the refresh happens inline as a fallback.
    |
    */

    'sync' => [
        'queue' => (bool) env('AI_SYNC_QUEUE', true),
        'synchronous_fallback' => (bool) env('AI_SYNC_SYNCHRONOUS', false),
        'queue_name' => env('AI_QUEUE_NAME', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Privacy
    |--------------------------------------------------------------------------
    |
    | Controls what may leave the server towards the provider. Secrets and
    | authentication data are never included in any context.
    |
    */

    'privacy' => [
        'redact_email' => (bool) env('AI_REDACT_EMAIL', true),
        'max_records_per_source' => (int) env('AI_MAX_RECORDS_PER_SOURCE', 25),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fine-tuning Preparation
    |--------------------------------------------------------------------------
    |
    | Collected training candidates are stored disabled by default and are NEVER
    | shipped to a provider automatically. This is scaffolding for a future,
    | explicitly opt-in fine-tuning phase.
    |
    */

    'fine_tuning' => [
        'collect_samples' => (bool) env('AI_COLLECT_TRAINING_SAMPLES', false),
    ],

];
