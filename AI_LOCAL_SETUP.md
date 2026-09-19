# Local AI Setup (100% Free, Privacy-First)

This project ships a **fully local** AI assistant with Retrieval-Augmented
Generation (RAG) and semantic search for the Personal Workspace.

> **No paid AI API is required.** There is no OpenAI / Anthropic / Gemini /
> Azure / OpenRouter key anywhere. The assistant runs entirely on your own
> computer using **Ollama** (local LLM + local embeddings) and a self-hosted
> **Qdrant** vector database. Your data never leaves your machine.

---

## Why local AI?

* **Privacy first** — your daily activities, goals, habits, events and finance
  notes are embedded and answered on-device.
* **Zero API cost** — no subscriptions, no per-token billing.
* **Works offline** — once the models are downloaded you can run without
  internet (other than the app itself).

The trade-off is that local AI consumes **your own resources**:

* **RAM** — a 3B model needs ~4–8 GB free; a 7B model ~8–16 GB.
* **CPU / GPU** — CPU-only works but is slower; a GPU speeds this up a lot.
* **Disk** — each model is 1–8 GB; embeddings add a few hundred MB.
* **Electricity** — model inference uses real power.

The application auto-detects your hardware and picks a model that fits. You can
always override it.

---

## Architecture

```
Laravel Application
        |
        v
   AI Assistant  (chat UI, /ai/assistant)
        |
        v
  Intent Analysis   (deterministic, PHP — NOT the LLM)
        |
        +----------------------------+
        |                            |
        v                            v
  Structured SQL retrieval     Semantic search
  (exact facts, money sums)    (Qdrant vector DB)
        |                            |
        +-----------+----------------+
                    |
                    v
              Context Builder   (grounded, fenced, budgeted)
                    |
                    v
              Local Ollama LLM
                    |
                    v
              Grounded Answer
```

* **MySQL/MariaDB is the source of truth.** The vector index is only a
  retrieval index; it can be rebuilt from MySQL at any time
  (`php artisan ai:reindex`). If vector data ever conflicts with MySQL,
  **MySQL wins.**
* **The LLM never queries the database** and never calculates money. All
  financial figures come from exact SQL aggregates in Laravel.
* **Every vector record carries `user_id / source_type / source_id`** and every
  semantic search is filtered by the authenticated user, so one user can never
  retrieve another user's data.

---

## 1. Install Ollama

1. Download Ollama for your OS from <https://ollama.com/download>
2. Install and start it. On Windows/macOS it runs as a background service.
3. Verify it is running:

```bash
ollama --version
```

By default Ollama listens on `http://127.0.0.1:11434` (matches the app default).

## 2. Pull a local LLM

Choose a model that fits your hardware (the app will auto-select among the ones
you actually install):

```bash
# Small / low-RAM (≈4–8 GB RAM) — good default on most laptops
ollama pull llama3.2:3b

# Very low RAM (≈4 GB)
ollama pull llama3.2:1b

# More capable (≈16 GB RAM)
ollama pull llama3.1:8b
ollama pull qwen2.5:7b
```

## 3. Pull a local embedding model

```bash
# Recommended: 768-dim local embeddings
ollama pull nomic-embed-text

# Alternatives
ollama pull mxbai-embed-large     # 1024-dim
ollama pull all-minilm            # 384-dim, very light
```

## 4. Run Qdrant locally

Qdrant is free and self-hosted. The simplest way is Docker:

```bash
docker run -d --name qdrant -p 6333:6333 -p 6334:6334 \
  -v qdrant_storage:/qdrant/storage qdrant/qdrant
```

No paid account is required. Verify it is reachable:

```bash
curl http://127.0.0.1:6333/healthz
```

> Don't have Docker? The app also ships a zero-infrastructure
> **MySQL-backed vector store** as a fallback. Set
> `AI_VECTOR_DRIVER=database` in `.env` to run with no extra server at all.

## 5. Configure `.env`

Add (or adjust) these values in your `.env`:

```env
AI_ENABLED=true
AI_PROVIDER=ollama

# Local LLM runtime
OLLAMA_BASE_URL=http://127.0.0.1:11434
# "auto" = pick a sensible installed model for the detected hardware.
OLLAMA_MODEL=auto
OLLAMA_EMBEDDING_MODEL=auto
AI_TIMEOUT=120

# Vector database (self-hosted Qdrant)
AI_VECTOR_DRIVER=qdrant
QDRANT_URL=http://127.0.0.1:6333
QDRANT_COLLECTION=life_management

# Retrieval / usage limits
AI_TOP_K=8
AI_MAX_CONTEXT_ITEMS=20
```

You can pin an exact model instead of `auto`:

```env
OLLAMA_MODEL=llama3.2:3b
OLLAMA_EMBEDDING_MODEL=nomic-embed-text
```

## 6. Run migrations

The AI tables (`ai_conversations`, `ai_messages`, `ai_settings`,
`ai_embeddings`) are created by the standard migrations — never run
`migrate:fresh`:

```bash
php artisan migrate
```

## 7. Build the semantic index

```bash
# Every user
php artisan ai:reindex

# A single user
php artisan ai:reindex --user=1
```

This reads authoritative MySQL data, generates embeddings locally and stores
the vectors. It is safe to re-run at any time.

## 8. Verify AI health

```bash
php artisan ai:health
php artisan ai:health --user=1
```

This reports: AI enabled, Ollama reachable, LLM model available, embedding
model available, vector DB reachable, vector record count and the detected
hardware.

## 9. Test the AI Assistant

1. Log in to the Personal Workspace.
2. Open **AI Assistant** in the sidebar (`/ai/assistant`).
3. Try:
   * “What did I do today?”
   * “What goals am I currently working on?”
   * “Which habits have I been consistent with?”
   * “How much did I spend this month?”
   * “Summarize my week.”
   * “Find activities related to learning programming.”

You can also see the live status under **Settings → AI Status**.

---

## Privacy & safety guarantees

* **User isolation** — every vector carries `user_id`; every search is filtered
  by it. There is no global semantic search over private records.
* **Prompt-injection defence** — retrieved text is wrapped in `<<DATA … DATA>>>`
  fences and the system prompt tells the model to treat it strictly as data, so
  a note saying “ignore all previous instructions” is treated as text, not a
  command.
* **No LLM arithmetic** — money totals, counts and balances are computed by
  SQL/Laravel, then explained by the model.
* **Currency safety** — the master currency is your **Settings → Currency**
  setting. Amounts in different currencies are never summed, and no exchange
  rate is ever invented.
* **Graceful degradation** — if Ollama or Qdrant is offline, the rest of the
  app keeps working; AI screens show a friendly “Local AI is currently
  unavailable” notice instead of crashing.

---

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| `Settings → AI Status` shows **Unavailable** | Start Ollama (`ollama serve`) and Qdrant. |
| “model is not installed” | `ollama pull <model>` or set `OLLAMA_MODEL` to an installed one (`ollama list`). |
| Semantic search empty | Run `php artisan ai:reindex --user=YOUR_ID`. |
| Vector DB **Offline** | Start Qdrant, or set `AI_VECTOR_DRIVER=database`. |
| Slow answers | Use a smaller model (e.g. `llama3.2:1b`) or enable a GPU. |
| Assistant disabled | Set `AI_ENABLED=true` in `.env` and reload. |

---

## Switching / removing the AI layer

The AI layer is fully optional and abstracted:

* `AIProviderInterface` → `OllamaAIProvider` (local LLM).
* `EmbeddingServiceInterface` → `OllamaEmbeddingProvider` (local embeddings).
* `VectorSearchInterface` → `QdrantVectorSearch` (with MySQL fallback).

To disable the AI entirely, set `AI_ENABLED=false`. Every non-AI feature keeps
working exactly as before, and the model observers perform no work.
