# Personal Progress

## 1. Purpose

A consolidated view of recent achievement and activity across goals, updates and
categories.

## 2. Current Status

Implemented.

## 3. User Flow

Sidebar → Personal Growth → Progress.

## 4. UI

- **Route**: `GET /personal-growth/progress` → `progress.index`.
- **View**: `resources/views/progress/index.blade.php`.

## 5. Backend

- **Controller**: `App\Http\Controllers\ProgressController@index`.
- **Models**: aggregates `Goal`, `GoalProgressUpdate`, `DailyActivity`.
- **Requests/services**: none.

## 6. Database

Reads `goals`, `goal_progress_updates`, `daily_activities`. See
[DATABASE.md](../DATABASE.md).

## 7. Business Rules

Figures are computed from the user's own rows (recent updates, completed goals,
active categories).

## 8. Validation

None (read-only).

## 9. Permissions & Security

Requires `auth`; user-scoped queries.

## 10. Data Flow

`GET → ProgressController@index → models → MySQL → progress view`.

## 11. File Map

| Concern    | Files                                         |
| ---------- | --------------------------------------------- |
| Controller | `app/Http/Controllers/ProgressController.php` |
| View       | `resources/views/progress/index.blade.php`    |

## 12. AI Integration

`ProgressAIService` (used by the AI assistant) summarizes recent progress updates and
achievements. The Progress **page** itself does not call the AI. Progress data is
represented in the AI via goal/progress/activity sources.

## 13. Known Limitations

- No AI summary embedded directly on the page.

## 14. Future Improvements (planned)

- Inline "Summarize my progress" action on the page.

## 15. Change History

- Initial audit and documentation (2026-09-20).
