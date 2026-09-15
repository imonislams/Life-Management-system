<x-app-layout>
    <x-slot name="title">Dashboard - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Dashboard</x-slot>

    <!-- Header Panel -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h1 class="card-title">Personal Life Management System</h1>
        <p class="card-subtitle">Welcome back, {{ Auth::user()->name }}</p>
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

    <!-- Charts Section -->
    <div class="dashboard-charts-grid">
        <!-- Monthly Income vs Expense Chart -->
        <div class="card">
            <h2 class="card-title" style="margin-bottom: 1rem;">Income vs Expenses (Last 6 Months)</h2>
            @if (array_sum($monthlyIncomeData) > 0 || array_sum($monthlyExpenseData) > 0)
                <div class="chart-container">
                    <canvas id="monthlyChart"></canvas>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-title">No data available for this chart</div>
                    <p>Add income or expense records to view monthly comparison.</p>
                </div>
            @endif
        </div>

        <!-- Expense Category Chart -->
        <div class="card">
            <h2 class="card-title" style="margin-bottom: 1rem;">Expense Distribution by Category</h2>
            @if (count($categoryData) > 0 && array_sum($categoryData) > 0)
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-title">No data available for this chart</div>
                    <p>Add expense records with categories to view distribution.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Transactions Grid -->
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
                    <div class="empty-state-title">No income records yet.</div>
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
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentExpenses as $exp)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($exp->date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge">{{ $exp->category->name ?? 'Uncategorized' }}</span>
                                    </td>
                                    <td class="text-amount-expense">৳ {{ number_format($exp->amount, 2) }} TK</td>
                                    <td>{{ $exp->description ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-title">No expense records yet.</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Load Chart.js CDN for interactive charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Monthly Bar Chart
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
                                label: 'Expenses (TK)',
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

            // Category Doughnut Chart
            const categoryCanvas = document.getElementById('categoryChart');
            if (categoryCanvas) {
                new Chart(categoryCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: @json($categoryLabels),
                        datasets: [{
                            data: @json($categoryData),
                            backgroundColor: [
                                '#3b82f6', '#ef4444', '#10b981', '#f59e0b',
                                '#8b5cf6', '#ec4899', '#6366f1', '#64748b'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right' }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
