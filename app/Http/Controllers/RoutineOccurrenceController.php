<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoutineOccurrenceRequest;
use App\Models\RoutineItem;
use App\Models\RoutineOccurrence;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoutineOccurrenceController extends Controller
{
    /**
     * Show a routine template together with its occurrence history.
     * Occurrences for the recent window are materialised on demand so the
     * history reflects the routine's recurrence without overwriting it.
     */
    public function index(Request $request, RoutineItem $routine): View
    {
        $this->authorize('view', $routine);

        $this->syncOccurrences($routine, Carbon::today()->subDays(30), Carbon::today()->addDays(30));

        $occurrences = $routine->occurrences()
            ->orderByDesc('occurrence_date')
            ->paginate(20)
            ->withQueryString();

        $completed = $routine->occurrences()->where('status', 'completed')->count();
        $skipped = $routine->occurrences()->where('status', 'skipped')->count();
        $pending = $routine->occurrences()->where('status', 'pending')->count();
        $total = $completed + $skipped + $pending;

        return view('daily-management.routine.occurrences', [
            'routine' => $routine,
            'occurrences' => $occurrences,
            'completed' => $completed,
            'skipped' => $skipped,
            'pending' => $pending,
            'completionRate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
        ]);
    }

    /**
     * Update the status of a single occurrence.
     */
    public function update(RoutineOccurrenceRequest $request, RoutineOccurrence $occurrence): RedirectResponse
    {
        $this->authorize('update', $occurrence);

        $validated = $request->validated();

        $occurrence->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $occurrence->notes,
            'completed_at' => $validated['status'] === 'completed' ? Carbon::now() : null,
        ]);

        return redirect()->back()->with('status', 'Routine occurrence updated.');
    }

    /**
     * Quickly mark today's occurrence of a routine completed or skipped.
     * Creates the occurrence row if it does not yet exist.
     */
    public function mark(Request $request, RoutineItem $routine): RedirectResponse
    {
        $this->authorize('update', $routine);

        $validated = $request->validate([
            'occurrence_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,completed,skipped'],
        ]);

        $date = isset($validated['occurrence_date'])
            ? Carbon::parse($validated['occurrence_date'])->toDateString()
            : Carbon::today()->toDateString();

        $status = $validated['status'];

        // updateOrCreate keeps exactly one row per routine per day.
        $routine->occurrences()->updateOrCreate(
            ['occurrence_date' => $date],
            [
                'user_id' => $routine->user_id,
                'status' => $status,
                'completed_at' => $status === 'completed' ? Carbon::now() : null,
            ]
        );

        return redirect()->back()->with('status', 'Routine marked as ' . $status . '.');
    }

    /**
     * Materialise occurrence rows for the routine within the given window.
     * Existing rows (and their statuses) are never overwritten.
     */
    protected function syncOccurrences(RoutineItem $routine, Carbon $from, Carbon $to): void
    {
        $existing = $routine->occurrences()
            ->whereBetween('occurrence_date', [$from->toDateString(), $to->toDateString()])
            ->pluck('occurrence_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->all();

        $existing = array_flip($existing);
        $rows = [];
        $now = Carbon::now();

        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->toDateString();

            if (! isset($existing[$key]) && $routine->isDueOn($cursor)) {
                $rows[] = [
                    'user_id' => $routine->user_id,
                    'routine_item_id' => $routine->id,
                    'occurrence_date' => $key,
                    'status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $cursor->addDay();
        }

        if (! empty($rows)) {
            RoutineOccurrence::insert($rows);
        }
    }
}
