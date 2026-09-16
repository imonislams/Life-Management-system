<x-app-layout>
    <x-slot name="title">Tasks - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Tasks</x-slot>

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Daily Tasks</h2>
                <p class="card-subtitle">Manage, organize, and track your daily tasks and to-dos.</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="btn-primary">+ Add New Task</a>
        </div>
    </div>

    <!-- Status Alert -->
    @if(session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif

    <!-- Summary Cards Grid -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Total Tasks</div>
            <div class="summary-card-value">{{ $totalTasks }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Pending</div>
            <div class="summary-card-value" style="color: #ea580c;">{{ $pendingTasks }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">In Progress</div>
            <div class="summary-card-value balance-color">{{ $inProgressTasks }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Completed</div>
            <div class="summary-card-value income-color">{{ $completedTasks }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="padding: 1rem;">
        <form method="GET" action="{{ route('tasks.index') }}" class="filter-bar" style="margin-bottom: 0;">
            <div class="filter-group">
                <label for="status" class="filter-label">Status</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="priority" class="filter-label">Priority</label>
                <select name="priority" id="priority" class="form-control" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>

            @if(request()->hasAny(['status', 'priority']))
                <div class="filter-group">
                    <a href="{{ route('tasks.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.75rem;">Reset Filters</a>
                </div>
            @endif
        </form>
    </div>

    <!-- Tasks List Table -->
    <div class="card">
        @if($tasks->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">Done</th>
                            <th>Task Title</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr style="{{ $task->status === 'completed' ? 'opacity: 0.65; background-color: #f8fafc;' : '' }}">
                                <td>
                                    <form method="POST" action="{{ route('tasks.toggle', $task) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <input
                                            type="checkbox"
                                            onchange="this.form.submit()"
                                            {{ $task->status === 'completed' ? 'checked' : '' }}
                                            style="width: 18px; height: 18px; cursor: pointer;"
                                            title="Toggle completion status"
                                        >
                                    </form>
                                </td>
                                <td>
                                    <div style="font-weight: 600; {{ $task->status === 'completed' ? 'text-decoration: line-through; color: #64748b;' : '' }}">
                                        {{ $task->title }}
                                    </div>
                                    @if($task->description)
                                        <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.15rem;">
                                            {{ Str::limit($task->description, 80) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($task->priority === 'high')
                                        <span class="badge badge-expense">High</span>
                                    @elseif($task->priority === 'medium')
                                        <span class="badge badge-active">Medium</span>
                                    @else
                                        <span class="badge badge-inactive">Low</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->status === 'completed')
                                        <span class="badge badge-income">Completed</span>
                                    @elseif($task->status === 'in_progress')
                                        <span class="badge badge-active">In Progress</span>
                                    @else
                                        <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($task->due_date)
                                        <span style="font-size: 0.85rem; {{ $task->due_date->isPast() && $task->status !== 'completed' ? 'color: #dc2626; font-weight: 600;' : '' }}">
                                            {{ $task->due_date->format('M d, Y') }}
                                        </span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">No due date</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('tasks.edit', $task) }}" class="btn-sm btn-secondary">Edit</a>
                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Are you sure you want to delete this task?');">
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

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $tasks->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No tasks found</div>
                <p style="font-size: 0.875rem; margin-bottom: 1rem;">Get started by adding your first daily task.</p>
                <a href="{{ route('tasks.create') }}" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">+ Add Task</a>
            </div>
        @endif
    </div>
</x-app-layout>
