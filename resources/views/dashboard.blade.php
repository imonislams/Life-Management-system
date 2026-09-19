<x-app-layout>
    <x-slot name="title">Dashboard - Life Management System</x-slot>
    <x-slot name="pageTitle">Dashboard</x-slot>

    <!-- Header Panel -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h1 class="card-title">Life Management System</h1>
        <p class="card-subtitle">Welcome, {{ Auth::user()->name }} — here is your complete overview.</p>
    </div>

    <!-- AI Assistant quick-access widget (100% local). Rendered only when the AI
         layer is configured; a failure never affects the rest of the dashboard. -->
    @if(config('ai.enabled'))
        @php
            try {
                $aiPanel = app(\App\Services\AI\AIHealthService::class)->report(auth()->id());
            } catch (\Throwable $e) {
                $aiPanel = null;
            }
        @endphp
        @if($aiPanel)
        <div class="card" style="margin-bottom: 1.5rem; display:flex; flex-wrap:wrap; gap:1rem; align-items:center; justify-content:space-between;">
            <div>
                <h2 class="card-title" style="margin-bottom:0.25rem;">🤖 Ask AI <span class="badge-active" style="margin-left:0.35rem;">Local</span></h2>
                <p class="card-subtitle" style="margin:0;">
                    Context: {{ $aiPanel['status'] }} · Model {{ $aiPanel['llm_model'] ?: '—' }} · {{ $aiPanel['vector']['record_count'] ?? 0 }} indexed records.
                </p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('ai.assistant') }}" class="btn-primary">Open AI Assistant</a>
                <a href="{{ route('ai.assistant') }}?q=Summarize+my+week" class="btn-secondary">Weekly AI Summary</a>
            </div>
        </div>
        @endif
    @endif

    <!-- Top Summary Cards: Money / Activities / Habits / Goals -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Money — Available Balance</div>
            <div class="summary-card-value balance-color">{{ \App\Support\CurrencyConfig::format(auth()->id(), $availableBalance) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Income {{ \App\Support\CurrencyConfig::format(auth()->id(), $totalIncome) }} · Expense {{ \App\Support\CurrencyConfig::format(auth()->id(), $totalExpenses) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Activities — Today</div>
            <div class="summary-card-value" style="color: #ea580c;">{{ $todayActivitiesCount }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">{{ $weekActivitiesCount }} this week · {{ $todayActivityMinutes }} min logged today</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Habits — Today</div>
            <div class="summary-card-value income-color">{{ $todayCompletedHabits }}/{{ $activeHabits }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">{{ $habitCompletionRate }}% completed today</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Goals — Overall Progress</div>
            <div class="summary-card-value">{{ $overallGoalProgress }}%</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">{{ $activeGoals }} active · {{ $completedGoals }} completed</div>
        </div>
    </div>

    <!-- 6 Summary Cards Grid -->
    <div class="dashboard-grid-5">
        <div class="summary-card">
            <div class="summary-card-title">Monthly Salary</div>
            <div class="summary-card-value"><x-money :amount="$monthlySalary" /></div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Additional Income</div>
            <div class="summary-card-value income-color"><x-money :amount="$additionalIncome" /></div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Income</div>
            <div class="summary-card-value income-color"><x-money :amount="$totalIncome" /></div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Expenses</div>
            <div class="summary-card-value expense-color"><x-money :amount="$totalExpenses" /></div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Total Savings</div>
            <div class="summary-card-value" style="color: #2563eb;"><x-money :amount="$totalSavings" /></div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Available Balance</div>
            <div class="summary-card-value balance-color"><x-money :amount="$availableBalance" /></div>
        </div>
    </div>

    <!-- Savings Goal Summary Card -->
    @if ($savingsSummary)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header-flex" style="margin-bottom: 0.5rem;">
                <div>
                    <h2 class="card-title">Savings Goal: {{ $savingsSummary['name'] }}</h2>
                    <p class="card-subtitle">Target: <x-money :amount="$savingsSummary['target_amount']" /></p>
                </div>
                <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm">Manage Savings</a>
            </div>

            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center; margin-top: 0.5rem;">
                <div style="flex: 1; min-width: 200px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.875rem; font-weight: 600;">
                        <span>Progress: {{ $savingsSummary['progress_percentage'] }}%</span>
                        <span>Saved: <x-money :amount="$savingsSummary['current_amount']" /></span>
                    </div>
                    <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden;">
                        <div style="width: {{ $savingsSummary['progress_percentage'] }}%; background-color: var(--primary-color); height: 100%;"></div>
                    </div>
                </div>
                <div style="font-size: 0.875rem; color: var(--text-muted); font-weight: 500;">
                    Remaining: <x-money :amount="$savingsSummary['remaining_amount']" />
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
                                    <x-money :amount="$item->amount" />
                                </td>
                                <td>{{ user_date($item->next_due_date) }}</td>
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
                                    <td>{{ user_date($inc->date) }}</td>
                                    <td class="text-amount-income"><x-money :amount="$inc->amount" /></td>
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
                                    <td>{{ user_date($exp->date) }}</td>
                                    <td class="text-amount-expense"><x-money :amount="$exp->amount" /></td>
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

    <!-- ============================================================== -->
    <!-- DAILY MANAGEMENT OVERVIEW                                       -->
    <!-- ============================================================== -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Daily Activities</h2>
                <p class="card-subtitle">What you actually did today, plus your habit and routine progress.</p>
            </div>
            <a href="{{ route('daily-activities.index') }}" class="btn-secondary btn-sm">Open Daily Activities</a>
        </div>

        <div class="dashboard-tx-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <!-- Today's Activities -->
            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div class="card-header-flex" style="margin-bottom: 0.75rem;">
                    <h3 style="font-size: 1rem; font-weight: 600;">Today's Activities</h3>
                    <a href="{{ route('daily-activities.index') }}" class="btn-secondary btn-sm">View</a>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span class="badge badge-inactive">Total {{ $totalActivities }}</span>
                    <span class="badge badge-active">Today {{ $todayActivitiesCount }}</span>
                    <span class="badge badge-income">{{ $todayActivityMinutes }} min</span>
                </div>
                @if($todayActivities->count() > 0)
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem;">
                        @foreach($todayActivities as $act)
                            <li style="display: flex; justify-content: space-between; gap: 0.5rem; padding: 0.3rem 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $act->title }}</span>
                                <span class="badge badge-active" style="flex-shrink: 0;">{{ $act->durationLabel() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Nothing logged today yet.</div>
                @endif
            </div>

            <!-- Recent Activities -->
            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div class="card-header-flex" style="margin-bottom: 0.75rem;">
                    <h3 style="font-size: 1rem; font-weight: 600;">Recent Activities</h3>
                    <a href="{{ route('daily-activities.index') }}" class="btn-secondary btn-sm">View</a>
                </div>
                @if($recentActivities->count() > 0)
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem;">
                        @foreach($recentActivities as $act)
                            <li style="display: flex; justify-content: space-between; gap: 0.5rem; padding: 0.3rem 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $act->title }}</span>
                                <span style="color: var(--text-muted); white-space: nowrap;">{{ user_date($act->activity_date) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No activities recorded yet.</div>
                @endif
            </div>

            <!-- Habit Overview -->
            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div class="card-header-flex" style="margin-bottom: 0.75rem;">
                    <h3 style="font-size: 1rem; font-weight: 600;">Habit Overview</h3>
                    <a href="{{ route('habits.index') }}" class="btn-secondary btn-sm">View</a>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span class="badge badge-active">Active {{ $activeHabits }}</span>
                    <span class="badge badge-income">Done Today {{ $todayCompletedHabits }}</span>
                    <span class="badge badge-inactive">Pending {{ $todayPendingHabits }}</span>
                </div>
                <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 8px; overflow: hidden; margin-bottom: 0.75rem;">
                    <div style="width: {{ $habitCompletionRate }}%; background-color: var(--primary-color); height: 100%;"></div>
                </div>
                @if($todayHabits->count() > 0)
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem;">
                        @foreach($todayHabits as $todayHabit)
                            <li style="display: flex; justify-content: space-between; gap: 0.5rem; padding: 0.3rem 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $todayHabit->title }}</span>
                                @if(in_array($todayHabit->id, $completedTodayHabitIds, true))
                                    <span class="badge badge-income" style="flex-shrink: 0;">Done</span>
                                @else
                                    <span class="badge badge-inactive" style="flex-shrink: 0;">Pending</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No active habits yet.</div>
                @endif
            </div>

            <!-- Routine Overview -->
            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div class="card-header-flex" style="margin-bottom: 0.75rem;">
                    <h3 style="font-size: 1rem; font-weight: 600;">Routine Overview</h3>
                    <a href="{{ route('routine.index') }}" class="btn-secondary btn-sm">View</a>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span class="badge badge-inactive">Total {{ $totalRoutineItems }}</span>
                    <span class="badge badge-income">Completed {{ $completedRoutineItems }}</span>
                    <span class="badge badge-active">Upcoming {{ $upcomingRoutineItems }}</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem;">{{ $routineCompletionRate }}% completed</div>
                @if($routinePreview->count() > 0)
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem;">
                        @foreach($routinePreview as $routineRow)
                            <li style="display: flex; justify-content: space-between; gap: 0.5rem; padding: 0.3rem 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $routineRow->title }}</span>
                                <span style="color: var(--text-muted); white-space: nowrap;">{{ $routineRow->startTimeLabel() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No routine items yet.</div>
                @endif
            </div>

            <!-- Goal Progress Overview (derived from real progress updates) -->
            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div class="card-header-flex" style="margin-bottom: 0.75rem;">
                    <h3 style="font-size: 1rem; font-weight: 600;">Goal Progress</h3>
                    <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm">View</a>
                </div>
                @if($goalProgressOverview->count() > 0)
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem;">
                        @foreach($goalProgressOverview as $gp)
                            <li style="padding: 0.35rem 0; border-bottom: 1px solid #f1f5f9;">
                                <div style="display:flex; justify-content:space-between; gap:0.5rem;">
                                    <a href="{{ route('goals.show', $gp['id']) }}" style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-main); text-decoration:none;">{{ $gp['title'] }}</a>
                                    <span style="white-space:nowrap; font-weight:600;">{{ $gp['percentage'] !== null ? $gp['percentage'] . '%' : '—' }}</span>
                                </div>
                                @if($gp['percentage'] !== null)
                                    <div style="background:#e2e8f0; border-radius:3px; height:6px; overflow:hidden; margin-top:0.3rem;">
                                        <div style="width: {{ min(100, $gp['percentage']) }}%; background: var(--primary-color); height:100%;"></div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No active goals yet.</div>
                @endif
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- PERSONAL GROWTH OVERVIEW                                        -->
    <!-- ============================================================== -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Personal Growth</h2>
                <p class="card-subtitle">Goal progress and personal performance summary.</p>
            </div>
            <a href="{{ route('progress.index') }}" class="btn-secondary btn-sm">Open Progress</a>
        </div>

        <div class="dashboard-tx-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
            <!-- Goal Overview -->
            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div class="card-header-flex" style="margin-bottom: 0.75rem;">
                    <h3 style="font-size: 1rem; font-weight: 600;">Goal Overview</h3>
                    <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm">View</a>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                    <span class="badge badge-inactive">Total {{ $totalGoals }}</span>
                    <span class="badge badge-active">Active {{ $activeGoals }}</span>
                    <span class="badge badge-income">Completed {{ $completedGoals }}</span>
                    @if($overdueGoals > 0)
                        <span class="badge badge-expense">Overdue {{ $overdueGoals }}</span>
                    @endif
                </div>
                <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 8px; overflow: hidden; margin-bottom: 0.75rem;">
                    <div style="width: {{ $overallGoalProgress }}%; background-color: var(--primary-color); height: 100%;"></div>
                </div>
                @if($upcomingTargetDates->count() > 0)
                    <div style="font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 0.25rem;">Upcoming Target Dates</div>
                    <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8rem;">
                        @foreach($upcomingTargetDates as $targetGoal)
                            <li style="display: flex; justify-content: space-between; gap: 0.5rem; padding: 0.3rem 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $targetGoal->title }}</span>
                                <span style="color: var(--text-muted); white-space: nowrap;">{{ user_date($targetGoal->target_date) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">No upcoming target dates.</div>
                @endif
            </div>

            <!-- Progress Overview -->
            <div style="border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem;">
                <div class="card-header-flex" style="margin-bottom: 0.75rem;">
                    <h3 style="font-size: 1rem; font-weight: 600;">Progress Overview</h3>
                    <a href="{{ route('progress.index') }}" class="btn-secondary btn-sm">Details</a>
                </div>
                @php
                    $progressBars = [
                        'Habit completion' => $habitCompletionRate,
                        'Goal progress' => $overallGoalProgress,
                        'Routine completion' => $routineCompletionRate,
                        'Savings progress' => $savingsRate,
                    ];
                @endphp
                @foreach($progressBars as $label => $value)
                    <div style="margin-bottom: 0.6rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.2rem;">
                            <span>{{ $label }}</span>
                            <span style="font-weight: 600;">{{ $value }}%</span>
                        </div>
                        <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 6px; overflow: hidden;">
                            <div style="width: {{ $value }}%; background-color: var(--primary-color); height: 100%;"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- IMPORTANT DATES — Upcoming Events                               -->
    <!-- ============================================================== -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Upcoming Events</h2>
                <p class="card-subtitle">
                    {{ $todayEventsCount }} today · {{ $upcomingEventsCount }} upcoming · {{ $monthEventCount }} this month
                </p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('calendar.index') }}" class="btn-secondary btn-sm">Calendar</a>
                <a href="{{ route('events.index') }}" class="btn-secondary btn-sm">All Events</a>
            </div>
        </div>

        @if($nextEvent)
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.5rem; padding: 0.875rem 1rem; margin-bottom: 1rem;">
                <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #1e40af;">Next Event</div>
                <div style="font-weight: 600; margin-top: 0.15rem;">{{ $nextEvent->title }}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">
                    {{ user_date($nextEvent->event_date) }}
                    @if($nextEvent->start_time) · {{ $nextEvent->startTimeLabel() }} @endif
                    @if($nextEvent->location) · {{ $nextEvent->location }} @endif
                </div>
            </div>
        @endif
        @if($upcomingEvents->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcomingEvents as $eventRow)
                            <tr>
                                <td><a href="{{ route('events.show', $eventRow) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">{{ $eventRow->title }}</a></td>
                                <td>{{ user_date($eventRow->event_date) }}</td>
                                <td>{{ $eventRow->start_time ? $eventRow->startTimeLabel() : 'All day' }}</td>
                                <td>{{ $eventRow->location ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state" style="padding: 1.5rem 1rem;">
                <div class="empty-state-title">No upcoming events</div>
                <p style="font-size: 0.875rem;">Add events to see them appear here.</p>
            </div>
        @endif
    </div>

    <!-- ============================================================== -->
    <!-- RECENT ACTIVITY                                                 -->
    <!-- ============================================================== -->
    @if($dashboardLayout !== 'compact')
    <div class="dashboard-tx-grid" style="margin-bottom: 1.5rem;">
        <!-- Recent Goals -->
        <div class="card">
            <div class="card-header-flex">
                <h2 class="card-title">Recent Goals</h2>
                <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm">View All</a>
            </div>
            @if($recentGoals->count() > 0)
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Goal</th>
                                <th>Progress</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentGoals as $goalRow)
                                <tr>
                                    <td>{{ $goalRow->title }}</td>
                                    <td>{{ $goalRow->progress }}%</td>
                                    <td>
                                        @if($goalRow->status === 'completed')
                                            <span class="badge badge-income">Completed</span>
                                        @elseif($goalRow->status === 'in_progress')
                                            <span class="badge badge-active">In Progress</span>
                                        @elseif($goalRow->status === 'paused')
                                            <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Paused</span>
                                        @else
                                            <span class="badge badge-inactive">Not Started</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-title">No goals yet</div>
                </div>
            @endif
        </div>

        <!-- Recent Events -->
        <div class="card">
            <div class="card-header-flex">
                <h2 class="card-title">Recent Events</h2>
                <a href="{{ route('events.index') }}" class="btn-secondary btn-sm">View All</a>
            </div>
            @if($recentEvents->count() > 0)
                <div class="data-table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentEvents as $recentEventRow)
                                <tr>
                                    <td>{{ $recentEventRow->title }}</td>
                                    <td>{{ user_date($recentEventRow->event_date) }}</td>
                                    <td><span class="badge badge-inactive">{{ ucfirst($recentEventRow->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-title">No past events</div>
                </div>
            @endif
        </div>
    </div>
    @endif
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
                                label: @json('Total Income (' . ($currencyFormat['code'] ?? '') . ')'),
                                data: @json($monthlyIncomeData),
                                backgroundColor: '#22c55e',
                                borderRadius: 4
                            },
                            {
                                label: @json('Total Expenses (' . ($currencyFormat['code'] ?? '') . ')'),
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
