# Calendar

## 1. Purpose

A read-only calendar view of the user's events.

## 2. Current Status

Implemented.

## 3. User Flow

Sidebar → Important Dates → Calendar. Events appear on their dates; the user links to
Events to edit.

## 4. UI

- **Route**: `GET /important-dates/calendar` → `calendar.index`.
- **View**: `resources/views/calendar/index.blade.php`.

## 5. Backend

- **Controller**: `App\Http\Controllers\CalendarController@index`.
- **Models**: reads `Event` (and may compute month grids).
- **Requests/services**: none.

## 6. Database

Reads `events`. See [DATABASE.md](../DATABASE.md).

## 7. Business Rules

The calendar renders the authenticated user's events for the displayed period.

## 8. Validation

None (read-only).

## 9. Permissions & Security

Requires `auth`; user-scoped reads.

## 10. Data Flow

`GET → CalendarController@index → Event model → MySQL → calendar view`.

## 11. File Map

| Concern    | Files                                         |
| ---------- | --------------------------------------------- |
| Controller | `app/Http/Controllers/CalendarController.php` |
| View       | `resources/views/calendar/index.blade.php`    |

## 12. AI Integration

None directly. Calendar data is **Events**, which the AI can query via
`EventAIService`. The calendar page itself has no AI feature.

## 13. Known Limitations

- Read-only; creation/editing happens in the Events module.

## 14. Future Improvements (planned)

- Month navigation and filters.

## 15. Change History

- Initial audit and documentation (2026-09-20).
