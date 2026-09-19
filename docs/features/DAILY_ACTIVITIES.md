# Daily Activities

## 1. Purpose

A personal journal of what the user actually did each day. Replaced the former
separate **Tasks** and **Work Logs** modules.

## 2. Current Status

Implemented.

## 3. User Flow

Sidebar → Daily Activities → list (filter by date/month/category/search) → create →
edit → delete. Completed/planned/etc. status and optional duration are recorded.

## 4. UI

- **Views**: `resources/views/daily-activities/{index,create,edit}.blade.php`.
- **Routes** (`daily-activities.*`, resource with parameter `dailyActivity`):
  `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
- Summary cards for today/this week/this month/all-time, plus filters.

## 5. Backend

- **Controller**: `App\Http\Controllers\DailyActivityController`.
- **Request**: `App\Http\Requests\DailyActivityRequest`.
- **Model**: `App\Models\DailyActivity` — `resolveDuration()` (derives minutes from
  start/end times, else `duration_minutes`), `durationLabel()`, `startTimeLabel()`.
- Optional link to a `Goal` via `goal_id`.

## 6. Database

Table `daily_activities` (columns, indexes: [DATABASE.md](../DATABASE.md)). Relationship:
`User → hasMany DailyActivity`; `DailyActivity → belongsTo Goal` (optional).

## 7. Business Rules

- `status` ∈ `completed | in_progress | planned | skipped`.
- Duration is derived from start/end times when both exist, otherwise the manual
  `duration_minutes` is used.

## 8. Validation

`DailyActivityRequest`: title required, `activity_date` required date, times optional,
`duration_minutes` nullable integer, `category`/`status` from allowed sets,
`goal_id` must belong to the user.

## 9. Permissions & Security

Requires `auth`. `DailyActivityPolicy` guards `view/update/delete`; the
`{dailyActivity}` route binding resolves only the user's own rows (404 otherwise).

## 10. Data Flow

`Route (auth) → DailyActivityController → DailyActivityRequest → DailyActivity model →
MySQL → redirect/view`.

## 11. File Map

| Concern    | Files                                                                     |
| ---------- | ------------------------------------------------------------------------- |
| Controller | `app/Http/Controllers/DailyActivityController.php`                        |
| Request    | `app/Http/Requests/DailyActivityRequest.php`                              |
| Model      | `app/Models/DailyActivity.php`                                            |
| Policy     | `app/Policies/DailyActivityPolicy.php`                                    |
| View       | `resources/views/daily-activities/*`                                      |
| Migration  | `database/migrations/2026_09_18_100005_create_daily_activities_table.php` |

## 12. AI Integration

Daily Activities are **semantically indexed** (`SOURCE_DAILY_ACTIVITY`) and are a core
part of the AI context. The AI can summarize today/this week, find time by category,
and find activities linked to a goal — always from the user's own records.

## 13. Known Limitations

- The old `tasks` and `work_logs` tables still exist in the database but are orphaned
  (see [DATABASE.md](../DATABASE.md#legacy-tables)).

## 14. Future Improvements (planned)

- Optional recurring activities.

## 15. Change History

- Initial audit and documentation (2026-09-20).
