@php
/** Shared duration formatter for the summary cards. */
$fmt = function (?int $minutes): string {
$minutes = (int) $minutes;
if ($minutes <= 0) return '0m' ;
    $h=intdiv($minutes, 60); $m=$minutes % 60;
    if ($h> 0 && $m > 0) return $h . 'h ' . $m . 'm';
    return $h > 0 ? $h . 'h' : $m . 'm';
    };
    @endphp
    <x-app-layout>
        <x-slot name="title">Daily Activities - Personal Life Management System</x-slot>
        <x-slot name="pageTitle">Daily Activities</x-slot>

            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif

        <div class="card">
            <div class="card-header-flex">
                <div>
                    <h2 class="card-title">Daily Activities</h2>
                    <p class="card-subtitle">A personal journal of what you actually did each day.</p>
                </div>
                <a href="{{ route('daily-activities.create') }}" class="btn-primary">+ Add Activity</a>
            </div>
        </div>

@if($errors->any())
        <div class="alert-danger">
            <ul style="margin:0; padding-left:1.1rem;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <!-- Daily / Weekly / Monthly overview -->
        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div class="summary-card">
                <div class="summary-card-title">Today</div>
                <div class="summary-card-value balance-color">{{ $todayCount }}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">{{ $fmt($todayMinutes) }} logged</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">This Week</div>
                <div class="summary-card-value">{{ $weekCount }}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">{{ $fmt($weekMinutes) }} logged</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">This Month</div>
                <div class="summary-card-value">{{ $monthCount }}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">{{ $fmt($monthMinutes) }} logged</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">All Time</div>
                <div class="summary-card-value income-color">{{ $totalCount }}</div>
                <div style="font-size:0.75rem; color:#94a3b8;">activities recorded</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card" style="padding: 1rem;">
            <form method="GET" action="{{ route('daily-activities.index') }}" class="filter-bar" style="margin-bottom: 0;">
                <div class="filter-group">
                    <label for="search" class="filter-label">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Title or notes">
                </div>

                <div class="filter-group">
                    <label for="category" class="filter-label">Category</label>
                    <select name="category" id="category" class="form-control" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="goal_id" class="filter-label">Goal</label>
                    <select name="goal_id" id="goal_id" class="form-control" onchange="this.form.submit()">
                        <option value="">All Goals</option>
                        @foreach($goals as $goalOption)
                        <option value="{{ $goalOption->id }}" {{ (string) request('goal_id') === (string) $goalOption->id ? 'selected' : '' }}>{{ $goalOption->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label for="date" class="filter-label">Date</label>
                    <input type="date" id="date" name="date" value="{{ request('date') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="from" class="filter-label">From</label>
                    <input type="date" id="from" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <label for="to" class="filter-label">To</label>
                    <input type="date" id="to" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="filter-group">
                    <button type="submit" class="btn-primary btn-sm" style="padding:0.625rem 1rem;">Filter</button>
                </div>

                @if(request()->hasAny(['search', 'category', 'goal_id', 'date', 'from', 'to']))
                <div class="filter-group">
                    <a href="{{ route('daily-activities.index') }}" class="btn-secondary btn-sm" style="padding:0.625rem 0.75rem;">Reset</a>
                </div>
                @endif
            </form>
        </div>

        <!-- Activity list (journal) -->
        <div class="card">
            @if($activities->count() > 0)
            @php $lastDate = null; @endphp
            @foreach($activities as $activity)
            @if(optional($activity->activity_date)->format('Y-m-d') !== $lastDate)
            @php $lastDate = optional($activity->activity_date)->format('Y-m-d'); @endphp
            <div style="display:flex; align-items:center; gap:0.75rem; margin:1.25rem 0 0.5rem 0;">
                <span style="font-weight:700; font-size:0.9rem;">{{ user_date($activity->activity_date) }}</span>
                <span style="flex:1; height:1px; background:var(--border-color);"></span>
            </div>
            @endif
            <div style="border:1px solid var(--border-color); border-radius:0.5rem; padding:0.85rem 1rem; margin-bottom:0.6rem;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem; flex-wrap:wrap;">
                    <div style="min-width:200px;">
                        <div style="font-weight:600;">
                            <a href="{{ route('daily-activities.show', $activity) }}" style="color:var(--text-main); text-decoration:none;">{{ $activity->title }}</a>
                        </div>
                        @if($activity->description)
                        <div style="font-size:0.8rem; color:#64748b; margin-top:0.15rem;">{{ \Illuminate\Support\Str::limit($activity->description, 140) }}</div>
                        @endif
                        <div style="display:flex; flex-wrap:wrap; gap:0.35rem; margin-top:0.4rem;">
                            @if($activity->category)<span class="badge badge-inactive">{{ $activity->category }}</span>@endif
                            @if($activity->goal)<span class="badge badge-active">🎯 {{ $activity->goal->title }}</span>@endif
                            <span class="badge badge-income">{{ $activity->durationLabel() }}</span>
                            @if($activity->start_time)<span style="font-size:0.75rem; color:#94a3b8;">{{ $activity->startTimeLabel() }} – {{ $activity->endTimeLabel() }}</span>@endif
                        </div>
                    </div>
                    <div class="action-buttons" style="justify-content:flex-end;">
                        <a href="{{ route('daily-activities.edit', $activity) }}" class="btn-sm btn-secondary">Edit</a>
                        <form method="POST" action="{{ route('daily-activities.destroy', $activity) }}"
                              data-confirm
                              data-confirm-title="Delete activity?"
                              data-confirm-message="Are you sure you want to delete this activity? This action cannot be undone."
                              data-confirm-action="Delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="pagination-wrapper">{{ $activities->links() }}</div>
            @else
            <div class="empty-state">
                <div class="empty-state-title">No activities recorded yet</div>
                <p style="font-size:0.875rem; margin-bottom:1rem;">Start your daily journal — record what you actually did today.</p>
                <a href="{{ route('daily-activities.create') }}" class="btn-primary btn-sm" style="padding:0.5rem 1rem;">+ Add Activity</a>
            </div>
            @endif
        </div>
    </x-app-layout>