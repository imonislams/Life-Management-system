# Finance (Money Management)

## 1. Purpose

Track personal money: income, salary, expenses, savings goals with a ledger, and
recurring income/expense templates — plus an analytics overview.

## 2. Current Status

Implemented. (Income, Salary, Expenses, Savings goals + transactions, Recurring, and
the Money Management analytics page.)

## 3. User Flow

- **Analytics**: sidebar → Money Management → Analytics shows totals and charts.
- **Income/Expense/Salary/Recurring**: list → create → edit → delete (standard CRUD).
- **Savings**: overview lists goals; create/edit a goal; add deposits/withdrawals
  (transactions) from a goal; edit/delete individual transactions.

## 4. UI

### Pages / Views

| View                                                                   | Purpose                |
| ---------------------------------------------------------------------- | ---------------------- |
| `resources/views/money-management/index.blade.php`                     | Analytics overview     |
| `resources/views/income/{index,create,edit}.blade.php`                 | Income CRUD            |
| `resources/views/expenses/{index,create,edit}.blade.php`               | Expense CRUD           |
| `resources/views/salary/{index,create,edit}.blade.php`                 | Salary CRUD            |
| `resources/views/savings/{index,create,edit,show}.blade.php`           | Savings goals + ledger |
| `resources/views/recurring-transactions/{index,create,edit}.blade.php` | Recurring CRUD         |

### Routes

`money-management.index`; `income.*`, `expenses.*`, `salary.*`,
`recurring-transactions.*` (resources except `show`); `savings.*` (custom goal +
transaction routes). See [API.md](../API.md).

### Components

Shared CSS classes only (`.card`, `.summary-card`, `.data-table`, `.filter-bar`,
`.badge-income`/`.badge-expense`). No custom Blade components.

## 5. Backend

### Controllers

- `App\Http\Controllers\MoneyManagementController` — `index`.
- `IncomeController`, `ExpenseController`, `SalaryController`,
  `RecurringTransactionController` — full resource CRUD.
- `SavingsController` — goals CRUD (`index/create/store/show/edit/update/destroy`) plus
  transactions (`storeTransaction`, `editTransaction`, `updateTransaction`,
  `destroyTransaction`).

### Requests

**None.** These controllers validate **inline** with `$request->validate([...])`.

### Services

**None.** Finance business logic lives in the controllers and models, with formatting
via `App\Support\CurrencyConfig` / `MoneyFormatter` and the `money()` helper.

### Models

`IncomeRecord`, `ExpenseRecord`, `Salary`, `SavingsGoal`, `SavingsTransaction`,
`RecurringTransaction`, `Currency`.

### Policies / Middleware

`SavingsGoalPolicy`, `SavingsTransactionPolicy`, `CurrencyPolicy` are registered;
scoped route bindings resolve `{savingsGoal}` and `{transaction}` to the current user.

## 6. Database

Tables: `income_records`, `expense_records`, `salaries`, `savings_goals`,
`savings_transactions`, `recurring_transactions` (+ `currencies`). See
[DATABASE.md](../DATABASE.md).

## 7. Business Rules

- Each record belongs to exactly one user (`user_id` from the session).
- Savings goals may be multiple per user; `SavingsGoal::progressPercentage()` and
  `remainingAmount()` derive progress from `current_amount` vs `target_amount`.
- Salary: active rows (`is_active = true`) are summed for monthly salary; the legacy
  `users.salary` column is a fallback.
- Amounts carry a `currency_code`; the workspace's master currency is in Settings.

## 8. Validation

Inline (`$request->validate`): amounts numeric ≥ 0, dates valid, currency required,
descriptions string. Exact rules per controller action.

## 9. Permissions & Security

All routes require `auth`. Data is user-scoped by relationship queries and scoped
route bindings; policies cover savings goals/transactions and currencies.

## 10. Data Flow

`Route (auth) → Controller (validate) → Model (user relationship) → MySQL → redirect

- flash`. Reads: `$request->user()->relation()->paginate()`.

## 11. File Map

| Concern     | Files                                                                                                             |
| ----------- | ----------------------------------------------------------------------------------------------------------------- |
| Controllers | `app/Http/Controllers/{MoneyManagement,Income,Expense,Salary,Savings,RecurringTransaction}Controller.php`         |
| Models      | `app/Models/{IncomeRecord,ExpenseRecord,Salary,SavingsGoal,SavingsTransaction,RecurringTransaction,Currency}.php` |
| Formatting  | `app/Support/{CurrencyConfig,MoneyFormatter}.php`, `app/Support/helpers.php`                                      |
| Migrations  | `database/migrations/2026_09_17_100006_*`, `2026_09_18_100001_*`, `2026_09_18_100002_*`, `2026_09_18_100004_*`    |

## 12. AI Integration

- Textual finance fields (`description`, `notes`, `source`, `category`, `name`) are
  semantically indexed. **Exact totals are never indexed** — they are computed by SQL
  in `FinanceAIService` and handed to the model as verified figures.
- Currency is reported per code; amounts in different currencies are never summed.

## 13. Known Limitations

- Finance controllers have no Form Requests (validation is inline).
- No budgets module; `settings.expense_monthly_limit` exists as a preference only.
- Transfers between accounts are not modelled as a distinct type.

## 14. Future Improvements (planned)

- Extract finance validation into Form Requests.
- Add a dedicated budgets module.

## 15. Change History

- Initial audit and documentation (2026-09-20).
