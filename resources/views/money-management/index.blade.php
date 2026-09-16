<x-app-layout>
    <x-slot name="title">Money Management Overview - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Money Management Overview</x-slot>

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Money Management Dashboard</h2>
                <p class="card-subtitle">Overview of your complete financial portfolio</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards Grid -->
    <div class="dashboard-grid-5">
        <!-- Total Income -->
        <div class="summary-card">
            <div class="summary-card-title">Total Income</div>
            <div class="summary-card-value income-color">
                ৳ {{ number_format($totalIncome, 2) }} TK
            </div>
        </div>

        <!-- Monthly Salary -->
        <div class="summary-card">
            <div class="summary-card-title">Monthly Salary</div>
            <div class="summary-card-value">
                ৳ {{ number_format($monthlySalary, 2) }} TK
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="summary-card">
            <div class="summary-card-title">Total Expenses</div>
            <div class="summary-card-value expense-color">
                ৳ {{ number_format($totalExpenses, 2) }} TK
            </div>
        </div>

        <!-- Total Savings Goal/Current -->
        <div class="summary-card">
            <div class="summary-card-title">Total Savings</div>
            <div class="summary-card-value" style="color: #2563eb;">
                ৳ {{ number_format($currentSavings, 2) }} TK
            </div>
        </div>

        <!-- Available Balance -->
        <div class="summary-card">
            <div class="summary-card-title">Current Balance</div>
            <div class="summary-card-value balance-color">
                ৳ {{ number_format($currentBalance, 2) }} TK
            </div>
        </div>
    </div>

    <!-- Modules Quick Navigation & Recurring Summary Grid -->
    <div class="dashboard-tx-grid">
        <!-- Modules Quick Links -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 1rem;">Money Modules</h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <a href="{{ route('income.index') }}" class="btn-secondary" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-radius: 0.375rem; text-decoration: none;">
                    <span>📈 Income Management</span>
                    <span style="font-weight: 600; color: #16a34a;">৳ {{ number_format($additionalIncome, 2) }}</span>
                </a>
                <a href="{{ route('salary.index') }}" class="btn-secondary" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-radius: 0.375rem; text-decoration: none;">
                    <span>💵 Salary Management</span>
                    <span style="font-weight: 600;">৳ {{ number_format($monthlySalary, 2) }}</span>
                </a>
                <a href="{{ route('expenses.index') }}" class="btn-secondary" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-radius: 0.375rem; text-decoration: none;">
                    <span>📉 Expense Management</span>
                    <span style="font-weight: 600; color: #dc2626;">৳ {{ number_format($totalExpenses, 2) }}</span>
                </a>
                <a href="{{ route('savings.index') }}" class="btn-secondary" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; border-radius: 0.375rem; text-decoration: none;">
                    <span>🏦 Savings Management</span>
                    <span style="font-weight: 600; color: #2563eb;">৳ {{ number_format($currentSavings, 2) }}</span>
                </a>
            </div>
        </div>

        <!-- Recurring Finance Summary Card -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h3 class="card-title">Recurring Finance Summary</h3>
                <a href="{{ route('recurring-transactions.index') }}" class="btn-sm btn-secondary">Manage Recurring</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                    <div style="font-size: 0.875rem; color: #64748b;">Active Scheduled Items</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">{{ $activeRecurringCount }} Items</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div style="padding: 0.75rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.375rem;">
                        <div style="font-size: 0.75rem; color: #166534; font-weight: 600;">Monthly Recurring Income</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #16a34a;">৳ {{ number_format($recurringIncomeTotal, 2) }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.375rem;">
                        <div style="font-size: 0.75rem; color: #991b1b; font-weight: 600;">Monthly Recurring Expense</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #dc2626;">৳ {{ number_format($recurringExpenseTotal, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
