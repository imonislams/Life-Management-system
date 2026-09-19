<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitRequest;
use App\Models\Habit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HabitController extends Controller
{
    /**
     * Display a listing of habits for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $query = $user->habits()->withCount('completions')->with('activities');

        if ($request->filled('status') && in_array($request->status, Habit::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('frequency') && in_array($request->frequency, Habit::FREQUENCIES, true)) {
            $query->where('frequency', $request->frequency);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $habits = $query->orderByRaw("CASE WHEN status = 'active' THEN 0 WHEN status = 'paused' THEN 1 ELSE 2 END")
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        // Load today's habit-level (non-activity) completions in a single query.
        $completedTodayIds = $user->habitCompletions()
            ->whereDate('completed_date', $today)
            ->whereNull('habit_activity_id')
            ->pluck('habit_id')
            ->all();

        // Today's completed activity ids, for per-activity progress rendering.
        $completedActivityIds = $user->habitCompletions()
            ->whereDate('completed_date', $today)
            ->whereNotNull('habit_activity_id')
            ->pluck('habit_activity_id')
            ->all();

        $totalHabits = $user->habits()->count();
        $activeHabits = $user->habits()->where('status', 'active')->count();
        $pausedHabits = $user->habits()->where('status', 'paused')->count();
        $completedHabits = $user->habits()->where('status', 'completed')->count();

        $todayCompletedCount = count($completedTodayIds);
        $todayPendingCount = max(0, $activeHabits - $todayCompletedCount);
        $todayCompletionPercentage = $activeHabits > 0
            ? (int) round(($todayCompletedCount / $activeHabits) * 100)
            : 0;

        return view('daily-management.habits.index', compact(
            'habits',
            'completedTodayIds',
            'completedActivityIds',
            'totalHabits',
            'activeHabits',
            'pausedHabits',
            'completedHabits',
            'todayCompletedCount',
            'todayPendingCount',
            'todayCompletionPercentage',
            'today'
        ));
    }

    /**
     * Show the form for creating a new habit.
     */
    public function create()
    {
        return view('daily-management.habits.create');
    }

    /**
     * Store a newly created habit in storage, together with any activities.
     */
    public function store(HabitRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $activities = $data['activities'] ?? [];
        unset($data['activities']);

        // Ownership is always taken from the authenticated user, never the request.
        $habit = $user->habits()->create($data);

        $this->syncActivities($habit, $activities, $user->id);

        return redirect()->route('habits.index')->with('status', 'Habit created successfully.');
    }

    /**
     * Display the specified habit with its activities and completion history.
     */
    public function show(Habit $habit)
    {
        $this->authorize('view', $habit);

        $today = Carbon::today();

        $habit->load(['activities.completions']);

        $completions = $habit->completions()
            ->with('activity')
            ->orderByDesc('completed_date')
            ->paginate(15)
            ->withQueryString();

        $activities = $habit->activities;

        // Daily / weekly / monthly progress computed from real completion rows.
        $dailyProgress = $this->periodProgress($habit, $today, $today);
        $weeklyProgress = $this->periodProgress($habit, $today->copy()->startOfWeek(), $today->copy()->endOfWeek());
        $monthlyProgress = $this->periodProgress($habit, $today->copy()->startOfMonth(), $today->copy()->endOfMonth());

        $totalCompletions = $habit->completions()->count();
        $completedToday = $habit->completions()->whereDate('completed_date', $today)->exists();

        $daysTracked = $habit->daysTracked();
        $progressPercentage = $daysTracked > 0
            ? (int) min(100, round(($totalCompletions / $daysTracked) * 100))
            : 0;

        return view('daily-management.habits.show', compact(
            'habit',
            'completions',
            'activities',
            'dailyProgress',
            'weeklyProgress',
            'monthlyProgress',
            'totalCompletions',
            'completedToday',
            'daysTracked',
            'progressPercentage',
            'today'
        ));
    }

    /**
     * Show the form for editing the specified habit.
     */
    public function edit(Habit $habit)
    {
        $this->authorize('update', $habit);

        $habit->load('activities');

        return view('daily-management.habits.edit', compact('habit'));
    }

    /**
     * Update the specified habit in storage.
     */
    public function update(HabitRequest $request, Habit $habit)
    {
        $this->authorize('update', $habit);

        $data = $request->validated();
        $activities = $data['activities'] ?? [];
        unset($data['activities']);

        $habit->update($data);

        $this->syncActivities($habit, $activities, $request->user()->id);

        return redirect()->route('habits.index')->with('status', 'Habit updated successfully.');
    }

    /**
     * Remove the specified habit from storage.
     */
    public function destroy(Habit $habit)
    {
        $this->authorize('delete', $habit);

        $habit->delete();

        return redirect()->route('habits.index')->with('status', 'Habit deleted successfully.');
    }

    /**
     * Toggle a habit's completion for a date. When the habit has activities,
     * toggling the habit marks every activity complete/incomplete for that day.
     */
    public function toggleCompletion(Request $request, Habit $habit)
    {
        $this->authorize('toggle', $habit);

        $validated = $request->validate([
            'completed_date' => ['nullable', 'date'],
        ]);

        $date = isset($validated['completed_date'])
            ? Carbon::parse($validated['completed_date'])->toDateString()
            : Carbon::today()->toDateString();

        $activities = $habit->activities()->get();

        if ($activities->isNotEmpty()) {
            // Habit-level toggle cascades across its activities.
            $allDone = $activities->every(fn ($a) => $a->completions()->whereDate('completed_date', $date)->exists());

            foreach ($activities as $activity) {
                $existing = $activity->completions()->whereDate('completed_date', $date)->first();

                if ($allDone) {
                    if ($existing) {
                        $existing->delete();
                    }
                } elseif (! $existing) {
                    $activity->completions()->create([
                        'habit_id' => $habit->id,
                        'user_id' => $request->user()->id,
                        'completed_date' => $date,
                    ]);
                }
            }

            $message = $allDone
                ? 'Habit activities cleared for ' . Carbon::parse($date)->format('M d, Y') . '.'
                : 'Habit activities marked as completed for ' . Carbon::parse($date)->format('M d, Y') . '.';

            return redirect()->back()->with('status', $message);
        }

        $existing = $habit->completions()->whereDate('completed_date', $date)->whereNull('habit_activity_id')->first();

        if ($existing) {
            $existing->delete();
            $message = 'Habit marked as not completed for ' . Carbon::parse($date)->format('M d, Y') . '.';
        } else {
            $habit->completions()->create([
                'user_id' => $request->user()->id,
                'completed_date' => $date,
            ]);
            $message = 'Habit marked as completed for ' . Carbon::parse($date)->format('M d, Y') . '.';
        }

        return redirect()->back()->with('status', $message);
    }

    /**
     * Replace the habit's activity list with the submitted names, keeping
     * existing activities (and their completion history) intact by name.
     *
     * @param  array<int, array<string, mixed>>  $activities
     */
    protected function syncActivities(Habit $habit, array $activities, int $userId): void
    {
        $names = collect($activities)
            ->pluck('name')
            ->filter(fn ($name) => is_string($name) && trim($name) !== '')
            ->map(fn ($name) => trim($name))
            ->unique()
            ->values();

        if ($names->isEmpty()) {
            return;
        }

        $existing = $habit->activities()->pluck('name')->map(fn ($n) => strtolower($n))->all();

        $order = $habit->activities()->max('sort_order') ?? 0;

        foreach ($names as $name) {
            if (in_array(strtolower($name), $existing, true)) {
                continue;
            }

            $order++;
            $habit->activities()->create([
                'user_id' => $userId,
                'name' => $name,
                'sort_order' => $order,
            ]);
        }
    }

    /**
     * Completion progress for a habit within a date range, based on either its
     * activities (per-activity) or habit-level completions.
     *
     * @return array{completed:int,expected:int,percentage:int}
     */
    protected function periodProgress(Habit $habit, Carbon $from, Carbon $to): array
    {
        $days = $from->diffInDays($to) + 1;

        $activities = $habit->activities;

        if ($activities->isNotEmpty()) {
            $expected = $activities->count() * $days;
            $completed = $habit->completions()
                ->whereNotNull('habit_activity_id')
                ->whereBetween('completed_date', [$from->toDateString(), $to->toDateString()])
                ->count();
        } else {
            $expected = $days;
            $completed = $habit->completions()
                ->whereBetween('completed_date', [$from->toDateString(), $to->toDateString()])
                ->count();
        }

        return [
            'completed' => $completed,
            'expected' => $expected,
            'percentage' => $expected > 0 ? (int) min(100, round(($completed / $expected) * 100)) : 0,
        ];
    }
}
