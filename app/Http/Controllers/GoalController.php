<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalRequest;
use App\Models\Goal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    /**
     * Display a listing of goals for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->goals();

        // Status filter (supports the special "overdue" filter).
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } elseif (in_array($request->status, Goal::STATUSES, true)) {
                $query->where('status', $request->status);
            }
        }

        // Priority filter.
        if ($request->filled('priority') && in_array($request->priority, Goal::PRIORITIES, true)) {
            $query->where('priority', $request->priority);
        }

        $goals = $query->orderByRaw("CASE WHEN status = 'completed' THEN 1 ELSE 0 END")
            ->orderBy('target_date', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $totalGoals = $user->goals()->count();
        $activeGoals = $user->goals()->active()->count();
        $completedGoals = $user->goals()->where('status', 'completed')->count();
        $pausedGoals = $user->goals()->where('status', 'paused')->count();
        $overdueGoals = $user->goals()->overdue()->count();
        // Overall progress averages each goal's real, derived progress percentage
        // (measurable goals use current/target; qualitative use their stored %).
        $percentages = $user->goals()->get()
            ->map(fn ($g) => $g->progressPercentage())
            ->filter(fn ($p) => $p !== null);
        $overallProgress = $percentages->isNotEmpty()
            ? (int) round($percentages->avg())
            : 0;

        return view('personal-growth.goals.index', compact(
            'goals',
            'totalGoals',
            'activeGoals',
            'completedGoals',
            'pausedGoals',
            'overdueGoals',
            'overallProgress'
        ));
    }

    /**
     * Show the form for creating a new goal.
     */
    public function create()
    {
        return view('personal-growth.goals.create');
    }

    /**
     * Store a newly created goal in storage.
     */
    public function store(GoalRequest $request)
    {
        $validated = $request->payload();

        // Ownership always comes from the authenticated user.
        $validated['completed_at'] = $validated['status'] === 'completed'
            ? Carbon::now()
            : null;

        // Measurable goals start from zero current progress; the value is built
        // up from real progress updates rather than a hand-typed percentage.
        if (($validated['progress_type'] ?? 'qualitative') === 'measurable') {
            $validated['current_amount'] = 0;
        }

        $request->user()->goals()->create($validated);

        return redirect()->route('goals.index')->with('status', 'Goal created successfully.');
    }

    /**
     * Display the specified goal.
     */
    public function show(Goal $goal)
    {
        $this->authorize('view', $goal);

        $goal->load(['progressUpdates', 'dailyActivities']);

        $progressUpdates = $goal->progressUpdates()->paginate(20)->withQueryString();

        $totalMinutes = (int) $goal->progressUpdates()->sum('time_spent_minutes');

        return view('personal-growth.goals.show', [
            'goal' => $goal,
            'progressUpdates' => $progressUpdates,
            'totalMinutes' => $totalMinutes,
            'progressPercentage' => $goal->progressPercentage(),
            'durationDays' => $goal->durationInDays(),
            'daysElapsed' => $goal->daysElapsed(),
            'daysRemaining' => $goal->daysRemaining(),
            'relatedActivities' => $goal->dailyActivities()->with('goal')->take(10)->get(),
        ]);
    }

    /**
     * Show the form for editing the specified goal.
     */
    public function edit(Goal $goal)
    {
        $this->authorize('update', $goal);

        return view('personal-growth.goals.edit', compact('goal'));
    }

    /**
     * Update the specified goal in storage.
     */
    public function update(GoalRequest $request, Goal $goal)
    {
        $this->authorize('update', $goal);

        $validated = $request->payload();

        // Keep completed_at in sync with the status transitions.
        if ($validated['status'] === 'completed' && $goal->status !== 'completed') {
            $validated['completed_at'] = Carbon::now();
        } elseif ($validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $goal->update($validated);

        return redirect()->route('goals.index')->with('status', 'Goal updated successfully.');
    }

    /**
     * Quick update of a goal's progress percentage.
     */
    public function updateProgress(Request $request, Goal $goal)
    {
        $this->authorize('updateProgress', $goal);

        $validated = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $progress = (int) $validated['progress'];

        $attributes = ['progress' => $progress];

        // Completing progress to 100% marks the goal completed automatically.
        if ($progress === 100 && $goal->status !== 'completed') {
            $attributes['status'] = 'completed';
            $attributes['completed_at'] = Carbon::now();
        } elseif ($progress < 100 && $goal->status === 'completed') {
            $attributes['status'] = 'in_progress';
            $attributes['completed_at'] = null;
        }

        $goal->update($attributes);

        return redirect()->back()->with('status', 'Goal progress updated to ' . $progress . '%.');
    }

    /**
     * Remove the specified goal from storage.
     */
    public function destroy(Goal $goal)
    {
        $this->authorize('delete', $goal);

        $goal->delete();

        return redirect()->route('goals.index')->with('status', 'Goal deleted successfully.');
    }
}
