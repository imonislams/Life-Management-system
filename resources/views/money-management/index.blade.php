<x-app-layout>
    <x-slot name="title">Money Analytics - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Money Analytics</x-slot>

    <!-- Header Panel with Title & Period Filter -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Money Analytics</h2>
                <p class="card-subtitle">Understand your income, expenses, savings, and financial balance at a glance.</p>
            </div>

            <!-- Date / Period Filter Form -->
            <form method="GET" action="{{ route('money-management.index') }}" class="filter-group" style="flex-direction: row; align-items: center; gap: 0.5rem;">
                <label for="period" class="filter-label" style="margin: 0; white-space: nowrap;">Period:</label>
                <select name="period" id="period" class="form-control" style="width: auto; padding: 0.375rem 0.75rem;" onchange="this.form.submit()">
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="last_3_months" {{ $period === 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                    <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>This Year</option>
                    <option value="all_time" {{ $period === 'all_time' ? 'selected' : '' }}>All Time</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Analytics Summary Cards Grid (6 Cards) -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <!-- 1. Total Income -->
        <div class="summary-card">
            <div class="summary-card-title">Total Income</div>
            <div class="summary-card-value income-color">
                <x-money :amount="$totalIncome" />
            </div>
        </div>

        <!-- 2. Total Salary -->
        <div class="summary-card">
            <div class="summary-card-title">Total Salary</div>
            <div class="summary-card-value">
                <x-money :amount="$monthlySalary" />
            </div>
        </div>

        <!-- 3. Total Expense -->
        <div class="summary-card">
            <div class="summary-card-title">Total Expense</div>
            <div class="summary-card-value expense-color">
                <x-money :amount="$totalExpenses" />
            </div>
        </div>

        <!-- 4. Total Savings -->
        <div class="summary-card">
            <div class="summary-card-title">Total Savings</div>
            <div class="summary-card-value" style="color: #2563eb;">
                <x-money :amount="$currentSavings" />
            </div>
        </div>

        <!-- 5. Available Balance -->
        <div class="summary-card">
            <div class="summary-card-title">Available Balance</div>
            <div class="summary-card-value balance-color">
                <x-money :amount="$availableBalance" />
            </div>
        </div>

        <!-- 6. Recurring Monthly Amount -->
        <div class="summary-card">
            <div class="summary-card-title">Net Recurring</div>
            <div class="summary-card-value" style="color: {{ $netRecurringAmount >= 0 ? '#16a34a' : '#dc2626' }};">
                <x-money :amount="$netRecurringAmount" />
            </div>
        </div>
    </div>

    <!-- Income vs Expense Chart (CSS Bar Chart) -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Income vs Expense Analytics</h3>
                <p class="card-subtitle">Comparing total monthly income against total expenses for recent months</p>
            </div>
            <div style="display: flex; gap: 1rem; font-size: 0.85rem;">
                <span style="display: flex; align-items: center; gap: 0.35rem;">
                    <span style="width: 12px; height: 12px; background: #16a34a; border-radius: 2px;"></span> Income
                </span>
                <span style="display: flex; align-items: center; gap: 0.35rem;">
                    <span style="width: 12px; height: 12px; background: #dc2626; border-radius: 2px;"></span> Expense
                </span>
            </div>
        </div>

        <div style="display: flex; justify-content: space-around; align-items: flex-end; height: 200px; padding-top: 1.5rem; border-bottom: 1px solid #e2e8f0; gap: 1rem; overflow-x: auto;">
            @foreach($monthlyChart as $bar)
                @php
                    $incHeight = $maxChartVal > 0 ? max(4, round(($bar['income'] / $maxChartVal) * 150)) : 4;
                    $expHeight = $maxChartVal > 0 ? max(4, round(($bar['expense'] / $maxChartVal) * 150)) : 4;
                @endphp
                <div style="display: flex; flex-direction: column; align-items: center; min-width: 60px; flex: 1;">
                    <div style="display: flex; align-items: flex-end; gap: 6px; height: 160px;">
                        <!-- Income Bar -->
                        <div title="Income: <x-money :amount="$bar['income']" />"
                             style="width: 18px; height: {{ $incHeight }}px; background-color: #16a34a; border-radius: 3px 3px 0 0; transition: height 0.3s;">
                        </div>
                        <!-- Expense Bar -->
                        <div title="Expense: <x-money :amount="$bar['expense']" />"
                             style="width: 18px; height: {{ $expHeight }}px; background-color: #dc2626; border-radius: 3px 3px 0 0; transition: height 0.3s;">
                        </div>
                    </div>
                    <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.5rem; font-weight: 500;">{{ $bar['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Analytics Breakdown Grid -->
    <div class="dashboard-tx-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); margin-bottom: 1.5rem;">
        <!-- 1. Expense Analysis -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 0.75rem;">Expense Analysis</h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Current Month Expense</span>
                    <span style="font-weight: 600; color: #dc2626;"><x-money :amount="$currentMonthExpenses" /></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Previous Month Expense</span>
                    <span style="font-weight: 600;"><x-money :amount="$prevMonthExpenses" /></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Expense Trend</span>
                    <span style="font-weight: 600; color: {{ $expenseGrowthPercent > 0 ? '#dc2626' : '#16a34a' }};">
                        {{ $expenseGrowthPercent > 0 ? '+' : '' }}{{ $expenseGrowthPercent }}%
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                    <span style="font-size: 0.875rem; color: #64748b;">Highest Single Expense</span>
                    <span style="font-weight: 600; color: #0f172a;">
                        @if($highestExpenseRecord)
                            <x-money :amount="$highestExpenseRecord->amount" />
                        @else
                            None
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Savings Analysis -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 0.75rem;">Savings Analysis</h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Savings Target</span>
                    <span style="font-weight: 600;"><x-money :amount="$targetSavings" /></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Current Saved</span>
                    <span style="font-weight: 600; color: #2563eb;"><x-money :amount="$currentSavings" /></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                    <span style="font-size: 0.875rem; color: #64748b;">Savings Ratio vs Income</span>
                    <span style="font-weight: 600; color: #2563eb;">{{ $savingsPercentage }}%</span>
                </div>
                <!-- Progress Bar -->
                <div style="background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden; margin-top: 0.25rem;">
                    <div style="background: #2563eb; height: 100%; width: {{ $savingsPercentage }}%;"></div>
                </div>
            </div>
        </div>

        <!-- 3. Salary & Recurring Overview -->
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 0.75rem;">Salary & Scheduled Overview</h3>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Monthly Fixed Salary</span>
                    <span style="font-weight: 600; color: #0f172a;"><x-money :amount="$monthlySalary" /></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Salary Payment Day</span>
                    <span style="font-weight: 600;">
                        {{ $activeSalaryRecord && $activeSalaryRecord->payment_day ? 'Day ' . $activeSalaryRecord->payment_day : 'Not Set' }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                    <span style="font-size: 0.875rem; color: #64748b;">Active Recurring Items</span>
                    <span style="font-weight: 600;">{{ $activeRecurringCount }} Items</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                    <span style="font-size: 0.875rem; color: #64748b;">Scheduled Monthly Net</span>
                    <span style="font-weight: 600; color: {{ $netRecurringAmount >= 0 ? '#16a34a' : '#dc2626' }};">
                        <x-money :amount="$netRecurringAmount" />
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Currency Breakdown (multi-currency safety) -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Totals by Currency</h3>
                <p class="card-subtitle">Amounts in different currencies are shown separately and never summed together.</p>
            </div>
        </div>

        <div class="dashboard-tx-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            <div style="border:1px solid var(--border-color); border-radius:0.5rem; padding:1rem;">
                <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:0.5rem;">Income</h4>
                @forelse($incomeByCurrency as $row)
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; padding:0.2rem 0;">
                        <span class="badge badge-active">{{ $row['code'] }}</span>
                        <span class="text-amount-income">{{ number_format($row['total'], 2) }}</span>
                    </div>
                @empty
                    <div style="font-size:0.8rem; color:#94a3b8; font-style:italic;">No income recorded.</div>
                @endforelse
            </div>

            <div style="border:1px solid var(--border-color); border-radius:0.5rem; padding:1rem;">
                <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:0.5rem;">Salary</h4>
                @forelse($salaryByCurrency as $row)
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; padding:0.2rem 0;">
                        <span class="badge badge-active">{{ $row['code'] }}</span>
                        <span>{{ number_format($row['total'], 2) }}</span>
                    </div>
                @empty
                    <div style="font-size:0.8rem; color:#94a3b8; font-style:italic;">No salary recorded.</div>
                @endforelse
            </div>

            <div style="border:1px solid var(--border-color); border-radius:0.5rem; padding:1rem;">
                <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:0.5rem;">Expenses</h4>
                @forelse($expenseByCurrency as $row)
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; padding:0.2rem 0;">
                        <span class="badge badge-expense">{{ $row['code'] }}</span>
                        <span class="text-amount-expense">{{ number_format($row['total'], 2) }}</span>
                    </div>
                @empty
                    <div style="font-size:0.8rem; color:#94a3b8; font-style:italic;">No expenses recorded.</div>
                @endforelse
            </div>

            <div style="border:1px solid var(--border-color); border-radius:0.5rem; padding:1rem;">
                <h4 style="font-size:0.9rem; font-weight:600; margin-bottom:0.5rem;">Savings</h4>
                @forelse($savingsByCurrency as $row)
                    <div style="display:flex; justify-content:space-between; font-size:0.85rem; padding:0.2rem 0;">
                        <span class="badge badge-active">{{ $row['code'] }}</span>
                        <span class="balance-color">{{ number_format($row['total'], 2) }}</span>
                    </div>
                @empty
                    <div style="font-size:0.8rem; color:#94a3b8; font-style:italic;">No savings recorded.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Transactions Section -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Recent Transactions Activity</h3>
                <p class="card-subtitle">Unified activity log across Income and Expenses</p>
            </div>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions as $tx)
                            <tr>
                                <td>
                                    @if($tx['type'] === 'income')
                                        <span class="badge badge-income">Income</span>
                                    @else
                                        <span class="badge badge-expense">Expense</span>
                                    @endif
                                </td>
                                <td style="font-weight: 500;">{{ $tx['title'] }}</td>
                                <td>{{ user_date($tx['date']) }}</td>
                                <td style="text-align: right;" class="{{ $tx['type'] === 'income' ? 'text-amount-income' : 'text-amount-expense' }}">
                                    {{ $tx['type'] === 'income' ? '+' : '-' }} <x-money :amount="$tx['amount']" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No recent activity recorded</div>
                <p style="font-size: 0.875rem;">Your financial transactions will appear here once you add income or expense items.</p>
            </div>
        @endif
    </div>
</x-app-layout>
