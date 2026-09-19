# Daily Routine

## 1. Purpose

Manage recurring daily **routine templates** (e.g. "Study English") and track each
scheduled day as an independent **occurrence** with its own status.

## 2. Current Status

Implemented.

## 3. User Flow

Sidebar → Daily Routine → create/edit/delete a routine template, set its recurrence,
view upcoming occurrences, and mark an occurrence completed/skipped for a given day.

## 4. UI

- **Views**: `resources/views/routine/*` (index/create/edit + occurrences).
- **Routes** (`routine.*`, resource with parameter `routine`): `index`, `create`,
  `store`, `show`, `edit`, `update`, `destroy`; plus `routine.mark`,
  `routine.occurrences`, and `routine-occurrences.update`.

## 5. Backend

- **Controllers**: `App\Http\Controllers\RoutineItemController`,
  `App\Http\Controllers\RoutineOccurrenceController`.
- **Requests**: `App\Http\Requests\RoutineItemRequest`,
  `App\Http\Requests\RoutineOccurrenceRequest`.
- **Models**: `App\Models\RoutineItem`, `App\Models\RoutineOccurrence`.

## 6. Database

- `routine_items` — the template: `title`, `start_time`, `end_time`,
  `recurrence_type` (`one_time|daily|weekly|monthly|custom_days|interval`),
  `interval_days`, `days_of_week`, `start_date`, `end_date`, `status`.
- `routine_occurrences` — per-day rows: `routine_item_id`, `occurrence_date`, `status`
  (`pending|completed|skipped`), `notes`, `completed_at`; **unique**
  `(routine_item_id, occurrence_date)`.

Relationships: `User → hasMany RoutineItem`; `RoutineItem → hasMany RoutineOccurrence`.

## 7. Business Rules

- A routine is a **template**; each scheduled day is a separate occurrence, so
  completing/skipping one day never overwrites the template.
- The unique `(routine_item_id, occurrence_date)` constraint prevents duplicate days.

## 8. Validation

`RoutineItemRequest` / `RoutineOccurrenceRequest` validate the recurrence fields, time
ranges and status values.

## 9. Permissions & Security

Requires `auth`. `RoutineItemPolicy` and `RoutineOccurrencePolicy` guard access; the
`{routine}` and `{occurrence}` bindings are scoped to the current user.

## 10. Data Flow

`Route (auth) → Controller → Request → RoutineItem/RoutineOccurrence → MySQL →
view/redirect`.

## 11. File Map

| Concern     | Files                                                                                                                   |
| ----------- | ----------------------------------------------------------------------------------------------------------------------- |
| Controllers | `app/Http/Controllers/RoutineItemController.php`, `RoutineOccurrenceController.php`                                     |
| Requests    | `app/Http/Requests/RoutineItemRequest.php`, `RoutineOccurrenceRequest.php`                                              |
| Models      | `app/Models/RoutineItem.php`, `RoutineOccurrence.php`                                                                   |
| Policies    | `app/Policies/RoutineItemPolicy.php`, `RoutineOccurrencePolicy.php`                                                     |
| Views       | `resources/views/routine/*`                                                                                             |
| Migrations  | `2026_09_18_100010_create_routine_items_table.php`, `2026_09_18_100003_extend_routine_items_and_create_occurrences.php` |

## 12. AI Integration

**Not indexed.** Routine items/occurrences are not part of semantic search or the AI
context (no source type exists for them). See [FEATURES.md](../FEATURES.md).

## 13. Known Limitations

- No AI integration yet.
- Recurrence generation logic is not documented in the AI layer.

## 14. Future Improvements (planned)

- Add a routine source type so the AI can summarize routine adherence.

## 15. Change History

- Initial audit and documentation (2026-09-20).
