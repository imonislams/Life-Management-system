<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    /**
     * Show an aggregated personal progress overview for the authenticated user.
     *
     * All figures are derived from the user's own records; no values are
     * hardcoded or fabricated.
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // ---- Daily Activities -------------------------------------------
        $totalActivities = $user->dailyActivities()->count();
        $todayActivities = $user->dailyActivities()->whereDate('activity_date', $today)->count();
        $weekActivities = $user->dailyActivities()
            ->whereBetween('activity_date', [$today->copy()->startOfWeek()->toDateString(), $today->copy()->endOfWeek()->toDateString()])
            ->count();
        $monthActivities = $user->dailyActivities()
            ->whereBetween('activity_date', [$today->copy()->startOfMonth()->toDateString(), $today->copy()->endOfMonth()->toDateString()])
            ->count();

        $totalActivityMinutes = (int) $user->dailyActivities()->get()->sum(fn ($a) => (int) $a->resolveDuration());
        $todayActivityMinutes = (int) $user->dailyActivities()->whereDate('activity_date', $today)->get()->sum(fn ($a) => (int) $a->resolveDuration());

        $recentActivities = $user->dailyActivities()
            ->with('goal')
            ->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        // Distinct days with at least one activity, used as a real streak proxy.
        $activeDays = $user->dailyActivities()
            ->distinct()
            ->count('activity_date');

        // ---- Habits ------------------------------------------------------
        $activeHabits = $user->habits()->where('status', 'active')->count();
        $completedTodayHabits = $user->habitCompletions()
            ->whereDate('completed_date', $today)
            ->count();
        $pendingTodayHabits = max(0, $activeHabits - $completedTodayHabits);
        $habitCompletionRate = $activeHabits > 0
            ? (int) round(($completedTodayHabits / $activeHabits) * 100)
            : 0;

        // Per-activity habit tracking (e.g. five daily prayers).
        $totalHabitActivities = $user->habitActivities()->where('is_active', true)->count();
        $completedHabitActivitiesToday = $user->habitCompletions()
            ->whereDate('completed_date', $today)
            ->whereNotNull('habit_activity_id')
            ->count();
        $habitActivityCompletionRate = $totalHabitActivities > 0
            ? (int) round(($completedHabitActivitiesToday / $totalHabitActivities) * 100)
            : 0;

        // ---- Goal progress -----------------------------------------------
        $goals = $user->goals()->get();
        $goalPercentages = $goals->map(fn ($g) => $g->progressPercentage())->filter(fn ($p) => $p !== null);
        $averageGoalProgress = $goalPercentages->isNotEmpty() ? (int) round($goalPercentages->avg()) : 0;
        $totalProgressUpdates = $user->goalProgressUpdates()->count();
        $progressTimeMinutes = (int) $user->goalProgressUpdates()->sum('time_spent_minutes');

        $recentGoalUpdates = $user->goalProgressUpdates()
            ->with('goal')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        // ---- Goals -------------------------------------------------------
        $totalGoals = $goals->count();
        $activeGoals = $goals->whereIn('status', ['not_started', 'in_progress', 'paused'])->count();
        $completedGoals = $goals->where('status', 'completed')->count();
        $overallGoalProgress = $averageGoalProgress;

        // ---- Daily Routine ----------------------------------------------
        $totalRoutineItems = $user->routines()->count();
        $completedRoutineItems = $user->routines()->where('status', 'completed')->count();
        $upcomingRoutineItems = $user->routines()
            ->where('status', 'active')
            ->whereTime('start_time', '>', $today->format('H:i:s'))
            ->count();
        $routineCompletionRate = $totalRoutineItems > 0
            ? (int) round(($completedRoutineItems / $totalRoutineItems) * 100)
            : 0;

        // Routine occurrence history (separate from the template).
        $routineOccurrencesCompleted = $user->routineOccurrences()->where('status', 'completed')->count();
        $routineOccurrencesSkipped = $user->routineOccurrences()->where('status', 'skipped')->count();
        $routineOccurrencesPending = $user->routineOccurrences()->where('status', 'pending')->count();
        $routineOccurrenceRate = ($routineOccurrencesCompleted + $routineOccurrencesSkipped) > 0
            ? (int) round(($routineOccurrencesCompleted / ($routineOccurrencesCompleted + $routineOccurrencesSkipped)) * 100)
            : 0;

        // ---- Savings progress -------------------------------------------
        $savingsGoals = $user->savingsGoals()->get();
        $savingsTotalTarget = (float) $savingsGoals->sum('target_amount');
        $savingsTotalCurrent = (float) $savingsGoals->sum('current_amount');
        $savingsRate = $savingsTotalTarget > 0
            ? (int) round(($savingsTotalCurrent / $savingsTotalTarget) * 100)
            : 0;

        return view('personal-growth.progress', compact(
            'totalActivities',
            'todayActivities',
            'weekActivities',
            'monthActivities',
            'totalActivityMinutes',
            'todayActivityMinutes',
            'activeDays',
            'recentActivities',
            'averageGoalProgress',
            'totalProgressUpdates',
            'progressTimeMinutes',
            'recentGoalUpdates',
            'activeHabits',
            'completedTodayHabits',
            'pendingTodayHabits',
            'habitCompletionRate',
            'totalHabitActivities',
            'completedHabitActivitiesToday',
            'habitActivityCompletionRate',
            'totalGoals',
            'activeGoals',
            'completedGoals',
            'overallGoalProgress',
            'totalRoutineItems',
            'completedRoutineItems',
            'upcomingRoutineItems',
            'routineCompletionRate',
            'routineOccurrencesCompleted',
            'routineOccurrencesSkipped',
            'routineOccurrencesPending',
            'routineOccurrenceRate',
            'savingsGoals',
            'savingsTotalTarget',
            'savingsTotalCurrent',
            'savingsRate',
            'today'
        ));
    }
}
