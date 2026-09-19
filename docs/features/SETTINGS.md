# Settings

## 1. Purpose

Per-user configuration for the whole workspace: general, currency, finance sections,
notifications, appearance, profile, security, and AI status.

## 2. Current Status

Implemented (all sections).

## 3. User Flow

Sidebar → Settings (or the pinned footer link). A shared tab nav
(`settings/partials/nav.blade.php`) switches sections; each section is a form that
saves to the user's single `settings` row.

## 4. UI

- **Views**: `resources/views/settings/*.blade.php` — `index`, `general`, `currency`,
  `currencies` (CRUD), `salary`, `savings`, `income`, `expense`, `recurring`,
  `notifications`, `appearance`, `profile`, `security`, `ai`.
- **Shared nav**: `resources/views/settings/partials/nav.blade.php`.
- **Routes** (`settings.*`): see [API.md](../API.md#7-settings).

## 5. Backend

- **Controllers**: `App\Http\Controllers\SettingsController` (all sections +
  `ai()` status method), `App\Http\Controllers\CurrencyController` (currency CRUD).
- **Requests**: `GeneralSettingsRequest`, `CurrencySettingsRequest`,
  `FinanceSettingsRequest`, `NotificationSettingsRequest`, `AppearanceSettingsRequest`,
  `ProfileUpdateRequest`, `PasswordUpdateRequest`, `CurrencyRequest`.
- **Model**: `App\Models\Setting` (`Setting::forUser($id)` lazily creates the row) and
  `App\Models\Currency`.
- **Support**: `App\Support\CurrencyConfig`, `App\Support\UserPreference`.

## 6. Database

`settings` — one row per user holding every section (general, currency, salary,
savings, income, expense, recurring, notifications, appearance).
`currencies` — per-user currency registry (unique `(user_id, code)`). See
[DATABASE.md](../DATABASE.md).

## 7. Business Rules

- **Currency is the master setting.** `settings.currency_code`/`symbol` is the
  workspace currency; it is surfaced to the AI and used by `CurrencyConfig::format()`.
  There is **no** separate currency setting under Money Management.
- Appearance (`theme`, `primary_color`, `compact_mode`) is applied by the layout.
- Notification preferences are **saved only** — nothing is dispatched yet.

## 8. Validation

Each Form Request defines its rules (e.g. currency code/symbol formats, timezone,
password confirmation in `PasswordUpdateRequest`).

## 9. Permissions & Security

Requires `auth`. The controller always resolves the row from `Auth::id()` — a user id
is never read from the request. `CurrencyPolicy` guards currency rows.

## 10. Data Flow

`Route (auth) → SettingsController → Form Request → Setting/Currency → MySQL →
redirect with session('status')`.

## 11. File Map

| Concern     | Files                                                                                                                    |
| ----------- | ------------------------------------------------------------------------------------------------------------------------ |
| Controllers | `app/Http/Controllers/SettingsController.php`, `CurrencyController.php`                                                  |
| Requests    | `app/Http/Requests/*SettingsRequest.php`, `ProfileUpdateRequest.php`, `PasswordUpdateRequest.php`, `CurrencyRequest.php` |
| Models      | `app/Models/Setting.php`, `Currency.php`                                                                                 |
| Views       | `resources/views/settings/*`                                                                                             |
| Migrations  | `2026_09_17_100005_create_settings_table.php`, `2026_09_17_100007_create_currencies_table.php`                           |

## 12. AI Integration

- **Settings → AI Status** (`SettingsController@ai`, view `settings/ai.blade.php`)
  shows the local AI health report: status, Ollama reachability, LLM/embedding models,
  vector database + record count, last reindex, installed models, detected hardware and
  a setup checklist. **No secrets are shown.**
- The currency configured here is passed to the AI so answers respect the user's unit.

## 13. Known Limitations

- Notification preferences do not yet trigger any delivery.
- Currency exchange rates are not modelled.

## 14. Future Improvements (planned)

- Dispatch the saved notification preferences.

## 15. Change History

- Initial audit and documentation (2026-09-20).
- Added the AI Status section.
