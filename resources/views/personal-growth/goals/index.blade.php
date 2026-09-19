@php
use App\Models\Goal;
@endphp
<x-app-layout>
    <x-slot name="title">Goals - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Goals</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Personal Goals</h2>
                <p class="card-subtitle">Set meaningful goals and track your progress toward them.</p>
            </div>
            <a href="{{ route('goals.create') }}" class="btn-primary">+ Add New Goal</a>
        </div>
    </div>

<!-- Summary Cards Grid -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Total Goals</div>
            <div class="summary-card-value">{{ $totalGoals }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Active</div>
            <div class="summary-card-value balance-color">{{ $activeGoals }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Completed</div>
            <div class="summary-card-value income-color">{{ $completedGoals }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Paused</div>
            <div class="summary-card-value" style="color: #ea580c;">{{ $pausedGoals }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Overdue</div>
            <div class="summary-card-value expense-color">{{ $overdueGoals }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Overall Progress</div>
            <div class="summary-card-value">{{ $overallProgress }}%</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding: 1rem;">
        <form method="GET" action="{{ route('goals.index') }}" class="filter-bar" style="margin-bottom: 0;">
            <div class="filter-group">
                <label for="status" class="filter-label">Status</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    @foreach(Goal::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ request('status') === $statusOption ? 'selected' : '' }}>
                        {{ str_replace('_', ' ', ucfirst($statusOption)) }}
                    </option>
                    @endforeach
                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="priority" class="filter-label">Priority</label>
                <select name="priority" id="priority" class="form-control" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    @foreach(Goal::PRIORITIES as $priorityOption)
                    <option value="{{ $priorityOption }}" {{ request('priority') === $priorityOption ? 'selected' : '' }}>
                        {{ ucfirst($priorityOption) }}
                    </option>
                    @endforeach
                </select>
            </div>

            @if(request()->hasAny(['status', 'priority']))
            <div class="filter-group">
                <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.75rem;">Reset Filters</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Goals Table -->
    <div class="card">
        @if($goals->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Goal</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Target Date</th>
                        <th style="min-width: 140px;">Progress</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($goals as $goal)
                    <tr style="{{ $goal->status === 'completed' ? 'opacity: 0.75;' : '' }}">
                        <td>
                            <a href="{{ route('goals.show', $goal) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none;">
                                {{ $goal->title }}
                            </a>
                            @if($goal->isOverdue())
                            <span class="badge badge-expense" style="margin-left: 0.35rem;">Overdue</span>
                            @endif
                            @if($goal->description)
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                {{ Str::limit($goal->description, 80) }}
                            </div>
                            @endif
                        </td>
                        <td>
                            @if($goal->priority === 'high')
                            <span class="badge badge-expense">High</span>
                            @elseif($goal->priority === 'medium')
                            <span class="badge badge-active">Medium</span>
                            @else
                            <span class="badge badge-inactive">Low</span>
                            @endif
                        </td>
                        <td>
                            @if($goal->status === 'completed')
                            <span class="badge badge-income">Completed</span>
                            @elseif($goal->status === 'in_progress')
                            <span class="badge badge-active">In Progress</span>
                            @elseif($goal->status === 'paused')
                            <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Paused</span>
                            @else
                            <span class="badge badge-inactive">Not Started</span>
                            @endif
                        </td>
                        <td>
                            @if($goal->target_date)
                            <span style="font-size: 0.85rem; {{ $goal->isOverdue() ? 'color: #dc2626; font-weight: 600;' : '' }}">
                                {{ user_date($goal->target_date) }}
                            </span>
                            @else
                            <span style="color: #94a3b8; font-style: italic;">Not set</span>
                            @endif
                        </td>
                        <td>
                            @php $goalPct = $goal->progressPercentage(); @endphp
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="flex: 1; background: #e2e8f0; height: 8px; border-radius: 4px; overflow: hidden; min-width: 60px;">
                                    <div style="background: var(--primary-color); height: 100%; width: {{ $goalPct ?? 0 }}%;"></div>
                                </div>
                                <span style="font-size: 0.75rem; font-weight: 600; white-space: nowrap;">{{ $goalPct !== null ? $goalPct . '%' : '—' }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('goals.show', $goal) }}" class="btn-sm btn-secondary">View</a>
                                <a href="{{ route('goals.edit', $goal) }}" class="btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('goals.destroy', $goal) }}"
                                      data-confirm
                                      data-confirm-title="Delete goal?"
                                      data-confirm-message="Are you sure you want to delete this goal? This action cannot be undone."
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
            {{ $goals->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No goals found</div>
            <p style="font-size: 0.875rem; margin-bottom: 1rem;">Define what you want to achieve and add your first goal.</p>
            <a href="{{ route('goals.create') }}" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">+ Add Goal</a>
        </div>
        @endif
    </div>
</x-app-layout>