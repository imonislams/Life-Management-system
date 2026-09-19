# Habits

## 1. Purpose

Track habits the user wants to maintain, optionally with independently trackable
sub-activities (e.g. the five prayers), and record daily completions.

## 2. Current Status

Implemented.

## 3. User Flow

Sidebar → Habits → create/edit/delete a habit → toggle completion for a day → manage
the habit's activities (add/edit/delete/toggle).

## 4. UI

- **Views**: `resources/views/habits/*` (index/create/edit).
- **Routes**: `habits.*` (resource) plus `habits.toggle`, and
  `habit-activities.store|toggle|update|destroy`.

## 5. Backend

- **Controllers**: `App\Http\Controllers\HabitController` (`toggleCompletion`),
  `App\Http\Controllers\HabitActivityController`.
- **Requests**: `App\Http\Requests\HabitRequest`,
  `App\Http\Requests\HabitActivityRequest`.
- **Models**: `App\Models\Habit` (`hasActivities()`, `isCompletedOn()`,
  `daysTracked()`), `App\Models\HabitActivity`, `App\Models\HabitCompletion`.

## 6. Database

`habits` (`frequency` ∈ `daily|weekly|custom`, `status` ∈ `active|paused|completed`),
`habit_activities` (`habit_id`, `name`, `sort_order`, `is_active`),
`habit_completions` (`habit_id`, optional `habit_activity_id`, `completed_date`).
Relationships: `Habit → hasMany HabitActivity`, `Habit → hasMany HabitCompletion`.

## 7. Business Rules

- A habit completion can be per habit or per activity.
- `Habit::isCompletedOn($date)` checks existence for a given date.
- AI consistency is computed from **real completion rows** over a window — streaks are
  never fabricated.

## 8. Validation

`HabitRequest` / `HabitActivityRequest` validate title, frequency/status enums, dates.

## 9. Permissions & Security

Requires `auth`. `HabitPolicy`, `HabitActivityPolicy` guard access; the `{habit}` and
`{activity}` bindings are user-scoped.

## 10. Data Flow

`Route (auth) → Controller → Request → Habit/HabitActivity/HabitCompletion → MySQL →
view/redirect`.

## 11. File Map

| Concern     | Files                                                                                              |
| ----------- | -------------------------------------------------------------------------------------------------- |
| Controllers | `app/Http/Controllers/HabitController.php`, `HabitActivityController.php`                          |
| Requests    | `app/Http/Requests/HabitRequest.php`, `HabitActivityRequest.php`                                   |
| Models      | `app/Models/Habit.php`, `HabitActivity.php`, `HabitCompletion.php`                                 |
| Policies    | `app/Policies/HabitPolicy.php`, `HabitActivityPolicy.php`                                          |
| Views       | `resources/views/habits/*`                                                                         |
| Migrations  | `2026_09_18_100008_create_habits_table.php`, `2026_09_18_100009_create_habit_activities_table.php` |

## 12. AI Integration

Habits are **semantically indexed** (`SOURCE_HABIT`). `HabitAIService` computes real
consistency percentages over a window for questions like _"Which habits have I been
consistent with?"_ — never inventing streaks.

## 13. Known Limitations

- Habit consistency window defaults to 30 days in the AI service.

## 14. Future Improvements (planned)

- Configurable consistency windows.

## 15. Change History

- Initial audit and documentation (2026-09-20).
