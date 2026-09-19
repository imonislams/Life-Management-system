<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Life Management System dashboard: a complete summary of the
     * authenticated user's money, tasks, habits, routine, goals and events.
     *
     * All figures come from real database records owned by the current user.
     */
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $today = Carbon::today();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // ------------------------------------------------------------------
        // 1. Money Management Summary (existing logic preserved)
        // ------------------------------------------------------------------
        $activeSalariesSum = (float) $user->salaries()->where('is_active', true)->sum('amount');
        $monthlySalary = $activeSalariesSum > 0 ? $activeSalariesSum : (float) ($user->salary ?? 0);

        $additionalIncome = (float) $user->incomeRecords()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('amount');

        $totalIncome = $monthlySalary + $additionalIncome;

        $totalExpenses = (float) $user->expenseRecords()
            ->whereYear('date', $currentYear)
            ->whereMonth('date', $currentMonth)
            ->sum('amount');

        // 2. Savings Goals Summary (all goals owned by the user)
        $savingsGoals = $user->savingsGoals()->get();
        $savingsGoal = $savingsGoals->first();
        $totalSavings = (float) $savingsGoals->sum('current_amount');
        $savingsTotalTarget = (float) $savingsGoals->sum('target_amount');
        $savingsRate = $savingsTotalTarget > 0
            ? (int) round(($totalSavings / $savingsTotalTarget) * 100)
            : 0;
        $availableBalance = $totalIncome - $totalExpenses - $totalSavings;
        $savingsSummary = null;

        if ($savingsGoal) {
            $target = (float) $savingsGoal->target_amount;
            $current = (float) $savingsGoal->current_amount;
            $remaining = max(0, $target - $current);
            $progress = $target > 0 ? min(100, round(($current / $target) * 100, 2)) : 0;

            $savingsSummary = [
                'name' => $savingsGoal->name,
                'target_amount' => $target,
                'current_amount' => $current,
                'remaining_amount' => $remaining,
                'progress_percentage' => $progress,
            ];
        }

        // 3. Monthly Income vs Expense Chart (Last 6 Months)
        $monthlyChartLabels = [];
        $monthlyIncomeData = [];
        $monthlyExpenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;

            $monthlyChartLabels[] = $monthDate->format('M Y');

            $mAddIncome = (float) $user->incomeRecords()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            // Include monthly salary if set
            $mTotalIncome = $monthlySalary + $mAddIncome;

            $mExpenses = (float) $user->expenseRecords()
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->sum('amount');

            $monthlyIncomeData[] = $mTotalIncome;
            $monthlyExpenseData[] = $mExpenses;
        }

        // 4. Recent Transactions
        $recentIncome = $user->incomeRecords()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $recentExpenses = $user->expenseRecords()
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // 5. Upcoming Active Recurring Transactions
        $upcomingRecurring = $user->recurringTransactions()
            ->where('is_active', true)
            ->orderBy('next_due_date', 'asc')
            ->get();

        // Net recurring summary (active recurring income minus expense).
        $netRecurringAmount = (float) $user->recurringTransactions()
            ->where('is_active', true)
            ->where('type', 'income')
            ->sum('amount')
            - (float) $user->recurringTransactions()
                ->where('is_active', true)
                ->where('type', 'expense')
                ->sum('amount');

        $activeRecurringCount = $user->recurringTransactions()->where('is_active', true)->count();

        // Unified recent financial activity.
        $recentFinancial = collect();

        foreach ($recentIncome as $income) {
            $recentFinancial->push([
                'type' => 'income',
                'title' => $income->description ?: 'Income',
                'date' => $income->date,
                'amount' => (float) $income->amount,
            ]);
        }

        foreach ($recentExpenses as $expense) {
            $recentFinancial->push([
                'type' => 'expense',
                'title' => $expense->description ?: 'Expense',
                'date' => $expense->date,
                'amount' => (float) $expense->amount,
            ]);
        }

        $recentFinancial = $recentFinancial
            ->sortByDesc(function ($item) {
                return Carbon::parse($item['date'])->timestamp;
            })
            ->take(5);

        // ------------------------------------------------------------------
        // 2. Daily Activities Summary (replaces Tasks + Work Logs)
        // ------------------------------------------------------------------
        $todayActivities = $user->dailyActivities()
            ->with('goal')
            ->whereDate('activity_date', $today)
            ->orderByDesc('id')
            ->get();

        $recentActivities = $user->dailyActivities()
            ->with('goal')
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $todayActivitiesCount = $todayActivities->count();
        $todayActivityMinutes = (int) $todayActivities->sum(fn ($a) => (int) $a->resolveDuration());
        $totalActivities = $user->dailyActivities()->count();

        $weekActivitiesCount = $user->dailyActivities()
            ->whereBetween('activity_date', [$today->copy()->startOfWeek()->toDateString(), $today->copy()->endOfWeek()->toDateString()])
            ->count();

        // ------------------------------------------------------------------
        // 3. Habits Summary
        // ------------------------------------------------------------------
        $activeHabits = $user->habits()->where('status', 'active')->count();
        $todayCompletedHabits = $user->habitCompletions()
            ->whereDate('completed_date', $today)
            ->count();
        $todayPendingHabits = max(0, $activeHabits - $todayCompletedHabits);
        $habitCompletionRate = $activeHabits > 0
            ? (int) round(($todayCompletedHabits / $activeHabits) * 100)
            : 0;

        $todayHabits = $user->habits()
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $completedTodayHabitIds = $user->habitCompletions()
            ->whereDate('completed_date', $today)
            ->pluck('habit_id')
            ->all();

        // ------------------------------------------------------------------
        // 4. Daily Routine Summary
        // ------------------------------------------------------------------
        $totalRoutineItems = $user->routines()->count();
        $completedRoutineItems = $user->routines()->where('status', 'completed')->count();
        $upcomingRoutineItems = $user->routines()
            ->where('status', 'active')
            ->whereTime('start_time', '>', $now->format('H:i:s'))
            ->count();
        $routineCompletionRate = $totalRoutineItems > 0
            ? (int) round(($completedRoutineItems / $totalRoutineItems) * 100)
            : 0;

        $routinePreview = $user->routines()
            ->orderBy('start_time')
            ->take(5)
            ->get();

        // ------------------------------------------------------------------
        // 5. Goals Summary
        // ------------------------------------------------------------------
        $totalGoals = $user->goals()->count();
        $activeGoals = $user->goals()->active()->count();
        $completedGoals = $user->goals()->where('status', 'completed')->count();
        $pausedGoals = $user->goals()->where('status', 'paused')->count();
        $overallGoalProgress = $totalGoals > 0
            ? (int) round((float) $user->goals()->avg('progress'))
            : 0;
        $overdueGoals = $user->goals()->overdue()->count();

        $upcomingTargetDates = $user->goals()
            ->whereNotNull('target_date')
            ->whereDate('target_date', '>=', $today)
            ->where('status', '!=', 'completed')
            ->orderBy('target_date')
            ->take(5)
            ->get();

        // ------------------------------------------------------------------
        // 6. Events Summary
        // ------------------------------------------------------------------
        $todayEventsCount = $user->events()->whereDate('event_date', $today)->count();
        $upcomingEventsCount = $user->events()->upcoming()->count();
        $monthEventCount = $user->events()
            ->whereYear('event_date', $currentYear)
            ->whereMonth('event_date', $currentMonth)
            ->count();

        $nextEvent = $user->events()
            ->whereDate('event_date', '>=', $today)
            ->where('status', '!=', 'cancelled')
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->first();

        $upcomingEvents = $user->events()
            ->whereDate('event_date', '>=', $today)
            ->where('status', '!=', 'cancelled')
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $recentEvents = $user->events()
            ->whereDate('event_date', '<', $today)
            ->orderBy('event_date', 'desc')
            ->take(5)
            ->get();

        // ------------------------------------------------------------------
        // 7. Recent Activity helpers
        // ------------------------------------------------------------------
        $recentGoals = $user->goals()
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Top goals with their derived progress percentage (real data only).
        $goalProgressOverview = $user->goals()
            ->whereIn('status', ['not_started', 'in_progress', 'paused'])
            ->orderByDesc('id')
            ->take(5)
            ->get()
            ->map(fn ($g) => [
                'title' => $g->title,
                'percentage' => $g->progressPercentage(),
                'days_remaining' => $g->daysRemaining(),
                'id' => $g->id,
            ]);

        // ------------------------------------------------------------------
        // 7c. Habit activity tracking (per-activity, e.g. five prayers)
        // ------------------------------------------------------------------
        $totalHabitActivities = $user->habitActivities()->where('is_active', true)->count();
        $completedHabitActivitiesToday = $user->habitCompletions()
            ->whereDate('completed_date', $today)
            ->whereNotNull('habit_activity_id')
            ->count();

        // ------------------------------------------------------------------
        // 7d. Today's routine occurrences (separate from the template)
        // ------------------------------------------------------------------
        $todayOccurrenceSummary = [
            'completed' => $user->routineOccurrences()->whereDate('occurrence_date', $today)->where('status', 'completed')->count(),
            'skipped' => $user->routineOccurrences()->whereDate('occurrence_date', $today)->where('status', 'skipped')->count(),
            'pending' => $user->routineOccurrences()->whereDate('occurrence_date', $today)->where('status', 'pending')->count(),
        ];

        // ------------------------------------------------------------------
        // 8. User settings (currency display + dashboard layout preference)
        // ------------------------------------------------------------------
        $settings = \App\Models\Setting::forUser($user->id);
        $currencyFormat = \App\Support\CurrencyConfig::resolve($user->id);
        $dashboardLayout = $settings->dashboard_layout ?? 'full';

        return view('dashboard', compact(
            'settings',
            'currencyFormat',
            'dashboardLayout',
            // Money
            'monthlySalary',
            'additionalIncome',
            'totalIncome',
            'totalExpenses',
            'totalSavings',
            'savingsRate',
            'availableBalance',
            'savingsSummary',
            'monthlyChartLabels',
            'monthlyIncomeData',
            'monthlyExpenseData',
            'recentIncome',
            'recentExpenses',
            'upcomingRecurring',
            'netRecurringAmount',
            'activeRecurringCount',
            'recentFinancial',
            // Daily Activities
            'todayActivities',
            'recentActivities',
            'todayActivitiesCount',
            'todayActivityMinutes',
            'totalActivities',
            'weekActivitiesCount',
            // Habits
            'activeHabits',
            'todayCompletedHabits',
            'todayPendingHabits',
            'habitCompletionRate',
            'todayHabits',
            'completedTodayHabitIds',
            // Routine
            'totalRoutineItems',
            'completedRoutineItems',
            'upcomingRoutineItems',
            'routineCompletionRate',
            'routinePreview',
            // Goals
            'totalGoals',
            'activeGoals',
            'completedGoals',
            'pausedGoals',
            'overallGoalProgress',
            'overdueGoals',
            'upcomingTargetDates',
            // Events
            'todayEventsCount',
            'upcomingEventsCount',
            'monthEventCount',
            'nextEvent',
            'upcomingEvents',
            'recentEvents',
            // Recent activity
            'recentGoals',
            'goalProgressOverview',
            // Habit activities + routine occurrences
            'totalHabitActivities',
            'completedHabitActivitiesToday',
            'todayOccurrenceSummary'
        ));
    }
}
