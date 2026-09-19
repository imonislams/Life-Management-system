<?php

namespace App\Http\Controllers;

use App\Http\Requests\HabitActivityRequest;
use App\Models\Habit;
use App\Models\HabitActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HabitActivityController extends Controller
{
    /**
     * Store a new activity under a habit.
     */
    public function store(HabitActivityRequest $request, Habit $habit): RedirectResponse
    {
        $this->authorize('update', $habit);

        $data = $request->validated();
        $data['sort_order'] = $data['sort_order'] ?? ($habit->activities()->max('sort_order') + 1);

        $habit->activities()->create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'],
        ]);

        return redirect()->route('habits.show', $habit)->with('status', 'Activity added successfully.');
    }

    public function update(HabitActivityRequest $request, HabitActivity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $activity->update($request->validated());

        return redirect()->route('habits.show', $activity->habit_id)->with('status', 'Activity updated successfully.');
    }

    public function destroy(HabitActivity $activity): RedirectResponse
    {
        $this->authorize('delete', $activity);

        $habitId = $activity->habit_id;
        $activity->delete();

        return redirect()->route('habits.show', $habitId)->with('status', 'Activity removed successfully.');
    }

    /**
     * Toggle completion of a specific habit activity on a given date.
     * Duplicate records for the same activity + date are prevented.
     */
    public function toggleCompletion(Request $request, HabitActivity $activity): RedirectResponse
    {
        $this->authorize('update', $activity);

        $validated = $request->validate([
            'completed_date' => ['nullable', 'date'],
        ]);

        $date = isset($validated['completed_date'])
            ? \Carbon\Carbon::parse($validated['completed_date'])->toDateString()
            : \Carbon\Carbon::today()->toDateString();

        $existing = $activity->completions()->whereDate('completed_date', $date)->first();

        if ($existing) {
            $existing->delete();
            $message = $activity->name . ' marked as not completed for ' . \Carbon\Carbon::parse($date)->format('M d, Y') . '.';
        } else {
            $activity->completions()->create([
                'habit_id' => $activity->habit_id,
                'user_id' => $request->user()->id,
                'completed_date' => $date,
            ]);
            $message = $activity->name . ' marked as completed for ' . \Carbon\Carbon::parse($date)->format('M d, Y') . '.';
        }

        return redirect()->back()->with('status', $message);
    }
}
