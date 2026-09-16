<x-app-layout>
    <x-slot name="title">Money Management Dashboard - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Money Management Dashboard</x-slot>

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Money Management Dashboard</h2>
                <p class="card-subtitle">Analytics overview of your complete financial portfolio</p>
            </div>
        </div>
    </div>

    <!-- Summary Cards Grid -->
    <div class="dashboard-grid-5">
        <!-- Total Income -->
        <div class="summary-card">
            <div class="summary-card-title">Total Income</div>
            <div class="summary-card-value income-color">
                {{ currency($totalIncome) }}
            </div>
        </div>

        <!-- Total Salary -->
        <div class="summary-card">
            <div class="summary-card-title">Total Salary</div>
            <div class="summary-card-value">
                {{ currency($monthlySalary) }}
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="summary-card">
            <div class="summary-card-title">Total Expenses</div>
            <div class="summary-card-value expense-color">
                {{ currency($totalExpenses) }}
            </div>
        </div>

        <!-- Total Savings -->
        <div class="summary-card">
            <div class="summary-card-title">Total Savings</div>
            <div class="summary-card-value" style="color: #2563eb;">
                {{ currency($currentSavings) }}
            </div>
        </div>

        <!-- Available Balance -->
        <div class="summary-card">
            <div class="summary-card-title">Available Balance</div>
            <div class="summary-card-value balance-color">
                {{ currency($totalIncome - $totalExpenses - $currentSavings) }}
            </div>
        </div>

        <!-- Recurring Monthly Amount -->
        <div class="summary-card">
            <div class="summary-card-title">Recurring Monthly</div>
            <div class="summary-card-value {{ $recurringMonthlyAmount < 0 ? 'expense-color' : 'income-color' }}">
                {{ currency($recurringMonthlyAmount) }}
            </div>
        </div>
    </div>

    <!-- Monthly Income vs Expense Chart -->
    <div class="card">
        <h2 class="card-title" style="margin-bottom: 1rem;">Monthly Income vs Expense</h2>
        @if (array_sum($monthlyIncomeData) > 0 || array_sum($monthlyExpenseData) > 0)
            <div class="chart-container">
                <canvas id="moneyMonthlyChart"></canvas>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No financial data available for chart</div>
                <p>Add income or expense records to view the comparison.</p>
            </div>
        @endif
    </div>

    <!-- Overview Grid: Savings / Salary / Recurring -->
    <div class="dashboard-tx-grid" style="margin-bottom: 1.5rem;">
        <!-- Savings Overview -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header-flex">
                <h3 class="card-title">Savings Overview</h3>
                <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm">Manage Savings</a>
            </div>

            @if ($savingsGoal)
                <p class="card-subtitle" style="margin-bottom: 0.75rem;">{{ $savingsGoal->name }}</p>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.375rem; font-size: 0.875rem; font-weight: 600;">
                    <span>Progress: {{ $savingsProgress }}%</span>
                    <span>Saved: {{ currency($currentSavings) }}</span>
                </div>
                <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 12px; overflow: hidden;">
                    <div style="width: {{ $savingsProgress }}%; background-color: var(--primary-color); height: 100%;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; font-size: 0.875rem; color: var(--text-muted);">
                    <span>Target: <strong style="color: var(--text-main);">{{ currency($savingsTarget) }}</strong></span>
                    <span>Remaining: <strong style="color: var(--text-main);">{{ currency($savingsRemaining) }}</strong></span>
                </div>
            @else
                <div class="empty-state" style="padding: 1.5rem 1rem;">
                    <div class="empty-state-title">No savings goal set.</div>
                    <p>Create a savings goal to track your progress.</p>
                </div>
            @endif
        </div>

        <!-- Salary Overview -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header-flex">
                <h3 class="card-title">Salary Overview</h3>
                <a href="{{ route('salary.index') }}" class="btn-secondary btn-sm">Manage Salary</a>
            </div>

            @if ($activeSalaryRecord)
                <div style="font-size: 1.75rem; font-weight: 700; color: var(--primary-color); margin-bottom: 0.5rem;">
                    {{ currency($monthlySalary) }}
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.375rem; font-size: 0.875rem; color: var(--text-muted);">
                    <span>
                        Payment day:
                        <strong style="color: var(--text-main);">
                            @if ($activeSalaryRecord->payment_day)
                                Day {{ $activeSalaryRecord->payment_day }}{{ ordinal_suffix((int) $activeSalaryRecord->payment_day) }} of month
                            @else
                                Not set
                            @endif
                        </strong>
                    </span>
                    <span>Active salary records: <strong style="color: var(--text-main);">{{ $activeSalaryCount }}</strong></span>
                    <span>Status: <span class="badge badge-active">Active</span></span>
                </div>
            @else
                <div class="empty-state" style="padding: 1.5rem 1rem;">
                    <div class="empty-state-title">No active salary set.</div>
                    <p>Add a salary record to include it in your analytics.</p>
                </div>
            @endif
        </div>

        <!-- Recurring Finance Overview -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header-flex">
                <h3 class="card-title">Recurring Finance</h3>
                <a href="{{ route('recurring-transactions.index') }}" class="btn-secondary btn-sm">Manage Recurring</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="padding: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                    <div style="font-size: 0.875rem; color: #64748b;">Active Scheduled Items</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">{{ $activeRecurringCount }} Items</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div style="padding: 0.75rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.375rem;">
                        <div style="font-size: 0.75rem; color: #166534; font-weight: 600;">Monthly Recurring Income</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #16a34a;">{{ currency($recurringIncomeTotal, false) }}</div>
                    </div>
                    <div style="padding: 0.75rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.375rem;">
                        <div style="font-size: 0.75rem; color: #991b1b; font-weight: 600;">Monthly Recurring Expense</div>
                        <div style="font-size: 1rem; font-weight: 700; color: #dc2626;">{{ currency($recurringExpenseTotal, false) }}</div>
                    </div>
                </div>

                <div style="font-size: 0.8125rem; color: var(--text-muted);">
                    Salary is managed separately and is not counted as recurring income.
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="dashboard-tx-grid">
        <!-- Recent Income -->
        <div class="card">
            <div class="card-header-flex">
                <h2 class="card-title">Recent Income</h2>
                <a href="{{ route('income.index') }}" class="btn-secondary btn-sm">View All</a>
            </div>

            @if ($recentIncome->count() > 0)
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentIncome as $inc)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($inc->date)->format('M d, Y') }}</td>
                                    <td class="text-amount-income">{{ currency($inc->amount) }}</td>
                                    <td>{{ $inc->description ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-title">No recent income records found.</div>
                </div>
            @endif
        </div>

        <!-- Recent Expenses -->
        <div class="card">
            <div class="card-header-flex">
                <h2 class="card-title">Recent Expenses</h2>
                <a href="{{ route('expenses.index') }}" class="btn-secondary btn-sm">View All</a>
            </div>

            @if ($recentExpenses->count() > 0)
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentExpenses as $exp)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($exp->date)->format('M d, Y') }}</td>
                                    <td class="text-amount-expense">{{ currency($exp->amount) }}</td>
                                    <td>{{ $exp->description ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-title">No recent expense records found.</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Chart.js for the Monthly Income vs Expense chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const moneyCanvas = document.getElementById('moneyMonthlyChart');
            if (moneyCanvas) {
                new Chart(moneyCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: @json($monthlyChartLabels),
                        datasets: [
                            {
                                label: 'Total Income ({{ currency_label() }})',
                                data: @json($monthlyIncomeData),
                                backgroundColor: '#22c55e',
                                borderRadius: 4
                            },
                            {
                                label: 'Total Expenses ({{ currency_label() }})',
                                data: @json($monthlyExpenseData),
                                backgroundColor: '#ef4444',
                                borderRadius: 4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
