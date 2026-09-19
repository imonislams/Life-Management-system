<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Display a monthly calendar of the authenticated user's events.
     *
     * The calendar is built from the existing Event records; no duplicate
     * event storage is created.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Resolve the month being viewed (defaults to the current month).
        $month = (int) $request->query('month', Carbon::today()->month);
        $year = (int) $request->query('year', Carbon::today()->year);

        if ($month < 1 || $month > 12) {
            $month = Carbon::today()->month;
        }

        $current = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $prev = $current->copy()->subMonth();
        $next = $current->copy()->addMonth();

        // Fetch all events for the visible month in a single query.
        $monthEvents = $user->events()
            ->whereYear('event_date', $current->year)
            ->whereMonth('event_date', $current->month)
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->get();

        // Group by Y-m-d for quick lookup when rendering the grid.
        $eventsByDate = $monthEvents->groupBy(function ($event) {
            return $event->event_date->format('Y-m-d');
        });

        // Build the calendar grid starting on the user's configured week start day.
        $weekStart = \App\Support\UserPreference::weekStart();

        $gridStart = $current->copy()->startOfWeek($weekStart);
        $gridEnd = $current->copy()->endOfMonth()->endOfWeek($weekStart);

        $days = [];
        $cursor = $gridStart->copy();

        while ($cursor->lessThanOrEqualTo($gridEnd)) {
            $key = $cursor->format('Y-m-d');
            $days[] = [
                'date' => $cursor->copy(),
                'inMonth' => $cursor->month === $current->month,
                'isToday' => $cursor->isToday(),
                'events' => $eventsByDate->get($key, collect()),
            ];
            $cursor->addDay();
        }

        // Selected date (defaults to today if it is in the current month,
        // otherwise the first day of the displayed month).
        $selectedDateParam = $request->query('selected_date');
        if ($selectedDateParam) {
            $selectedDate = Carbon::parse($selectedDateParam);
        } else {
            $selectedDate = Carbon::today()->month === $current->month
                && Carbon::today()->year === $current->year
                ? Carbon::today()
                : $current->copy();
        }

        $selectedEvents = $user->events()
            ->whereDate('event_date', $selectedDate)
            ->orderBy('start_time')
            ->get();

        $monthEventCount = $monthEvents->count();

        // Weekday header labels follow the configured week start day.
        $allLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $weekdayLabels = [];
        for ($i = 0; $i < 7; $i++) {
            $weekdayLabels[] = $allLabels[($weekStart + $i) % 7];
        }

        return view('important-dates.calendar', compact(
            'current',
            'prev',
            'next',
            'days',
            'selectedDate',
            'selectedEvents',
            'monthEventCount',
            'weekdayLabels'
        ));
    }
}
