# Life Management System

A **personal** Life Management System built with Laravel 12 and MySQL/MariaDB.
It brings money management, daily activities, habits, routine, goals, progress,
events and a **100% local AI Assistant** into one private workspace.

This is **not** an office/ERP/HR system — there is no employee, attendance, payroll
or company management.

---

## Tech stack

| Layer     | Technology                                                  |
| --------- | ----------------------------------------------------------- |
| Framework | Laravel 12 (PHP 8.2+)                                       |
| Database  | MySQL / MariaDB                                             |
| Front     | Blade + a custom CSS design system (Vite build)             |
| Auth      | Laravel session guard                                       |
| Local AI  | Ollama (LLM + embeddings) + Qdrant (vector DB) + custom RAG |

---

## Main features

- **Dashboard** — a complete overview of money, activities, habits, routine, goals and events.
- **Money Management** — analytics, income, salary, expenses, savings (goals + ledger), recurring finance, multi-currency.
- **Daily Activities** — a personal daily journal.
- **Daily Routine** — recurring routine templates with per-day occurrences.
- **Habits** — habits with optional activities and daily completions.
- **Goals** — measurable/qualitative goals with dated progress history.
- **Personal Progress** — consolidated recent progress.
- **Events & Calendar** — personal events and a calendar view.
- **Settings** — general, currency, finance sections, notifications, appearance, profile, security, AI status.
- **AI Assistant** — ask questions about your own data, answered locally.

---

## Local AI stack (100% free)

The AI Assistant runs entirely on your machine:

```
Ollama  ->  local LLM (e.g. llama3.2:3b / qwen2.5:3b)
        ->  local embeddings (e.g. nomic-embed-text)
Qdrant  ->  self-hosted vector database (or a MySQL fallback)
```

**No paid AI API and no API key are required** — no OpenAI, Anthropic, Gemini, Azure
or OpenRouter. See **[AI_LOCAL_SETUP.md](./AI_LOCAL_SETUP.md)** for setup.

---

## Installation summary

```bash
composer install
copy .env.example .env        # cp on Unix
php artisan key:generate
# configure DB_* in .env
php artisan migrate
npm install && npm run build
php artisan serve
```

> Never run `migrate:fresh`, `migrate:refresh` or `db:wipe` — this project holds real
> personal data.

---

## Development commands

| Command                         | Purpose                                 |
| ------------------------------- | --------------------------------------- |
| `php artisan serve`             | Start the dev server                    |
| `npm run dev` / `npm run build` | Vite dev / production assets            |
| `php artisan migrate`           | Apply migrations                        |
| `php artisan queue:work`        | Process queued jobs (AI embedding sync) |
| `php artisan ai:health`         | Check the local AI stack                |
| `php artisan ai:reindex`        | Rebuild the semantic index              |
| `php artisan test`              | Run the test suite                      |

---

## Documentation

Detailed documentation lives in **[docs/README.md](./docs/README.md)**:

- [Architecture](./docs/ARCHITECTURE.md)
- [Database](./docs/DATABASE.md)
- [UI](./docs/UI.md)
- [Backend](./docs/BACKEND.md)
- [Features](./docs/FEATURES.md) + [per-feature docs](./docs/features/)
- [HTTP surface](./docs/API.md)
- [AI architecture](./docs/AI.md)
- [Security](./docs/SECURITY.md)
- [Development guide](./docs/DEVELOPMENT.md)
- [Changelog](./docs/CHANGELOG.md)
- [Local AI setup](./AI_LOCAL_SETUP.md)

---

## License

This project is built on the Laravel framework, which is open-source software
licensed under the [MIT license](https://opensource.org/licenses/MIT).
