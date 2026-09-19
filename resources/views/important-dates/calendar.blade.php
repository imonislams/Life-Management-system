@php
    use Carbon\Carbon;
@endphp
<x-app-layout>
    <x-slot name="title">Calendar - Important Dates</x-slot>
    <x-slot name="pageTitle">Calendar</x-slot>

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Calendar</h2>
                <p class="card-subtitle">Your events for {{ $current->format('F Y') }}. Select a date to view its events.</p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('events.create', ['date' => $selectedDate->format('Y-m-d')]) }}" class="btn-primary">+ Create Event</a>
                <a href="{{ route('events.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">All Events</a>
            </div>
        </div>
    </div>

<!-- Month Navigation -->
    <div class="card">
        <div class="card-header-flex" style="margin-bottom: 0.75rem;">
            <div class="action-buttons">
                <a href="{{ route('calendar.index', ['month' => $prev->month, 'year' => $prev->year]) }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">← Previous</a>
                <a href="{{ route('calendar.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Today</a>
                <a href="{{ route('calendar.index', ['month' => $next->month, 'year' => $next->year]) }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.875rem;">Next →</a>
            </div>

            <div style="font-weight: 700; font-size: 1.125rem;">
                {{ $current->format('F Y') }}
                <span style="font-size: 0.8rem; font-weight: 500; color: var(--text-muted); margin-left: 0.5rem;">
                    {{ $monthEventCount }} event{{ $monthEventCount === 1 ? '' : 's' }}
                </span>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div style="overflow-x: auto;">
            <div style="min-width: 640px;">
                <!-- Weekday header -->
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; margin-bottom: 4px;">
                    @foreach($weekdayLabels as $label)
                    <div style="text-align: center; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); padding: 0.35rem 0;">
                        {{ $label }}
                    </div>
                    @endforeach
                </div>

                <!-- Day cells -->
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
                    @foreach($days as $day)
                    @php
                    $isSelected = $day['date']->isSameDay($selectedDate);
                    $hasEvents = $day['events']->count() > 0;
                    $cellBackground = $isSelected ? '#eff6ff' : ($day['inMonth'] ? '#ffffff' : '#f8fafc');
                    $borderColor = $isSelected ? 'var(--primary-color)' : 'var(--border-color)';
                    @endphp
                    <a
                        href="{{ route('calendar.index', ['month' => $current->month, 'year' => $current->year, 'selected_date' => $day['date']->format('Y-m-d')]) }}"
                        style="
                                display: block;
                                text-decoration: none;
                                color: inherit;
                                border: 1px solid {{ $borderColor }};
                                border-radius: 0.375rem;
                                padding: 0.5rem;
                                min-height: 84px;
                                background: {{ $cellBackground }};
                                {{ $day['inMonth'] ? '' : 'opacity: 0.55;' }}
                            ">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                            <span style="font-size: 0.8rem; font-weight: {{ $day['isToday'] ? '700' : '500' }}; {{ $day['isToday'] ? 'color: var(--primary-color);' : '' }}">
                                {{ $day['date']->day }}
                            </span>
                            @if($day['isToday'])
                            <span style="font-size: 0.6rem; font-weight: 700; color: var(--primary-color); text-transform: uppercase;">Today</span>
                            @endif
                        </div>

                        @if($hasEvents)
                        @foreach($day['events']->take(2) as $dayEvent)
                        <div style="font-size: 0.65rem; background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; border-radius: 0.25rem; padding: 0.1rem 0.25rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            {{ $dayEvent->title }}
                        </div>
                        @endforeach
                        @if($day['events']->count() > 2)
                        <div style="font-size: 0.6rem; color: var(--text-muted);">
                            +{{ $day['events']->count() - 2 }} more
                        </div>
                        @endif
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Selected Date Events -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Events on {{ $selectedDate->format('l, M d, Y') }}</h3>
                <p class="card-subtitle">{{ $selectedEvents->count() }} event{{ $selectedEvents->count() === 1 ? '' : 's' }} scheduled.</p>
            </div>
            <a href="{{ route('events.create', ['date' => $selectedDate->format('Y-m-d')]) }}" class="btn-primary btn-sm" style="padding: 0.5rem 0.875rem;">+ Add Event</a>
        </div>

        @if($selectedEvents->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($selectedEvents as $selectedEvent)
                    <tr>
                        <td style="font-weight: 600;">{{ $selectedEvent->title }}</td>
                        <td style="white-space: nowrap;">
                            {{ $selectedEvent->start_time ? $selectedEvent->startTimeLabel() : 'All day' }}
                        </td>
                        <td>{{ $selectedEvent->location ?: '—' }}</td>
                        <td>
                            @if($selectedEvent->status === 'completed')
                            <span class="badge badge-income">Completed</span>
                            @elseif($selectedEvent->status === 'cancelled')
                            <span class="badge badge-expense">Cancelled</span>
                            @else
                            <span class="badge badge-active">Upcoming</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('events.show', $selectedEvent) }}" class="btn-sm btn-secondary">View</a>
                                <a href="{{ route('events.edit', $selectedEvent) }}" class="btn-sm btn-secondary">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No events on this date</div>
            <p style="font-size: 0.875rem; margin-bottom: 1rem;">Select another date or create a new event for this day.</p>
            <a href="{{ route('events.create', ['date' => $selectedDate->format('Y-m-d')]) }}" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">+ Add Event</a>
        </div>
        @endif
    </div>
</x-app-layout>