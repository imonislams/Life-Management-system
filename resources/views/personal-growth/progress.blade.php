<x-app-layout>
    <x-slot name="title">Progress - Personal Growth</x-slot>
    <x-slot name="pageTitle">Progress</x-slot>

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Personal Progress</h2>
                <p class="card-subtitle">An aggregated view of your daily activities, goals, habits, routine and savings.</p>
            </div>
            <span style="font-size: 0.85rem; color: var(--text-muted);">As of {{ user_date($today) }}</span>
        </div>
    </div>

    <!-- Top Level Summary Cards -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Activities — Today</div>
            <div class="summary-card-value income-color">{{ $todayActivities }}</div>
            <div style="font-size:0.75rem; color:#94a3b8;">{{ $todayActivityMinutes }} min logged</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Active Days</div>
            <div class="summary-card-value">{{ $activeDays }}</div>
            <div style="font-size:0.75rem; color:#94a3b8;">days with activity</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Goal Progress</div>
            <div class="summary-card-value balance-color">{{ $overallGoalProgress }}%</div>
            <div style="font-size:0.75rem; color:#94a3b8;">{{ $totalProgressUpdates }} updates logged</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Habit Completion</div>
            <div class="summary-card-value">{{ $habitCompletionRate }}%</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Savings Progress</div>
            <div class="summary-card-value income-color">{{ $savingsRate }}%</div>
        </div>
    </div>

    <!-- Section: Daily Activities -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Daily Activities</h3>
                <p class="card-subtitle">What you actually did — today, this week and this month.</p>
            </div>
            <a href="{{ route('daily-activities.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Go to Activities</a>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-bottom: 0.5rem;">
            <div class="summary-card">
                <div class="summary-card-title">Today</div>
                <div class="summary-card-value balance-color">{{ $todayActivities }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">This Week</div>
                <div class="summary-card-value">{{ $weekActivities }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">This Month</div>
                <div class="summary-card-value">{{ $monthActivities }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">All Time</div>
                <div class="summary-card-value income-color">{{ $totalActivities }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Total Time</div>
                <div class="summary-card-value">{{ intdiv($totalActivityMinutes, 60) }}h {{ $totalActivityMinutes % 60 }}m</div>
            </div>
        </div>

        @if($recentActivities->count() > 0)
            <h4 style="font-size:0.9rem; font-weight:600; margin:1rem 0 0.5rem;">Recent Activity</h4>
            <ul style="list-style:none; padding:0; margin:0;">
                @foreach($recentActivities as $act)
                    <li style="display:flex; justify-content:space-between; gap:0.5rem; padding:0.4rem 0; border-bottom:1px solid #f1f5f9; font-size:0.85rem;">
                        <a href="{{ route('daily-activities.show', $act) }}" style="color:var(--text-main); text-decoration:none;">{{ $act->title }}</a>
                        <span style="color:#94a3b8; white-space:nowrap;">{{ user_date($act->activity_date) }} · {{ $act->durationLabel() }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <!-- Section: Goal Progress Updates -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Goal Progress</h3>
                <p class="card-subtitle">Your daily work toward each goal, newest first.</p>
            </div>
            <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Go to Goals</a>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 0.5rem;">
            <div class="summary-card">
                <div class="summary-card-title">Total Goals</div>
                <div class="summary-card-value">{{ $totalGoals }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Average Progress</div>
                <div class="summary-card-value balance-color">{{ $averageGoalProgress }}%</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Progress Updates</div>
                <div class="summary-card-value income-color">{{ $totalProgressUpdates }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Time on Goals</div>
                <div class="summary-card-value">{{ intdiv($progressTimeMinutes, 60) }}h {{ $progressTimeMinutes % 60 }}m</div>
            </div>
        </div>

        @if($recentGoalUpdates->count() > 0)
            <ul style="list-style:none; padding:0; margin:0;">
                @foreach($recentGoalUpdates as $upd)
                    <li style="padding:0.4rem 0; border-bottom:1px solid #f1f5f9; font-size:0.85rem;">
                        <div style="display:flex; justify-content:space-between; gap:0.5rem;">
                            <span><strong>{{ optional($upd->goal)->title ?? 'Goal' }}</strong> — {{ \Illuminate\Support\Str::limit($upd->description, 60) }}</span>
                            <span style="color:#94a3b8; white-space:nowrap;">{{ user_date($upd->date) }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div style="font-size:0.85rem; color:#94a3b8; font-style:italic;">No goal progress updates yet.</div>
        @endif
    </div>

    <!-- Section: Habits -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Habits</h3>
                <p class="card-subtitle">Today's habit activity against your active habits.</p>
            </div>
            <a href="{{ route('habits.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Go to Habits</a>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 0.5rem;">
            <div class="summary-card">
                <div class="summary-card-title">Active Habits</div>
                <div class="summary-card-value">{{ $activeHabits }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Completed Today</div>
                <div class="summary-card-value income-color">{{ $completedTodayHabits }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Pending Today</div>
                <div class="summary-card-value" style="color: #ea580c;">{{ $pendingTodayHabits }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Completion</div>
                <div class="summary-card-value balance-color">{{ $habitCompletionRate }}%</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Habit Activities Today</div>
                <div class="summary-card-value">{{ $completedHabitActivitiesToday }}/{{ $totalHabitActivities }}</div>
            </div>
        </div>

        <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden;">
            <div style="width: {{ $habitCompletionRate }}%; background-color: var(--primary-color); height: 100%;"></div>
        </div>
    </div>

    <!-- Section: Goals -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Goals</h3>
                <p class="card-subtitle">Overview of your goal progress.</p>
            </div>
            <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Go to Goals</a>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 0.5rem;">
            <div class="summary-card">
                <div class="summary-card-title">Total Goals</div>
                <div class="summary-card-value">{{ $totalGoals }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Active</div>
                <div class="summary-card-value balance-color">{{ $activeGoals }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Completed</div>
                <div class="summary-card-value income-color">{{ $completedGoals }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Overall Progress</div>
                <div class="summary-card-value">{{ $overallGoalProgress }}%</div>
            </div>
        </div>

        <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden;">
            <div style="width: {{ $overallGoalProgress }}%; background-color: var(--primary-color); height: 100%;"></div>
        </div>
    </div>

    <!-- Section: Daily Routine -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Daily Routine</h3>
                <p class="card-subtitle">Status of your scheduled routine items.</p>
            </div>
            <a href="{{ route('routine.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Go to Routine</a>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 0.5rem;">
            <div class="summary-card">
                <div class="summary-card-title">Total Items</div>
                <div class="summary-card-value">{{ $totalRoutineItems }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Completed</div>
                <div class="summary-card-value income-color">{{ $completedRoutineItems }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Upcoming</div>
                <div class="summary-card-value balance-color">{{ $upcomingRoutineItems }}</div>
            </div>
        </div>

        <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden;">
            <div style="width: {{ $routineCompletionRate }}%; background-color: #0f172a; height: 100%;"></div>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-top: 1rem;">
            <div class="summary-card">
                <div class="summary-card-title">Occurrences Completed</div>
                <div class="summary-card-value income-color">{{ $routineOccurrencesCompleted }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Occurrences Skipped</div>
                <div class="summary-card-value" style="color:#ea580c;">{{ $routineOccurrencesSkipped }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Occurrences Pending</div>
                <div class="summary-card-value balance-color">{{ $routineOccurrencesPending }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Occurrence Rate</div>
                <div class="summary-card-value">{{ $routineOccurrenceRate }}%</div>
            </div>
        </div>
    </div>

    <!-- Section: Savings -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Savings</h3>
                <p class="card-subtitle">Progress across all your savings goals.</p>
            </div>
            <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Go to Savings</a>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-bottom: 0.5rem;">
            <div class="summary-card">
                <div class="summary-card-title">Goals</div>
                <div class="summary-card-value">{{ $savingsGoals->count() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Saved</div>
                <div class="summary-card-value income-color">{{ number_format($savingsTotalCurrent, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Target</div>
                <div class="summary-card-value">{{ number_format($savingsTotalTarget, 2) }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Progress</div>
                <div class="summary-card-value balance-color">{{ $savingsRate }}%</div>
            </div>
        </div>

        <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden;">
            <div style="width: {{ min(100, $savingsRate) }}%; background-color: var(--primary-color); height: 100%;"></div>
        </div>
    </div>

</x-app-layout>