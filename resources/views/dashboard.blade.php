<x-app-layout>
    <x-slot name="title">Dashboard - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Dashboard</x-slot>

    <!-- Header Panel -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h1 class="card-title">Personal Finance Management System</h1>
        <p class="card-subtitle">Welcome, {{ Auth::user()->name }}</p>
    </div>

    <!-- 5 Summary Cards Grid -->
    <div class="dashboard-grid-5">
        <div class="summary-card">
            <div class="summary-card-title">Monthly Salary</div>
            <div class="summary-card-value">৳ {{ number_format($monthlySalary, 2) }} TK</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Additional Income</div>
            <div class="summary-card-value income-color">৳ {{ number_format($additionalIncome, 2) }} TK</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Income</div>
            <div class="summary-card-value income-color">৳ {{ number_format($totalIncome, 2) }} TK</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Expenses</div>
            <div class="summary-card-value expense-color">৳ {{ number_format($totalExpenses, 2) }} TK</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Remaining Balance</div>
            <div class="summary-card-value balance-color">৳ {{ number_format($remainingBalance, 2) }} TK</div>
        </div>
    </div>

    <!-- Savings Goal Summary Card -->
    @if ($savingsSummary)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header-flex" style="margin-bottom: 0.5rem;">
                <div>
                    <h2 class="card-title">Savings Goal: {{ $savingsSummary['name'] }}</h2>
                    <p class="card-subtitle">Target: ৳ {{ number_format($savingsSummary['target_amount'], 2) }} TK</p>
                </div>
                <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm">Manage Savings</a>
            </div>

            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center; margin-top: 0.5rem;">
                <div style="flex: 1; min-width: 200px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.875rem; font-weight: 600;">
                        <span>Progress: {{ $savingsSummary['progress_percentage'] }}%</span>
                        <span>Saved: ৳ {{ number_format($savingsSummary['current_amount'], 2) }} TK</span>
                    </div>
                    <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden;">
                        <div style="width: {{ $savingsSummary['progress_percentage'] }}%; background-color: var(--primary-color); height: 100%;"></div>
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">
                    Remaining: ৳ {{ number_format($savingsSummary['remaining_amount'], 2) }} TK
                </div>
            </div>
        </div>
    @endif

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
                                    <td class="text-amount-income">৳ {{ number_format($inc->amount, 2) }} TK</td>
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
                                    <td class="text-amount-expense">৳ {{ number_format($exp->amount, 2) }} TK</td>
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
                                label: 'Total Income (TK)',
                                data: @json($monthlyIncomeData),
                                backgroundColor: '#22c55e',
                                borderRadius: 4
                            },
                            {
                                label: 'Total Expenses (TK)',
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
