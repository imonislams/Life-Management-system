@php
use App\Models\Habit;
@endphp
<x-app-layout>
    <x-slot name="title">Habits - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Habits</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Daily Habits</h2>
                <p class="card-subtitle">Build consistency by creating habits and tracking daily completions.</p>
            </div>
            <a href="{{ route('habits.create') }}" class="btn-primary">+ Add New Habit</a>
        </div>
    </div>

<!-- Summary Cards Grid -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Total Habits</div>
            <div class="summary-card-value">{{ $totalHabits }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Active Habits</div>
            <div class="summary-card-value balance-color">{{ $activeHabits }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Completed Today</div>
            <div class="summary-card-value income-color">{{ $todayCompletedCount }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Pending Today</div>
            <div class="summary-card-value" style="color: #ea580c;">{{ $todayPendingCount }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Today's Progress</div>
            <div class="summary-card-value">{{ $todayCompletionPercentage }}%</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding: 1rem;">
        <form method="GET" action="{{ route('habits.index') }}" class="filter-bar" style="margin-bottom: 0;">
            <div class="filter-group">
                <label for="status" class="filter-label">Status</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(Habit::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst($statusOption) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="frequency" class="filter-label">Frequency</label>
                <select name="frequency" id="frequency" class="form-control" onchange="this.form.submit()">
                    <option value="">All Frequencies</option>
                    @foreach(Habit::FREQUENCIES as $freqOption)
                    <option value="{{ $freqOption }}" {{ request('frequency') === $freqOption ? 'selected' : '' }}>
                        {{ ucfirst($freqOption) }}
                    </option>
                    @endforeach
                </select>
            </div>

            @if(request()->hasAny(['status', 'frequency']))
            <div class="filter-group">
                <a href="{{ route('habits.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.75rem;">Reset Filters</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Habits Table -->
    <div class="card">
        @if($habits->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">Today</th>
                        <th>Habit</th>
                        <th>Frequency</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>Completions</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($habits as $habit)
                    @php $isDoneToday = in_array($habit->id, $completedTodayIds, true); @endphp
                    <tr style="{{ $isDoneToday ? 'background-color: #f8fafc;' : '' }}">
                        <td>
                            <form method="POST" action="{{ route('habits.toggle', $habit) }}" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="completed_date" value="{{ $today->toDateString() }}">
                                <input
                                    type="checkbox"
                                    onchange="this.form.submit()"
                                    {{ $isDoneToday ? 'checked' : '' }}
                                    style="width: 18px; height: 18px; cursor: pointer;"
                                    title="Mark completed for {{ user_date($today) }}">
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('habits.show', $habit) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">
                                {{ $habit->title }}
                            </a>
                            @if($habit->description)
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                {{ Str::limit($habit->description, 80) }}
                            </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-active">{{ ucfirst($habit->frequency) }}</span>
                        </td>
                        <td>
                            @if($habit->status === 'active')
                            <span class="badge badge-income">Active</span>
                            @elseif($habit->status === 'paused')
                            <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Paused</span>
                            @else
                            <span class="badge badge-inactive">Completed</span>
                            @endif
                        </td>
                        <td>
                            @if($habit->start_date)
                            <span style="font-size: 0.85rem;">{{ user_date($habit->start_date) }}</span>
                            @else
                            <span style="color: #94a3b8; font-style: italic;">Not set</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-weight: 600;">{{ $habit->completions_count }}</span>
                            <span style="font-size: 0.75rem; color: #64748b;">total</span>
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('habits.show', $habit) }}" class="btn-sm btn-secondary">View</a>
                                <a href="{{ route('habits.edit', $habit) }}" class="btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('habits.destroy', $habit) }}"
                                      data-confirm
                                      data-confirm-title="Delete habit?"
                                      data-confirm-message="Are you sure you want to delete this habit? This action cannot be undone."
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
            {{ $habits->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No habits found</div>
            <p style="font-size: 0.875rem; margin-bottom: 1rem;">Start building better routines by adding your first habit.</p>
            <a href="{{ route('habits.create') }}" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">+ Add Habit</a>
        </div>
        @endif
    </div>
</x-app-layout>