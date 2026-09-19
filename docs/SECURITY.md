# Security

This document describes the security mechanisms that are **actually implemented**.
Where something is only a recommendation, it is labelled as such.

---

## 1. Authentication

- Standard Laravel **session guard** (`Auth::attempt`) — no Jetstream/Breeze/Fortify.
- `LoginController@store`:
    - validates `email` + `password`,
    - calls `Auth::attempt($credentials, $remember)`,
    - regenerates the session on success,
    - throws `ValidationException` with `trans('auth.failed')` on failure (no user
      enumeration beyond the standard message).
- `LoginController@destroy` logs out, invalidates the session and regenerates the
  CSRF token.
- `RegisterController@store` validates name/email/password
  (`Password::defaults()`, `confirmed`, `unique:users`), hashes the password via the
  model's `hashed` cast, then logs the user in.

---

## 2. Authorization

Two layers guarantee per-user isolation:

### a) Relationship-scoped queries

Controllers read/write through `$request->user()->relation()` (e.g.
`$request->user()->expenses()`), so the `user_id` filter is implicit.

### b) Scoped route bindings

Defined in `AppServiceProvider::boot()`. A parameter resolves only to a row owned by
current user, so a cross-user id produces **404 before the controller runs**:
`savingsGoal`, `transaction`, `activity`, `occurrence`, `dailyActivity`,
`progressUpdate`, `conversation`.

### c) Policies

`Gate::policy(...)` maps models to policies in `app/Policies` (12 files). Each checks
`$user->id === $model->user_id` for `view`/`update`/`delete` (and module-specific
abilities such as `GoalPolicy@updateProgress`). Controllers call `$this->authorize(...)`
where a policy applies.

---

## 3. Middleware

- `auth` / `guest` guard the route groups (`routes/web.php`).
- `ApplyUserPreferences` (`app/Http/Middleware/ApplyUserPreferences.php`) sets the
  authenticated user's timezone for the request so dates render in their locale.

---

## 4. CSRF

Laravel's `ValidateCsrfToken` middleware is active for the web group. All forms use
`@csrf`; the AI chat `fetch()` calls send the token via the `X-CSRF-TOKEN` header.

---

## 5. Validation

- **Form Requests** (`app/Http/Requests`, 18 files) validate the settings/profile/
  password flows and the personal modules (Daily Activities, Goals, Habits, Events,
  Routine, Savings, Currency).
- **Finance CRUD controllers** validate inline with `$request->validate([...])`.
- Validation failures redirect back with `$errors` (no data is written).

---

## 6. Mass assignment protection

Every model defines an explicit `$fillable` list. Controllers pass only validated
data to `create()`/`update()`; `user_id` is taken from the authenticated user, never
from request input.

---

## 7. User data isolation (summary)

| Vector           | Enforcement                                             |
| ---------------- | ------------------------------------------------------- |
| Reads            | Queries go through the user relationship                |
| Route params     | Scoped bindings → 404 on foreign ids                    |
| Policies         | Ownership checks on view/update/delete                  |
| AI vectors       | Every row carries `user_id`; every search filters by it |
| AI conversations | Scoped binding + `AiConversationPolicy`                 |

---

## 8. File uploads

- **Profile photo**: `SettingsController@updateProfile` stores the file via
  `$request->file('profile_photo')->store('avatars', 'public')` and validates it via
  `ProfileUpdateRequest`. The previous photo is deleted when replaced/removed.
- **Logo/favicon** paths are stored as strings in `settings` and rendered through
  `Storage::disk('public')`.

Validation rules for uploads are defined in the relevant Form Request.

---

## 9. AI data access

The AI layer respects the same boundaries:

- The **LLM never receives database access.** Laravel runs all queries.
- Every AI retrieval is scoped to the authenticated user:
    - vector search passes `user_id` and filters on it,
    - `QdrantVectorSearch` re-checks the returned payload's `user_id`,
    - conversation retrieval is scoped by the route binding + policy.
- Financial figures are computed by SQL/Laravel, never by the model.
- Untrusted text is sanitised (`AIPrivacyService::sanitizeUntrusted`) and fenced
  (`<<DATA … DATA>>>`) before reaching the model, with a system prompt that treats it
  strictly as data (prompt-injection defence).

---

## 10. Environment secrets

- **No API keys are required** and none are stored in the database.
- `ai_settings` deliberately excludes credentials (documented in its migration).
- Secrets live only in `.env`; `.env.example` documents variable **names** with no
  real values.
- Model/URL names shown in Settings → AI Status are non-secret and safe to display.

---

## 11. Recommendations (not yet implemented)

These are **not** present in the code today:

- Email verification (`MustVerifyEmail` is commented out).
- Two-factor authentication.
- Global rate limiting beyond the AI endpoint's per-user throttle.
- Content Security Policy headers.
- Storage of AI request/response bodies (only a compact retrieval trace is stored).
