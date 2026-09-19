# Feature Registry

Status reflects the **actual implementation** as audited.

Legend: ✓ Implemented · ◑ Partial · ○ Planned · — Not applicable

| Feature                                         | Status    | UI  | Backend   | Database             | AI            | Doc                                                |
| ----------------------------------------------- | --------- | --- | --------- | -------------------- | ------------- | -------------------------------------------------- |
| Authentication (login/register/logout)          | ✓         | ✓   | ✓         | ✓                    | —             | —                                                  |
| Dashboard                                       | ✓         | ✓   | ✓         | ✓                    | ◑ (widget)    | [DASHBOARD](./features/DASHBOARD.md)               |
| Money Management (analytics)                    | ✓         | ✓   | ✓         | ✓                    | ◑             | [FINANCE](./features/FINANCE.md)                   |
| Income                                          | ✓         | ✓   | ✓         | ✓                    | ◑ (notes)     | [FINANCE](./features/FINANCE.md)                   |
| Salary                                          | ✓         | ✓   | ✓         | ✓                    | ◑             | [FINANCE](./features/FINANCE.md)                   |
| Expenses                                        | ✓         | ✓   | ✓         | ✓                    | ◑ (notes)     | [FINANCE](./features/FINANCE.md)                   |
| Savings (goals + ledger)                        | ✓         | ✓   | ✓         | ✓                    | ◑             | [FINANCE](./features/FINANCE.md)                   |
| Recurring Finance                               | ✓         | ✓   | ✓         | ✓                    | ◑             | [FINANCE](./features/FINANCE.md)                   |
| Currency management                             | ✓         | ✓   | ✓         | ✓                    | ✓             | [SETTINGS](./features/SETTINGS.md)                 |
| Daily Activities                                | ✓         | ✓   | ✓         | ✓                    | ✓             | [DAILY_ACTIVITIES](./features/DAILY_ACTIVITIES.md) |
| Daily Routine (templates + occurrences)         | ✓         | ✓   | ✓         | ✓                    | ○             | [ROUTINE](./features/ROUTINE.md)                   |
| Habits (+ activities + completions)             | ✓         | ✓   | ✓         | ✓                    | ✓             | [HABITS](./features/HABITS.md)                     |
| Goals (+ progress updates)                      | ✓         | ✓   | ✓         | ✓                    | ✓             | [GOALS](./features/GOALS.md)                       |
| Personal Progress                               | ✓         | ✓   | ✓         | ✓                    | ✓             | [PROGRESS](./features/PROGRESS.md)                 |
| Events                                          | ✓         | ✓   | ✓         | ✓                    | ✓             | [EVENTS](./features/EVENTS.md)                     |
| Calendar                                        | ✓         | ✓   | ✓         | ✓                    | ○             | [CALENDAR](./features/CALENDAR.md)                 |
| Settings (all sections)                         | ✓         | ✓   | ✓         | ✓                    | ◑ (AI status) | [SETTINGS](./features/SETTINGS.md)                 |
| **Local AI Assistant (RAG)**                    | ✓         | ✓   | ✓         | ✓                    | ✓             | [AI_ASSISTANT](./features/AI_ASSISTANT.md)         |
| AI semantic indexing + reindex                  | ✓         | —   | ✓         | ✓                    | ✓             | [AI_ASSISTANT](./features/AI_ASSISTANT.md)         |
| **Fine-tuning samples** (`ai_training_samples`) | ○ Planned | —   | ◑ (model) | ✓                    | —             | —                                                  |
| **Task / Work Log modules**                     | — Removed | —   | —         | Legacy tables remain | —             | [DATABASE](./DATABASE.md#legacy-tables)            |

---

## Notes on the audited statuses

- **Routine is not indexed by the AI.** There is no `RoutineItem`/`RoutineOccurrence`
  source type in the indexing service, so routine data is not part of semantic search
  or the AI context. Marked ○ for AI.
- **Calendar is a read-only view** of Events; it has no separate AI integration
  (Events do, via the AI layer).
- **Fine-tuning** code exists (`AiTrainingSample` model, `ai_training_samples` table,
  `ai.fine_tuning.collect_samples` config) but is **unused scaffolding** — no code
  captures samples. It is not part of the shipped feature set.
- **Finance AI is "◑"** because only _textual_ finance fields (descriptions/notes/
  categories) are indexed for semantic search; totals are always computed by SQL.
  See [AI.md](./AI.md).

---

## Feature document template

New feature docs must follow this structure:

```markdown
# Feature Name

## 1. Purpose

## 2. Current Status

## 3. User Flow

## 4. UI (Pages / Routes / Views / Components)

## 5. Backend (Controllers / Requests / Services / Models / Policies/Middleware)

## 6. Database (Tables / Relationships / Migrations)

## 7. Business Rules

## 8. Validation

## 9. Permissions & Security

## 10. Data Flow

## 11. File Map

## 12. AI Integration

## 13. Known Limitations

## 14. Future Improvements (marked as planned)

## 15. Change History
```
