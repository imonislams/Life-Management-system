# Development Guide

How to set up, run and extend the Life Management System.

---

## 1. Requirements

| Requirement     | Version                                                                  |
| --------------- | ------------------------------------------------------------------------ |
| PHP             | 8.2+                                                                     |
| Composer        | 2.x                                                                      |
| Node.js + npm   | for Vite asset build                                                     |
| MySQL / MariaDB | source of truth                                                          |
| Ollama          | only for the AI Assistant (optional)                                     |
| Qdrant          | only if using the Qdrant vector driver (optional; MySQL fallback exists) |

---

## 2. Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Create the environment file
copy .env.example .env      # Windows
# cp .env.example .env      # Unix

# 3. Generate the app key
php artisan key:generate

# 4. Configure the database in .env (see below), then migrate
php artisan migrate

# 5. Install & build front-end assets
npm install
npm run build
```

There is a combined Composer script: `composer setup` (install + key + migrate +
npm install + build).

---

## 3. Environment setup

Key variables in `.env` (defaults shown in `.env.example`):

```env
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=life_management_system
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

`QUEUE_CONNECTION=database` matters for the AI embedding sync: run
`php artisan queue:work` if you want background indexing (otherwise jobs wait; CRUD
is never blocked).

---

## 4. Database setup

```bash
php artisan migrate          # apply migrations
php artisan migrate:status   # inspect state
```

> ⚠️ **Never** run `migrate:fresh`, `migrate:refresh` or `db:wipe`. This project holds
> real personal data and its rules forbid destructive database operations.

Seeding: the standard `DatabaseSeeder` runs `UserFactory` users. Run with
`php artisan db:seed` only if you intend to create sample data.

---

## 5. Frontend setup

```bash
npm install
npm run dev      # vite dev server (with hot reload)
npm run build    # production build
```

Assets are wired via `@vite(['resources/css/app.css', 'resources/js/app.js'])` in the
layout. Styling uses the custom design system in `resources/css/app.css` (no Tailwind
utility classes in views).

---

## 6. Development server

```bash
php artisan serve
# optional bundled runner:
composer dev     # serve + queue:listen + pail + vite concurrently
```

---

## 7. Local AI setup

The AI layer is optional. Full instructions: **[AI_LOCAL_SETUP.md](../AI_LOCAL_SETUP.md)**.
Quick version:

```bash
# 1. Ollama (LLM + embeddings)
ollama pull llama3.2:3b
ollama pull nomic-embed-text

# 2. Qdrant (optional; otherwise set AI_VECTOR_DRIVER=database)
docker run -d --name qdrant -p 6333:6333 qdrant/qdrant

# 3. Enable in .env
#   AI_ENABLED=true
#   OLLAMA_MODEL=auto
#   OLLAMA_EMBEDDING_MODEL=auto (or nomic-embed-text)

# 4. Build the index and check health
php artisan ai:reindex
php artisan ai:health
```

---

## 8. Common commands

| Command                                                   | Purpose                                 |
| --------------------------------------------------------- | --------------------------------------- |
| `php artisan serve`                                       | Start the dev server                    |
| `npm run dev` / `npm run build`                           | Vite dev / production assets            |
| `php artisan migrate`                                     | Apply migrations                        |
| `php artisan config:clear` / `route:clear` / `view:clear` | Clear caches                            |
| `php artisan queue:work`                                  | Process queued jobs (AI embedding sync) |
| `php artisan ai:health`                                   | Local AI health report                  |
| `php artisan ai:reindex`                                  | Rebuild the semantic index              |
| `php artisan test`                                        | Run the test suite                      |

---

## 9. Documentation workflow

**A code change and its documentation change happen together.**

1. Read the feature doc for the module you are touching
   (`docs/features/<FEATURE>.md`).
2. Update the relevant core doc if the change affects architecture, the database, the
   UI or the backend (`docs/ARCHITECTURE.md`, `docs/DATABASE.md`, `docs/UI.md`,
   `docs/BACKEND.md`, `docs/AI.md`).
3. Update `docs/FEATURES.md` if a feature's status changed.
4. Add an entry to `docs/CHANGELOG.md` for meaningful changes.
5. Keep documentation **accurate** — never document a class/route/table that does not
   exist.

---

## 10. Feature development contract

```
Requirement
  → Feature documentation
  → Database design
  → Backend design
  → UI design
  → Implementation
  → Documentation update
```

When extending: follow the module's existing pattern (thin controller + model +
`App\Support` helper; Form Request when the module uses one). Only introduce a
service class when the logic is genuinely complex/reusable (as the AI layer does).

---

## 11. Troubleshooting

| Symptom                                 | Fix                                                                |
| --------------------------------------- | ------------------------------------------------------------------ |
| Blank page / styles missing             | Run `npm run build` (or `npm run dev`)                             |
| Config changes not applied              | `php artisan config:clear`                                         |
| Vector/search errors after model change | `php artisan ai:reindex`                                           |
| AI says "unavailable"                   | Start Ollama (and Qdrant, or set `AI_VECTOR_DRIVER=database`)      |
| Embedding sync not running              | Start `php artisan queue:work` (or set `AI_SYNC_SYNCHRONOUS=true`) |
| `SQLSTATE` connection errors            | Check `.env` DB credentials                                        |
| Route/view cache stale                  | `php artisan route:clear && php artisan view:clear`                |
