# Events

## 1. Purpose

Create and manage personal events/appointments (title, date, time, location, notes,
status).

## 2. Current Status

Implemented.

## 3. User Flow

Sidebar → Important Dates → Events → create/edit/delete an event.

## 4. UI

- **Views**: `resources/views/events/{index,create,edit}.blade.php`.
- **Routes**: `events.*` (resource).

## 5. Backend

- **Controller**: `App\Http\Controllers\EventController`.
- **Request**: `App\Http\Requests\EventRequest`.
- **Model**: `App\Models\Event` — `statusLabel()`, `startTimeLabel()`, `endTimeLabel()`,
  scopes `upcoming()` and `past()`.

## 6. Database

Table `events`: `title`, `description`, `event_date`, `start_time`, `end_time`,
`location`, `notes`, `status` (`upcoming|completed|cancelled`). Relationship:
`User → hasMany Event`. See [DATABASE.md](../DATABASE.md).

## 7. Business Rules

- `upcoming()` = `event_date >= today`; `past()` = `event_date < today`.
- Time labels honour the user's 12h/24h preference via `UserPreference::formatTime()`.

## 8. Validation

`EventRequest` validates title, `event_date`, optional times, status enum.

## 9. Permissions & Security

Requires `auth`. `EventPolicy` guards access; the `{event}` binding is user-scoped.

## 10. Data Flow

`Route (auth) → EventController → EventRequest → Event model → MySQL → view/redirect`.

## 11. File Map

| Concern    | Files                                                           |
| ---------- | --------------------------------------------------------------- |
| Controller | `app/Http/Controllers/EventController.php`                      |
| Request    | `app/Http/Requests/EventRequest.php`                            |
| Model      | `app/Models/Event.php`                                          |
| Policy     | `app/Policies/EventPolicy.php`                                  |
| View       | `resources/views/events/*`                                      |
| Migration  | `database/migrations/2026_09_18_100011_create_events_table.php` |

## 12. AI Integration

Events are **semantically indexed** (`SOURCE_EVENT`). `EventAIService` returns events
for a requested window so the AI can answer _"What events do I have this week?"_ or
_"What is coming up?"_ from real rows.

## 13. Known Limitations

- No reminders are dispatched (notification preferences are saved only).

## 14. Future Improvements (planned)

- Event reminders via the queue.

## 15. Change History

- Initial audit and documentation (2026-09-20).
