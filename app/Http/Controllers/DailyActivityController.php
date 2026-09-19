<?php

namespace App\Http\Controllers;

use App\Http\Requests\DailyActivityRequest;
use App\Models\DailyActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyActivityController extends Controller
{
    /**
     * Personal daily journal of what the user actually did.
     *
     * Supports a list view with search / date / category / goal filters and
     * daily, weekly and monthly aggregations.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = $user->dailyActivities()->with('goal');

        // Search across title and description.
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', '%' . $term . '%')
                    ->orWhere('description', 'like', '%' . $term . '%');
            });
        }

        // Category filter.
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Related goal filter.
        if ($request->filled('goal_id')) {
            $query->where('goal_id', (int) $request->input('goal_id'));
        }

        // Date filters: exact date, or a from/to range.
        if ($request->filled('date')) {
            $query->whereDate('activity_date', $request->input('date'));
        }
        if ($request->filled('from')) {
            $query->whereDate('activity_date', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('activity_date', '<=', $request->input('to'));
        }

        $activities = $query->orderByDesc('activity_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $todayActivities = $user->dailyActivities()->whereDate('activity_date', $today)->get();
        $weekActivities = $user->dailyActivities()
            ->whereBetween('activity_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();
        $monthActivities = $user->dailyActivities()
            ->whereBetween('activity_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get();

        // Categories actually used by the user (for the filter dropdown).
        $categories = $user->dailyActivities()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('daily-activities.index', [
            'activities' => $activities,
            'goals' => $user->goals()->orderBy('title')->get(['id', 'title']),
            'categories' => $categories,
            'today' => $today,
            // Summary metrics, all computed from real rows.
            'todayCount' => $todayActivities->count(),
            'todayMinutes' => (int) $todayActivities->sum(fn($a) => (int) $a->resolveDuration()),
            'weekCount' => $weekActivities->count(),
            'weekMinutes' => (int) $weekActivities->sum(fn($a) => (int) $a->resolveDuration()),
            'monthCount' => $monthActivities->count(),
            'monthMinutes' => (int) $monthActivities->sum(fn($a) => (int) $a->resolveDuration()),
            'totalCount' => $user->dailyActivities()->count(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('daily-activities.create', [
            'goals' => $request->user()->goals()->orderBy('title')->get(['id', 'title']),
            'presetDate' => $request->query('date'),
        ]);
    }

    public function store(DailyActivityRequest $request)
    {
        $request->user()->dailyActivities()->create($request->payload());

        return redirect()->route('daily-activities.index')
            ->with('status', 'Activity recorded successfully.');
    }

    public function show(DailyActivity $dailyActivity): View
    {
        $this->authorize('view', $dailyActivity);

        $dailyActivity->load('goal');

        return view('daily-activities.show', ['activity' => $dailyActivity]);
    }

    public function edit(Request $request, DailyActivity $dailyActivity): View
    {
        $this->authorize('update', $dailyActivity);

        return view('daily-activities.edit', [
            'activity' => $dailyActivity,
            'goals' => $request->user()->goals()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(DailyActivityRequest $request, DailyActivity $dailyActivity)
    {
        $this->authorize('update', $dailyActivity);

        $dailyActivity->update($request->payload());

        return redirect()->route('daily-activities.index')
            ->with('status', 'Activity updated successfully.');
    }

    public function destroy(DailyActivity $dailyActivity)
    {
        $this->authorize('delete', $dailyActivity);

        $dailyActivity->delete();

        return redirect()->route('daily-activities.index')
            ->with('status', 'Activity deleted successfully.');
    }
}
