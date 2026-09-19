@php
use App\Models\RoutineItem;
@endphp
<x-app-layout>
    <x-slot name="title">Daily Routine - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Daily Routine</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Daily Routine</h2>
                <p class="card-subtitle">Organize and structure your daily schedule, sorted by start time.</p>
            </div>
            <a href="{{ route('routine.create') }}" class="btn-primary">+ Add Routine Item</a>
        </div>
    </div>

<!-- Summary Cards Grid -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Total Routine Items</div>
            <div class="summary-card-value">{{ $totalItems }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Active</div>
            <div class="summary-card-value balance-color">{{ $activeItems }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Completed</div>
            <div class="summary-card-value income-color">{{ $completedItems }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Paused</div>
            <div class="summary-card-value" style="color:#64748b;">{{ $pausedItems }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Skipped</div>
            <div class="summary-card-value" style="color: #ea580c;">{{ $skippedItems }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding: 1rem;">
        <form method="GET" action="{{ route('routine.index') }}" class="filter-bar" style="margin-bottom: 0;">
            <div class="filter-group">
                <label for="status" class="filter-label">Status</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(RoutineItem::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label for="recurrence_type" class="filter-label">Recurrence</label>
                <select name="recurrence_type" id="recurrence_type" class="form-control" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach(RoutineItem::RECURRENCE_TYPES as $typeOption)
                    <option value="{{ $typeOption }}" {{ request('recurrence_type') === $typeOption ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $typeOption)) }}
                    </option>
                    @endforeach
                </select>
            </div>

            @if(request()->hasAny(['status', 'recurrence_type']))
            <div class="filter-group">
                <a href="{{ route('routine.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.75rem;">Reset Filter</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Routine Items Table -->
    <div class="card">
        @if($routineItems->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Start</th>
                        <th>End</th>
                        <th>Routine Item</th>
                        <th>Recurrence</th>
                        <th>Status</th>
                        <th>Today</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($routineItems as $routineItem)
                    <tr style="{{ $routineItem->status === 'skipped' ? 'opacity: 0.7;' : '' }}">
                        <td style="font-weight: 600; white-space: nowrap;">{{ $routineItem->startTimeLabel() }}</td>
                        <td style="white-space: nowrap;">{{ $routineItem->endTimeLabel() }}</td>
                        <td>
                            <a href="{{ route('routine.show', $routineItem) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">
                                {{ $routineItem->title }}
                            </a>
                            @if($routineItem->description)
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                {{ Str::limit($routineItem->description, 80) }}
                            </div>
                            @endif
                        </td>
                        <td><span class="badge badge-active">{{ $routineItem->recurrenceLabel() }}</span></td>
                        <td>
                            @if($routineItem->status === 'completed')
                            <span class="badge badge-income">Completed</span>
                            @elseif($routineItem->status === 'skipped')
                            <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Skipped</span>
                            @elseif($routineItem->status === 'paused')
                            <span class="badge badge-inactive">Paused</span>
                            @else
                            <span class="badge badge-active">Active</span>
                            @endif
                        </td>
                        <td>
                            @php $todayOcc = $todayOccurrences[$routineItem->id] ?? null; @endphp
                            @if(! $routineItem->isDueOn($today))
                                <span style="font-size:0.8rem; color:#94a3b8;">Not scheduled</span>
                            @elseif($todayOcc && $todayOcc->status === 'completed')
                                <span class="badge badge-income">Completed</span>
                            @elseif($todayOcc && $todayOcc->status === 'skipped')
                                <span class="badge" style="background:#fff7ed; color:#c2410c; border:1px solid #ffedd5;">Skipped</span>
                            @else
                                <span class="badge" style="background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe;">Pending</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('routine.occurrences', $routineItem) }}" class="btn-sm btn-secondary">History</a>
                                <a href="{{ route('routine.show', $routineItem) }}" class="btn-sm btn-secondary">View</a>
                                <a href="{{ route('routine.edit', $routineItem) }}" class="btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('routine.destroy', $routineItem) }}"
                                      data-confirm
                                      data-confirm-title="Delete routine item?"
                                      data-confirm-message="Are you sure you want to delete this routine item? This action cannot be undone."
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
            {{ $routineItems->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No routine items found</div>
            <p style="font-size: 0.875rem; margin-bottom: 1rem;">Structure your day by adding your first routine item.</p>
            <a href="{{ route('routine.create') }}" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">+ Add Routine Item</a>
        </div>
        @endif
    </div>
</x-app-layout>