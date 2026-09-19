# Changelog

Meaningful changes to the Life Management System. Only changes discovered or made
during documentation/cleanup work are recorded — no fabricated history.

Format: `## YYYY-MM-DD` with `Added` / `Changed` / `Fixed` / `Documentation` sections.

---

## 2026-09-20
### Added
- **Global alert system** — centralized toasts + confirmation modal.
  - `resources/views/components/toast.blade.php` (flash → toasts)
  - `resources/views/components/confirm-modal.blade.php` (reusable dialog)
  - `resources/js/alerts.js` (toast/modal behaviour + `window.Alerts` API)
  - Toast/modal styles in `resources/css/app.css` (design tokens, dark mode, reduced-motion)
  - Both components included globally in `resources/views/layouts/app.blade.php`.
- `docs/UI/ALERTS_AND_NOTIFICATIONS.md` documenting the architecture and usage.

### Changed
- Replaced **native `confirm()` delete prompts** with the centralized `data-confirm*`
  modal across finance, activities, habits, routine, goals, events, savings, currencies
  and the AI assistant (16 forms + 1 JS call).
- Removed the per-page inline `@if(session('status'))` alert blocks (31 views) now that
  `<x-toast />` renders flash messages globally.
- `resources/js/app.js` now imports `./alerts`.

### Fixed
- `data-confirm` is a valueless attribute; the handler used a truthiness check on
  `dataset.confirm` (empty string = falsy) so the modal never opened. Switched to
  `hasAttribute('data-confirm')`.

### Restoration (later the same day)
The global alert integration was **decoupled** to return the application to its
pre-alert behaviour:
- Removed `<x-toast />` / `<x-confirm-modal />` from `resources/views/layouts/app.blade.php`.
- Reverted `resources/js/app.js` to its original `import './bootstrap';` (no `./alerts`).
- Restored the inline `@if (session('status'))` success blocks to the module list/detail
  views from which they had been removed (finance, activities, habits, routine, goals,
  events, currencies).
- Assets rebuilt without the alerts module.
The alert feature files (`components/toast.blade.php`, `components/confirm-modal.blade.php`,
`js/alerts.js`, related CSS) are left on disk but are no longer mounted, so no app-wide
behaviour depends on them.

### Documentation
- Created the `docs/` documentation set: `README`, `ARCHITECTURE`, `DATABASE`, `UI`,
  `BACKEND`, `FEATURES`, `API`, `AI`, `SECURITY`, `DEVELOPMENT`, `CHANGELOG`.
- Created per-feature documents under `docs/features/` for every implemented module
  (Dashboard, Finance, Daily Activities, Routine, Habits, Goals, Progress, Events,
  Calendar, Settings, AI Assistant).
- Replaced the stock Laravel root `README.md` with a project-specific overview and
  links to `docs/README.md`.
- Documented the two **orphaned legacy tables** (`tasks`, `work_logs`) that still
  exist in the database but have no model or code references.

### Removed

- `app/Services/AI/QuestionClassifier.php` — a dead duplicate superseded by
  `App\Services\AI\IntentAnalyzer` (referenced only in a comment).
- `app/Services/AI/EmbeddingService.php` — an unused facade; the container binds
  `EmbeddingServiceInterface` directly.

### Changed

- `IntentAnalyzer` now expands broad "find/search" questions to search **all**
  semantic source types instead of a single matched type (better cross-cutting
  retrieval).
- `HardwareDetector` uses modern `Get-CimInstance` via the full `powershell.exe` path
  (with a `wmic` fallback), so RAM/CPU/GPU detection works on current Windows.
- `ai:reindex` no longer uses a progress bar (which wrote to stderr and looked like a
  failure under some shells).

### Fixed
- `tests/Feature/ExampleTest.php` asserted the root route returns HTTP 200, but `/`
  has always redirected (to the dashboard when authenticated, to the login screen
  otherwise). The test now asserts the real redirect behaviour, so the suite is green
  (39 passing).

### Added
- `docs/features/AI_ASSISTANT.md` etc. documenting the 100% local AI + RAG stack.

---

## Prior state (pre-documentation)

The following were already implemented before this documentation pass and are
documented as-is:

- Personal Workspace modules: Dashboard, Money Management (Income, Salary, Expenses,
  Savings, Recurring), Daily Activities, Daily Routine, Habits, Goals, Personal
  Progress, Events, Calendar, Settings.
- 100% local AI Assistant: Ollama + Qdrant + RAG, semantic indexing, conversations,
  health checks and the `ai:reindex` / `ai:health` commands.

> This section records known prior state only; it is not a full project history.
