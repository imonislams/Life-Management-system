@php
use Carbon\Carbon;
@endphp
<x-app-layout>
    <x-slot name="title">{{ $habit->title }} - Habit Details</x-slot>
    <x-slot name="pageTitle">Habit Details</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">{{ $habit->title }}</h2>
                <p class="card-subtitle">
                    {{ ucfirst($habit->frequency) }} habit
                    @if($habit->start_date)
                    · started {{ user_date($habit->start_date) }}
                    @endif
                </p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('habits.edit', $habit) }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Edit</a>
                <a href="{{ route('habits.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Habits</a>
            </div>
        </div>

        @if($habit->description)
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">{{ $habit->description }}</p>
        @endif
    </div>

<!-- Multiple Activities -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Activities</h2>
                <p class="card-subtitle">Track each activity of this habit independently. Example: Fajr, Dhuhr, Asr…</p>
            </div>
        </div>

        @if($activities->count() > 0)
            <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                @foreach($activities as $activity)
                    @php $activityDone = $activity->isCompletedOn($today); @endphp
                    <div class="summary-card" style="text-align:left;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:0.5rem;">
                            <div style="font-weight:600;">{{ $activity->name }}</div>
                            <form method="POST" action="{{ route('habit-activities.toggle', $activity) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="completed_date" value="{{ $today->toDateString() }}">
                                <input type="checkbox" onchange="this.form.submit()" {{ $activityDone ? 'checked' : '' }} style="width:18px; height:18px; cursor:pointer;" title="Mark {{ $activity->name }} for today">
                            </form>
                        </div>
                        <div style="font-size:0.75rem; color:#94a3b8; margin-top:0.25rem;">
                            {{ $activityDone ? 'Completed today' : 'Pending today' }} · {{ $activity->completions->count() }} total
                        </div>
                        <div class="action-buttons" style="margin-top:0.5rem;">
                            <form method="POST" action="{{ route('habit-activities.destroy', $activity) }}"
                                  data-confirm
                                  data-confirm-title="Remove activity?"
                                  data-confirm-message="Are you sure you want to remove this activity? This action cannot be undone."
                                  data-confirm-action="Remove">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger-sm">Remove</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state" style="padding:1rem 0;">
                <div class="empty-state-title">No activities yet</div>
                <p style="font-size:0.875rem;">Add activities below to track sub-parts of this habit (e.g. five daily prayers).</p>
            </div>
        @endif
        <form method="POST" action="{{ route('habit-activities.store', $habit) }}" style="display:flex; gap:0.5rem; align-items:flex-end; margin-top:1rem; flex-wrap:wrap;">
            @csrf
            <div class="form-group" style="flex:1; min-width:200px; margin-bottom:0;">
                <label for="activity_name" class="form-label">New Activity</label>
                <input id="activity_name" type="text" name="name" class="form-control" placeholder="e.g. Fajr" required>
            </div>
            <button type="submit" class="btn-primary btn-sm" style="padding:0.625rem 1rem;">Add Activity</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Status</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                @if($habit->status === 'active')
                <span class="badge badge-income">Active</span>
                @elseif($habit->status === 'paused')
                <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Paused</span>
                @else
                <span class="badge badge-inactive">Completed</span>
                @endif
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Frequency</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ ucfirst($habit->frequency) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Total Completions</div>
            <div class="summary-card-value income-color">{{ $totalCompletions }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Days Tracked</div>
            <div class="summary-card-value">{{ $daysTracked }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Completion Rate</div>
            <div class="summary-card-value balance-color">{{ $progressPercentage }}%</div>
        </div>
    </div>

    <!-- Daily / Weekly / Monthly Progress -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Daily Progress</div>
            <div class="summary-card-value">{{ $dailyProgress['percentage'] }}%</div>
            <div style="font-size:0.75rem; color:#94a3b8;">{{ $dailyProgress['completed'] }}/{{ $dailyProgress['expected'] }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Weekly Progress</div>
            <div class="summary-card-value">{{ $weeklyProgress['percentage'] }}%</div>
            <div style="font-size:0.75rem; color:#94a3b8;">{{ $weeklyProgress['completed'] }}/{{ $weeklyProgress['expected'] }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Monthly Progress</div>
            <div class="summary-card-value">{{ $monthlyProgress['percentage'] }}%</div>
            <div style="font-size:0.75rem; color:#94a3b8;">{{ $monthlyProgress['completed'] }}/{{ $monthlyProgress['expected'] }}</div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card">
        <div class="card-header-flex" style="margin-bottom: 0.5rem;">
            <div>
                <h3 class="card-title">Overall Completion Progress</h3>
                <p class="card-subtitle">{{ $totalCompletions }} of {{ $daysTracked }} tracked days completed.</p>
            </div>

            <!-- Date selector for marking completion -->
            <form method="POST" action="{{ route('habits.toggle', $habit) }}" class="filter-group" style="flex-direction: row; align-items: center; gap: 0.5rem;">
                @csrf
                @method('PATCH')
                <label for="completed_date" class="filter-label" style="margin: 0; white-space: nowrap;">Mark for:</label>
                <input
                    id="completed_date"
                    type="date"
                    name="completed_date"
                    value="{{ $today->toDateString() }}"
                    class="form-control"
                    style="width: auto; padding: 0.375rem 0.75rem;">
                <button type="submit" class="btn-primary btn-sm" style="padding: 0.5rem 0.875rem;">Toggle</button>
            </form>
        </div>

        <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 10px; overflow: hidden; margin-top: 0.5rem;">
            <div style="width: {{ $progressPercentage }}%; background-color: var(--primary-color); height: 100%;"></div>
        </div>
        <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem;">
            Today ({{ user_date($today) }}) is
            <strong>{{ $completedToday ? 'marked as completed' : 'still pending' }}</strong>.
        </p>
    </div>

    <!-- Completion History -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Completion History</h2>
                <p class="card-subtitle">Every date this habit has been completed.</p>
            </div>
        </div>

        @if($completions->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Activity</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($completions as $completion)
                    <tr>
                        <td style="font-weight: 500;">{{ user_date($completion->completed_date) }}</td>
                        <td>{{ $completion->completed_date->format('l') }}</td>
                        <td>
                            @if($completion->activity)
                                <span class="badge badge-active">{{ $completion->activity->name }}</span>
                            @else
                                <span style="color:#94a3b8;">Habit-level</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                @if($completion->activity)
                                    <form method="POST" action="{{ route('habit-activities.toggle', $completion->activity) }}"
                                          data-confirm
                                          data-confirm-title="Remove completion record?"
                                          data-confirm-message="Are you sure you want to remove this completion record? This action cannot be undone."
                                          data-confirm-action="Remove">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="completed_date" value="{{ $completion->completed_date->format('Y-m-d') }}">
                                        <button type="submit" class="btn-sm btn-danger-sm">Remove</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('habits.toggle', $habit) }}"
                                          data-confirm
                                          data-confirm-title="Remove completion record?"
                                          data-confirm-message="Are you sure you want to remove this completion record? This action cannot be undone."
                                          data-confirm-action="Remove">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="completed_date" value="{{ $completion->completed_date->format('Y-m-d') }}">
                                        <button type="submit" class="btn-sm btn-danger-sm">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $completions->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No completions recorded yet</div>
            <p style="font-size: 0.875rem;">Use the toggle above to mark this habit as completed for a date.</p>
        </div>
        @endif
    </div>
</x-app-layout>