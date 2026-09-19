# Documentation Index — Life Management System

Welcome. This folder is the **single source of truth** for how the Life Management
System is built, organized and extended.

> Everything here describes the **actual implementation**. Where something is only
> planned or configured-but-unused, it is explicitly labelled.

---

## What is this project?

A **personal** Life Management System built with Laravel 12 and MySQL/MariaDB. It
covers Money Management, Daily Activities, Habits, Daily Routine, Goals, Personal
Progress, Events/Calendar, Settings and a **100% local AI Assistant** (Ollama +
Qdrant + RAG).

It is **not** an office/ERP/HR system. There is no employee, attendance, payroll,
department or company workspace.

---

## How this documentation is organized

| Document | Read it when you want to… |
|----------|---------------------------|
| [README.md](./README.md) | Get an overview and find your way around (this file) |
| [ARCHITECTURE.md](./ARCHITECTURE.md) | Understand layers, request lifecycle and how the pieces fit |
| [DATABASE.md](./DATABASE.md) | Understand every table, column, relationship and index |
| [UI.md](./UI.md) | Understand layouts, navigation, views and CSS conventions |
| [UI/ALERTS_AND_NOTIFICATIONS.md](./UI/ALERTS_AND_NOTIFICATIONS.md) | Use the global toast + confirmation-modal system |
| [BACKEND.md](./BACKEND.md) | Understand routes, controllers, requests, models, policies |
| [FEATURES.md](./FEATURES.md) | See a registry of features and their implementation status |
| [API.md](./API.md) | Understand the HTTP surface (routes + the AI JSON endpoints) |
| [AI.md](./AI.md) | Understand the local AI + RAG architecture in detail |
| [SECURITY.md](./SECURITY.md) | Understand authentication, authorization and data isolation |
| [DEVELOPMENT.md](./DEVELOPMENT.md) | Set up the project and follow the development workflow |
| [CHANGELOG.md](./CHANGELOG.md) | See meaningful changes made to the project |

Per-feature deep dives live in [`features/`](./features/).

---

## Where should a developer start?

1. Read this index, then [ARCHITECTURE.md](./ARCHITECTURE.md) for the big picture.
2. Read the feature page for whatever you are about to change
   (see [FEATURES.md](./FEATURES.md)).
3. Follow the change contract in [DEVELOPMENT.md](./DEVELOPMENT.md): **code change
   + documentation update happen together.**
4. Add a [CHANGELOG.md](./CHANGELOG.md) entry when the change is meaningful.

---

## Feature documents

| Feature doc | Module |
|-------------|--------|
| [features/DASHBOARD.md](./features/DASHBOARD.md) | Dashboard overview |
| [features/FINANCE.md](./features/FINANCE.md) | Money Management (Income, Salary, Expenses, Savings, Recurring) |
| [features/DAILY_ACTIVITIES.md](./features/DAILY_ACTIVITIES.md) | Daily Activities journal |
| [features/ROUTINE.md](./features/ROUTINE.md) | Daily Routine (templates + occurrences) |
| [features/HABITS.md](./features/HABITS.md) | Habits + Habit Activities + completions |
| [features/GOALS.md](./features/GOALS.md) | Goals + Progress Updates |
| [features/PROGRESS.md](./features/PROGRESS.md) | Personal Progress dashboard |
| [features/EVENTS.md](./features/EVENTS.md) | Events |
| [features/CALENDAR.md](./features/CALENDAR.md) | Calendar |
| [features/SETTINGS.md](./features/SETTINGS.md) | Per-user Settings (incl. Currency, AI Status) |
| [features/AI_ASSISTANT.md](./features/AI_ASSISTANT.md) | Local AI Assistant (RAG) |

---

## Conventions used in these docs

- File paths are given relative to the project root
  (`C:\xampp\htdocs\Life-Management-system`).
- "Implemented" means the code exists and is wired. "Configured" means settings
  exist. "Planned" means designed but not built. "Legacy/Unused" means the code
  exists but nothing references it.
- Environment variable names are documented with their real, current names.
