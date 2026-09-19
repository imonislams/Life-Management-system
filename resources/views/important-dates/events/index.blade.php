@php
use App\Models\Event;
@endphp
<x-app-layout>
    <x-slot name="title">Events - Important Dates</x-slot>
    <x-slot name="pageTitle">Events</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Events</h2>
                <p class="card-subtitle">Keep track of important dates, appointments, and occasions.</p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('calendar.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Calendar View</a>
                <a href="{{ route('events.create') }}" class="btn-primary">+ Add New Event</a>
            </div>
        </div>
    </div>

<!-- Summary Cards Grid -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Total Events</div>
            <div class="summary-card-value">{{ $totalEvents }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Today</div>
            <div class="summary-card-value balance-color">{{ $todayEvents }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Upcoming</div>
            <div class="summary-card-value income-color">{{ $upcomingEvents }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Past</div>
            <div class="summary-card-value">{{ $pastEvents }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Cancelled</div>
            <div class="summary-card-value expense-color">{{ $cancelledEvents }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding: 1rem;">
        <form method="GET" action="{{ route('events.index') }}" class="filter-bar" style="margin-bottom: 0;">
            <div class="filter-group">
                <label for="range" class="filter-label">Range</label>
                <select name="range" id="range" class="form-control" onchange="this.form.submit()">
                    <option value="">All Dates</option>
                    <option value="today" {{ request('range') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="upcoming" {{ request('range') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="past" {{ request('range') === 'past' ? 'selected' : '' }}>Past</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="status" class="filter-label">Status</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(Event::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst($statusOption) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="date" class="filter-label">Specific Date</label>
                <input type="date" name="date" id="date" value="{{ request('date') }}" class="form-control">
            </div>

            <div class="filter-group">
                <button type="submit" class="btn-primary btn-sm" style="padding: 0.625rem 0.75rem;">Apply</button>
            </div>

            @if(request()->hasAny(['range', 'status', 'date']))
            <div class="filter-group">
                <a href="{{ route('events.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.75rem;">Reset</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Events Table -->
    <div class="card">
        @if($events->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($events as $event)
                    <tr style="{{ $event->status === 'cancelled' ? 'opacity: 0.6;' : '' }}">
                        <td>
                            <a href="{{ route('events.show', $event) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">
                                {{ $event->title }}
                            </a>
                            @if($event->description)
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                {{ Str::limit($event->description, 80) }}
                            </div>
                            @endif
                        </td>
                        <td style="white-space: nowrap;">{{ user_date($event->event_date) }}</td>
                        <td style="white-space: nowrap;">
                            @if($event->start_time)
                            {{ $event->startTimeLabel() }}
                            @if($event->end_time)
                            – {{ $event->endTimeLabel() }}
                            @endif
                            @else
                            <span style="color: #94a3b8; font-style: italic;">All day</span>
                            @endif
                        </td>
                        <td>{{ $event->location ?: '—' }}</td>
                        <td>
                            @if($event->status === 'completed')
                            <span class="badge badge-income">Completed</span>
                            @elseif($event->status === 'cancelled')
                            <span class="badge badge-expense">Cancelled</span>
                            @else
                            <span class="badge badge-active">Upcoming</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('events.show', $event) }}" class="btn-sm btn-secondary">View</a>
                                <a href="{{ route('events.edit', $event) }}" class="btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('events.destroy', $event) }}"
                                      data-confirm
                                      data-confirm-title="Delete event?"
                                      data-confirm-message="Are you sure you want to delete this event? This action cannot be undone."
                                      data-confirm-action="Delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $events->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No events found</div>
            <p style="font-size: 0.875rem; margin-bottom: 1rem;">Add your first important date to see it here.</p>
            <a href="{{ route('events.create') }}" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">+ Add Event</a>
        </div>
        @endif
    </div>
</x-app-layout>