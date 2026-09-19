# Dashboard

## 1. Purpose

A single overview page summarizing the user's money, activities, habits, routine,
goals and events, plus a small AI widget.

## 2. Current Status

Implemented.

## 3. User Flow

Log in → the Dashboard is the landing page (`/dashboard` / `route('dashboard')`).
It aggregates read-only summaries; each card links to its module.

## 4. UI

- **Route**: `GET /dashboard` → `dashboard`.
- **View**: `resources/views/dashboard.blade.php`.
- **Components**: summary cards (`.summary-card`), a Chart.js bar chart (monthly
  income vs expense, last 6 months), recent-activity tables, and an **Ask AI** panel.
- The dashboard layout can be reduced via `settings.dashboard_layout`.

## 5. Backend

- **Controller**: `App\Http\Controllers\DashboardController@index`.
- It computes, in one action, everything the view needs: money summary, savings
  progress, a 6-month income/expense series, recent income/expenses/recurring,
  today's and recent activities, habit stats, routine stats, goal stats, event stats,
  plus settings (currency, dashboard layout).
- **Services**: none. **Requests**: none. **Models**: reads through the user's
  relationships (`incomeRecords`, `expenseRecords`, `dailyActivities`, `habits`,
  `goals`, `events`, `routines`, `routineOccurrences`, …).

## 6. Database

Reads across all module tables (no writes). See [DATABASE.md](../DATABASE.md).

## 7. Business Rules

- All figures are computed from the authenticated user's own rows.
- Derived values (e.g. `$availableBalance = income − expenses − savings`) are computed
  in PHP from SQL sums — never by the AI.
- Currency formatting uses `CurrencyConfig::format($userId, $amount)`.

## 8. Validation

None (read-only page).

## 9. Permissions & Security

Requires `auth`; every query is user-scoped.

## 10. Data Flow

`GET /dashboard → DashboardController@index → user relationships → MySQL → dashboard
blade`.

## 11. File Map

| Concern    | Files                                                       |
| ---------- | ----------------------------------------------------------- |
| Controller | `app/Http/Controllers/DashboardController.php`              |
| View       | `resources/views/dashboard.blade.php`                       |
| Helpers    | `app/Support/CurrencyConfig.php`, `app/Support/helpers.php` |

## 12. AI Integration

A compact **Ask AI** widget renders (only when `config('ai.enabled')` is true) with a
link to the AI Assistant and a "Weekly AI Summary" shortcut. It is wrapped in a
`try/catch`, so an AI failure can never break the dashboard. See
[AI_ASSISTANT.md](./AI_ASSISTANT.md).

## 13. Known Limitations

- The dashboard is a heavy single action (many queries); it is not currently cached.
- The AI widget shows status/links only — it does not inline-generate answers.

## 14. Future Improvements (planned)

- Optional caching of dashboard aggregates.
- Inline AI summary card driven by the weekly-summary intent.

## 15. Change History

- Initial audit and documentation (2026-09-20).
