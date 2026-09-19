# Goals & Progress Updates

## 1. Purpose

Track personal goals (measurable or qualitative) with a dated progress history and
time spent.

## 2. Current Status

Implemented.

## 3. User Flow

Sidebar → Personal Growth → Goals → create/edit/delete a goal → update progress →
add/edit/delete dated progress updates. The **Progress** page aggregates this
alongside activities.

## 4. UI

- **Views**: `resources/views/goals/*`, `resources/views/progress/index.blade.php`.
- **Routes**: `goals.*` (resource), `goals.progress`,
  `goal-progress.store|update|destroy`, `progress.index`.

## 5. Backend

- **Controllers**: `App\Http\Controllers\GoalController` (`updateProgress`),
  `GoalProgressUpdateController`, `ProgressController`.
- **Requests**: `App\Http\Requests\GoalRequest`,
  `App\Http\Requests\GoalProgressUpdateRequest`.
- **Models**: `App\Models\Goal`, `App\Models\GoalProgressUpdate`.

## 6. Database

- `goals`: `progress_type` (`measurable|qualitative`), `target_amount`,
  `current_amount`, `progress`, `start_date`, `target_date`, `status`
  (`not_started|in_progress|completed|paused|cancelled`), `completed_at`.
- `goal_progress_updates`: `goal_id`, `date`, `description`, `progress_value`,
  `time_spent_minutes`, `notes`.

Relationships: `User → hasMany Goal`; `Goal → hasMany GoalProgressUpdate`;
`Goal → hasMany DailyActivity` (via `daily_activities.goal_id`).

## 7. Business Rules

Derived helpers (all computed by Laravel, never the LLM):

- `progressPercentage()` — measurable: `current_amount / target_amount`; qualitative:
  the stored `progress`.
- `daysElapsed()`, `daysRemaining()`, `durationInDays()` — from `start_date` /
  `target_date`.
- `isOverdue()` — target date passed and not completed.

## 8. Validation

`GoalRequest` and `GoalProgressUpdateRequest` validate titles, dates, numeric values,
status/progress-type enums, and that referenced ids belong to the user.

## 9. Permissions & Security

Requires `auth`. `GoalPolicy` (`view/update/delete/updateProgress`) and
`GoalProgressUpdatePolicy` enforce ownership; the `{goal}` and `{progressUpdate}`
bindings are user-scoped.

## 10. Data Flow

`Route (auth) → Controller → Request → Goal/GoalProgressUpdate → MySQL →
view/redirect`.

## 11. File Map

| Concern     | Files                                                                                                   |
| ----------- | ------------------------------------------------------------------------------------------------------- |
| Controllers | `app/Http/Controllers/GoalController.php`, `GoalProgressUpdateController.php`, `ProgressController.php` |
| Requests    | `app/Http/Requests/GoalRequest.php`, `GoalProgressUpdateRequest.php`                                    |
| Models      | `app/Models/Goal.php`, `GoalProgressUpdate.php`                                                         |
| Policies    | `app/Policies/GoalPolicy.php`, `GoalProgressUpdatePolicy.php`                                           |
| Views       | `resources/views/goals/*`, `resources/views/progress/index.blade.php`                                   |
| Migrations  | `2026_09_18_100006_create_goals_table.php`, `2026_09_18_100007_create_goal_progress_updates_table.php`  |

## 12. AI Integration

Goals and Goal Progress are **semantically indexed** (`SOURCE_GOAL`,
`SOURCE_GOAL_PROGRESS`). `GoalAIService` supplies verified progress %, days
elapsed/remaining and recent activity so the AI can answer _"How is my goal
progress?"_ or _"How many days are left?"_ with exact numbers.

## 13. Known Limitations

- Progress updates are free-text plus a numeric value; no automated task linkage.

## 14. Future Improvements (planned)

- Milestone sub-goals.

## 15. Change History

- Initial audit and documentation (2026-09-20).
