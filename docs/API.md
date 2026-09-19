# API / HTTP Surface

The application is a **session-based server-rendered app**, not a JSON REST API.
There is no `/api` route file and no API tokens. The only JSON endpoints are the
internal ones used by the AI Assistant chat UI (they are session-authenticated and
CSRF-protected).

Routes are defined in `routes/web.php`. All routes except `/`, `/login`, `/register`
require the `auth` middleware.

---

## 1. AI Assistant (JSON endpoints)

These are called by `resources/views/ai/assistant.blade.php` via `fetch()`.

| Method   | URI                                | Name                       | Controller                      | Purpose                      |
| -------- | ---------------------------------- | -------------------------- | ------------------------------- | ---------------------------- |
| `GET`    | `/ai/assistant`                    | `ai.assistant`             | `AIAssistantController@index`   | Render the assistant page    |
| `POST`   | `/ai/assistant/ask`                | `ai.assistant.ask`         | `AIAssistantController@ask`     | Ask a question; returns JSON |
| `DELETE` | `/ai/conversations/{conversation}` | `ai.conversations.destroy` | `AIAssistantController@destroy` | Delete an owned conversation |

### `POST /ai/assistant/ask`

**Request body** (JSON):

```json
{
    "question": "How much did I spend this month?",
    "conversation_id": 12
}
```

`question` is required (max `AI_MAX_QUESTION_CHARS`, default 2000).
`conversation_id` is optional; if supplied it must belong to the current user.

**Success `200`:**

```json
{
    "ok": true,
    "answer": "…grounded answer…",
    "conversation_id": 12,
    "intent": "finance",
    "sources": [
        { "source_type": "expense_record", "source_id": 3, "score": 0.42 }
    ],
    "degraded": false
}
```

**Failure `503`** (AI disabled or provider/vector unavailable):

```json
{
    "ok": false,
    "error": "Local AI is currently unavailable. Please start Ollama and try again.",
    "reason": "network"
}
```

**Failure `429`** (per-user rate limit exceeded):

```json
{
    "ok": false,
    "error": "You have reached the AI usage limit. Please wait a moment and try again."
}
```

`DELETE /ai/conversations/{conversation}` returns `{ "ok": true }`.

The `{conversation}` parameter is bound in `AppServiceProvider` to the current user,
so another user's conversation id returns **404**.

---

## 2. Authentication

| Method | URI         | Name                             |
| ------ | ----------- | -------------------------------- |
| `GET`  | `/`         | — (redirects to dashboard/login) |
| `GET`  | `/register` | `register`                       |
| `POST` | `/register` | —                                |
| `GET`  | `/login`    | `login`                          |
| `POST` | `/login`    | —                                |
| `POST` | `/logout`   | `logout`                         |

---

## 3. Dashboard & Money Management

| Method | URI                 | Name                     |
| ------ | ------------------- | ------------------------ |
| `GET`  | `/dashboard`        | `dashboard`              |
| `GET`  | `/money-management` | `money-management.index` |

### Income (resource, except `show`)

`GET /income`, `GET /income/create`, `POST /income`, `GET /income/{income}/edit`,
`PUT/PATCH /income/{income}`, `DELETE /income/{income}` — names `income.*`.

### Expenses (resource, except `show`)

Same shape under `/expenses` — names `expenses.*`.

### Salary (resource, except `show`)

Same shape under `/salary` — names `salary.*`.

### Savings

| Method   | URI                                        | Name                           |
| -------- | ------------------------------------------ | ------------------------------ |
| `GET`    | `/savings`                                 | `savings.index`                |
| `GET`    | `/savings/goals/create`                    | `savings.goals.create`         |
| `POST`   | `/savings/goals`                           | `savings.goals.store`          |
| `GET`    | `/savings/goals/{savingsGoal}`             | `savings.goals.show`           |
| `GET`    | `/savings/goals/{savingsGoal}/edit`        | `savings.goals.edit`           |
| `PUT`    | `/savings/goals/{savingsGoal}`             | `savings.goals.update`         |
| `DELETE` | `/savings/goals/{savingsGoal}`             | `savings.goals.destroy`        |
| `POST`   | `/savings/transactions`                    | `savings.transactions.store`   |
| `GET`    | `/savings/transactions/{transaction}/edit` | `savings.transactions.edit`    |
| `PUT`    | `/savings/transactions/{transaction}`      | `savings.transactions.update`  |
| `DELETE` | `/savings/transactions/{transaction}`      | `savings.transactions.destroy` |

### Recurring Transactions (resource, except `show`)

Under `/recurring-transactions` — names `recurring-transactions.*`.

---

## 4. Daily Management

### Daily Activities (resource, param `dailyActivity`)

Under `/daily-management/activities` — names `daily-activities.*`.

### Habits (resource) + Habit Activities

| Method       | URI                                                    | Name                       |
| ------------ | ------------------------------------------------------ | -------------------------- |
| `GET/POST/…` | `/daily-management/habits…`                            | `habits.*`                 |
| `PATCH`      | `/daily-management/habits/{habit}/toggle`              | `habits.toggle`            |
| `POST`       | `/daily-management/habits/{habit}/activities`          | `habit-activities.store`   |
| `PATCH`      | `/daily-management/habit-activities/{activity}/toggle` | `habit-activities.toggle`  |
| `PUT`        | `/daily-management/habit-activities/{activity}`        | `habit-activities.update`  |
| `DELETE`     | `/daily-management/habit-activities/{activity}`        | `habit-activities.destroy` |

### Daily Routine (resource, param `routine`) + Occurrences

| Method       | URI                                                  | Name                         |
| ------------ | ---------------------------------------------------- | ---------------------------- |
| `GET/POST/…` | `/daily-management/routine…`                         | `routine.*`                  |
| `PATCH`      | `/daily-management/routine/{routine}/mark`           | `routine.mark`               |
| `GET`        | `/daily-management/routine/{routine}/occurrences`    | `routine.occurrences`        |
| `PATCH`      | `/daily-management/routine-occurrences/{occurrence}` | `routine-occurrences.update` |

---

## 5. Personal Growth

| Method       | URI                                               | Name                    |
| ------------ | ------------------------------------------------- | ----------------------- |
| `GET/POST/…` | `/personal-growth/goals…`                         | `goals.*`               |
| `PATCH`      | `/personal-growth/goals/{goal}/progress`          | `goals.progress`        |
| `POST`       | `/personal-growth/goals/{goal}/progress-updates`  | `goal-progress.store`   |
| `PUT`        | `/personal-growth/goal-progress/{progressUpdate}` | `goal-progress.update`  |
| `DELETE`     | `/personal-growth/goal-progress/{progressUpdate}` | `goal-progress.destroy` |
| `GET`        | `/personal-growth/progress`                       | `progress.index`        |

---

## 6. Important Dates

| Method       | URI                         | Name             |
| ------------ | --------------------------- | ---------------- |
| `GET`        | `/important-dates/calendar` | `calendar.index` |
| `GET/POST/…` | `/important-dates/events…`  | `events.*`       |

---

## 7. Settings

| Method                      | URI                                                                 | Name                                             |
| --------------------------- | ------------------------------------------------------------------- | ------------------------------------------------ |
| `GET`                       | `/settings`                                                         | `settings.index`                                 |
| `GET`/`PUT`                 | `/settings/general`                                                 | `settings.general` / `settings.general.update`   |
| `GET`/`PUT`                 | `/settings/currency`                                                | `settings.currency` / `settings.currency.update` |
| `GET/POST/PUT/PATCH/DELETE` | `/settings/currencies…`                                             | `settings.currencies.*`                          |
| `GET`                       | `/settings/salary`, `/savings`, `/income`, `/expense`, `/recurring` | `settings.salary`, …                             |
| `PUT`                       | `/settings/finance`                                                 | `settings.finance.update`                        |
| `GET`/`PUT`                 | `/settings/notifications`                                           | `settings.notifications` / `.update`             |
| `GET`/`PUT`                 | `/settings/appearance`                                              | `settings.appearance` / `.update`                |
| `GET`/`PUT`                 | `/settings/profile`                                                 | `settings.profile` / `.update`                   |
| `GET`                       | `/settings/security`                                                | `settings.security`                              |
| `PUT`                       | `/settings/security/password`                                       | `settings.password.update`                       |
| `GET`                       | `/settings/ai`                                                      | `settings.ai`                                    |

---

## 8. Framework routes (not app features)

`GET /up` (health), `GET|PUT /storage/{path}` (local disk serving) — provided by the
Laravel framework, not written for this app.
