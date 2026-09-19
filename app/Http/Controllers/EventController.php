<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    /**
     * Display a listing of events for the authenticated user,
     * with optional date-range filtering and status filtering.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->events();

        if ($request->filled('status') && in_array($request->status, Event::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        // Range filter: today | upcoming | past
        if ($request->filled('range')) {
            if ($request->range === 'today') {
                $query->whereDate('event_date', Carbon::today());
            } elseif ($request->range === 'upcoming') {
                $query->upcoming();
            } elseif ($request->range === 'past') {
                $query->past();
            }
        }

        // Specific date filter.
        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->date);
        }

        $events = $query->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        $totalEvents = $user->events()->count();
        $upcomingEvents = $user->events()->upcoming()->count();
        $pastEvents = $user->events()->past()->count();
        $cancelledEvents = $user->events()->where('status', 'cancelled')->count();
        $todayEvents = $user->events()->whereDate('event_date', Carbon::today())->count();

        return view('important-dates.events.index', compact(
            'events',
            'totalEvents',
            'upcomingEvents',
            'pastEvents',
            'cancelledEvents',
            'todayEvents'
        ));
    }

    /**
     * Show the form for creating a new event.
     */
    public function create(Request $request)
    {
        // Allow pre-selecting a date when redirected from the calendar.
        $presetDate = $request->query('date');

        return view('important-dates.events.create', compact('presetDate'));
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(EventRequest $request)
    {
        // Ownership always comes from the authenticated user.
        $request->user()->events()->create($request->validated());

        return redirect()->route('events.index')->with('status', 'Event created successfully.');
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event)
    {
        $this->authorize('view', $event);

        return view('important-dates.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event.
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);

        return view('important-dates.events.edit', compact('event'));
    }

    /**
     * Update the specified event in storage.
     */
    public function update(EventRequest $request, Event $event)
    {
        $this->authorize('update', $event);

        $event->update($request->validated());

        return redirect()->route('events.index')->with('status', 'Event updated successfully.');
    }

    /**
     * Remove the specified event from storage.
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('events.index')->with('status', 'Event deleted successfully.');
    }
}
