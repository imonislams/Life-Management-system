<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoutineItemRequest;
use App\Models\RoutineItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineItemController extends Controller
{
    /**
     * Display a listing of routine items for the authenticated user,
     * sorted by start time.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->routines();

        if ($request->filled('status') && in_array($request->status, RoutineItem::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('recurrence_type') && in_array($request->recurrence_type, RoutineItem::RECURRENCE_TYPES, true)) {
            $query->where('recurrence_type', $request->recurrence_type);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $routineItems = $query->orderBy('start_time', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Today's occurrences for the listed routines, keyed by routine id.
        $today = \Carbon\Carbon::today();
        $todayOccurrences = $user->routineOccurrences()
            ->whereDate('occurrence_date', $today)
            ->get()
            ->keyBy('routine_item_id');

        $totalItems = $user->routines()->count();
        $activeItems = $user->routines()->where('status', 'active')->count();
        $pausedItems = $user->routines()->where('status', 'paused')->count();
        $completedItems = $user->routines()->where('status', 'completed')->count();
        $skippedItems = $user->routines()->where('status', 'skipped')->count();

        return view('daily-management.routine.index', compact(
            'routineItems',
            'todayOccurrences',
            'totalItems',
            'activeItems',
            'pausedItems',
            'completedItems',
            'skippedItems',
            'today'
        ));
    }

    /**
     * Show the form for creating a new routine item.
     */
    public function create()
    {
        return view('daily-management.routine.create');
    }

    /**
     * Store a newly created routine item in storage.
     */
    public function store(RoutineItemRequest $request)
    {
        // Ownership is always taken from the authenticated user.
        $request->user()->routines()->create($request->payload());

        return redirect()->route('routine.index')->with('status', 'Routine item created successfully.');
    }

    /**
     * Display the specified routine item.
     */
    public function show(RoutineItem $routine)
    {
        $this->authorize('view', $routine);

        return view('daily-management.routine.show', ['routineItem' => $routine]);
    }

    /**
     * Show the form for editing the specified routine item.
     */
    public function edit(RoutineItem $routine)
    {
        $this->authorize('update', $routine);

        return view('daily-management.routine.edit', ['routineItem' => $routine]);
    }

    /**
     * Update the specified routine item in storage.
     */
    public function update(RoutineItemRequest $request, RoutineItem $routine)
    {
        $this->authorize('update', $routine);

        $routine->update($request->payload());

        return redirect()->route('routine.index')->with('status', 'Routine item updated successfully.');
    }

    /**
     * Remove the specified routine item from storage.
     */
    public function destroy(RoutineItem $routine)
    {
        $this->authorize('delete', $routine);

        $routine->delete();

        return redirect()->route('routine.index')->with('status', 'Routine item deleted successfully.');
    }
}
