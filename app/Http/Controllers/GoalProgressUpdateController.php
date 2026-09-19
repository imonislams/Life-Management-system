<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalProgressUpdateRequest;
use App\Models\Goal;
use App\Models\GoalProgressUpdate;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GoalProgressUpdateController extends Controller
{
    /**
     * Record a daily progress update toward a goal.
     *
     * Every update is preserved as history. For measurable goals the update's
     * progress_value is added to the goal's current_amount (never fabricated);
     * once current >= target the goal is marked completed.
     */
    public function store(GoalProgressUpdateRequest $request, Goal $goal): RedirectResponse
    {
        $this->authorize('update', $goal);

        $data = $request->validated();

        DB::transaction(function () use ($goal, $data, $request) {
            $goal->progressUpdates()->create([
                'user_id' => $request->user()->id,
                'date' => $data['date'],
                'description' => $data['description'],
                'progress_value' => $data['progress_value'] ?? null,
                'time_spent_minutes' => $data['time_spent_minutes'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recalculateGoalProgress($goal);
        });

        return redirect()->route('goals.show', $goal)
            ->with('status', "Today's progress recorded successfully.");
    }

    public function update(GoalProgressUpdateRequest $request, GoalProgressUpdate $progressUpdate): RedirectResponse
    {
        $this->authorize('update', $progressUpdate);

        $data = $request->validated();
        $goal = $progressUpdate->goal()->firstOrFail();

        DB::transaction(function () use ($progressUpdate, $data, $goal) {
            $progressUpdate->update([
                'date' => $data['date'],
                'description' => $data['description'],
                'progress_value' => $data['progress_value'] ?? null,
                'time_spent_minutes' => $data['time_spent_minutes'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->recalculateGoalProgress($goal);
        });

        return redirect()->route('goals.show', $goal)
            ->with('status', 'Progress update saved successfully.');
    }

    public function destroy(GoalProgressUpdate $progressUpdate): RedirectResponse
    {
        $this->authorize('delete', $progressUpdate);

        $goal = $progressUpdate->goal()->firstOrFail();

        DB::transaction(function () use ($progressUpdate, $goal) {
            $progressUpdate->delete();
            $this->recalculateGoalProgress($goal);
        });

        return redirect()->route('goals.show', $goal)
            ->with('status', 'Progress update deleted successfully.');
    }

    /**
     * Recompute a goal's stored progress from its real progress-update history.
     *
     * - Measurable goals: current_amount is the sum of logged progress values.
     * - Qualitative goals: progress is the number of logged updates relative to
     *   the days elapsed in the goal window (a real, derived measure).
     */
    protected function recalculateGoalProgress(Goal $goal): void
    {
        $goal->refresh();

        $updates = $goal->progressUpdates()->get();

        if ($goal->progress_type === 'measurable' && (float) $goal->target_amount > 0) {
            $current = (float) $updates->sum(fn($u) => (float) ($u->progress_value ?? 0));

            $attributes = ['current_amount' => $current];

            if ($current >= (float) $goal->target_amount && $goal->status !== 'completed') {
                $attributes['status'] = 'completed';
                $attributes['progress'] = 100;
                $attributes['completed_at'] = Carbon::now();
            } elseif ($current < (float) $goal->target_amount && $goal->status === 'completed') {
                $attributes['status'] = 'in_progress';
                $attributes['completed_at'] = null;
            }

            $goal->forceFill($attributes)->save();

            return;
        }

        // Qualitative goal: derive a real percentage from logged updates over the
        // elapsed days of the goal window (falls back to count when no window).
        $updateCount = $updates->count();
        $windowDays = $goal->durationInDays();
        $progress = $goal->progress;

        if ($windowDays && $windowDays > 0) {
            $progress = (int) min(100, round(($updateCount / $windowDays) * 100));
        } elseif ($updateCount > 0) {
            $progress = min(100, $updateCount * 10);
        }

        $attributes = ['progress' => $progress];

        if ($progress >= 100 && $goal->status !== 'completed') {
            $attributes['status'] = 'completed';
            $attributes['completed_at'] = Carbon::now();
        }

        $goal->forceFill($attributes)->save();
    }
}
