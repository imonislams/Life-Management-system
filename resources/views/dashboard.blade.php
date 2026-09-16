<x-app-layout>
    <x-slot name="title">Dashboard - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Dashboard</x-slot>

    <!-- Header Panel -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h1 class="card-title">Personal Finance Management System</h1>
        <p class="card-subtitle">Welcome, {{ Auth::user()->name }}</p>
    </div>

    <!-- 6 Summary Cards Grid -->
    <div class="dashboard-grid-5">
        <div class="summary-card">
            <div class="summary-card-title">Monthly Salary</div>
            <div class="summary-card-value">{{ currency($monthlySalary) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Additional Income</div>
            <div class="summary-card-value income-color">{{ currency($additionalIncome) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Income</div>
            <div class="summary-card-value income-color">{{ currency($totalIncome) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Expenses</div>
            <div class="summary-card-value expense-color">{{ currency($totalExpenses) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Savings</div>
            <div class="summary-card-value" style="color: #2563eb;">{{ currency($totalSavings) }}</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Available Balance</div>
            <div class="summary-card-value balance-color">{{ currency($availableBalance) }}</div>
        </div>
    </div>

    <!-- Savings Goal Summary Card -->
    @if ($savingsSummary)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header-flex" style="margin-bottom: 0.5rem;">
                <div>
                    <h2 class="card-title">Savings Goal: {{ $savingsSummary['name'] }}</h2>
                    <p class="card-subtitle">Target: {{ currency($savingsSummary['target_amount']) }}</p>
                </div>
                <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm">Manage Savings</a>
            </div>

            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center; margin-top: 0.5rem;">
                <div style="flex: 1; min-width: 200px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.875rem; font-weight: 600;">
                        <span>Progress: {{ $savingsSummary['progress_percentage'] }}%</span>
                        <span>Saved: {{ currency($savingsSummary['current_amount']) }}</span>
                    </div>
                    <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden;">
                        <div style="width: {{ $savingsSummary['progress_percentage'] }}%; background-color: var(--primary-color); height: 100%;"></div>
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">
                    Remaining: {{ currency($savingsSummary['remaining_amount']) }}
                </div>
            </div>
        </div>
    @endif

    <!-- Upcoming Recurring Finance Card -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Upcoming Recurring Finance</h2>
                <p class="card-subtitle">Scheduled active recurring income and expenses.</p>
            </div>
            <a href="{{ route('recurring-transactions.index') }}" class="btn-secondary btn-sm">Manage Recurring</a>
        </div>

        @if ($upcomingRecurring->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Next Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($upcomingRecurring as $item)
                            <tr>
                                <td><strong>{{ $item->title }}</strong></td>
                                <td>
                                    @if ($item->type === 'income')
                                        <span class="badge badge-income">Income</span>
                                    @else
                                        <span class="badge badge-expense">Expense</span>
                                    @endif
                                </td>
                                <td class="{{ $item->type === 'income' ? 'text-amount-income' : 'text-amount-expense' }}">
                                    {{ currency($item->amount) }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($item->next_due_date)->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state" style="padding: 1.5rem 1rem;">
                <div class="empty-state-title">No upcoming active recurring records found.</div>
            </div>
        @endif
    </div>

    <!-- Monthly Income vs Expense Chart -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 class="card-title" style="margin-bottom: 1rem;">Income vs Expense</h2>
        @if (array_sum($monthlyIncomeData) > 0 || array_sum($monthlyExpenseData) > 0)
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No financial data available for chart</div>
                <p>Add income or expense records to view comparison.</p>
            </div>
        @endif
    </div>

    <!-- Recent Activity Grid -->
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

    <!-- Load Chart.js CDN for interactive chart -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const monthlyCanvas = document.getElementById('monthlyChart');
            if (monthlyCanvas) {
                new Chart(monthlyCanvas.getContext('2d'), {
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
