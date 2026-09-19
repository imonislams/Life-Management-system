@php
$progressPercent = $progressPercentage ?? $goal->progressPercentage();
@endphp
<x-app-layout>
    <x-slot name="title">{{ $goal->title }} - Goal Details</x-slot>
    <x-slot name="pageTitle">Goal Details</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">{{ $goal->title }}</h2>
                <p class="card-subtitle">
                    {{ $goal->statusLabel() }}
                    @if($goal->target_date)
                    · Target {{ user_date($goal->target_date) }}
                    @endif
                    @if($goal->isOverdue())
                    · <span style="color: #dc2626; font-weight: 600;">Overdue</span>
                    @endif
                </p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('goals.edit', $goal) }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Edit</a>
                <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Goals</a>
            </div>
        </div>

        @if($goal->description)
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">{{ $goal->description }}</p>
        @endif
    </div>

<!-- Summary Cards -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Status</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                @if($goal->status === 'completed')
                <span class="badge badge-income">Completed</span>
                @elseif($goal->status === 'in_progress')
                <span class="badge badge-active">In Progress</span>
                @elseif($goal->status === 'paused')
                <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Paused</span>
                @else
                <span class="badge badge-inactive">Not Started</span>
                @endif
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Priority</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ ucfirst($goal->priority) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Progress</div>
            <div class="summary-card-value balance-color">{{ $progressPercent !== null ? $progressPercent . '%' : '—' }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Total Duration</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ $durationDays !== null ? $durationDays . ' days' : '—' }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Days Elapsed</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ $daysElapsed !== null ? $daysElapsed . ' days' : '—' }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Days Remaining</div>
            <div class="summary-card-value" style="font-size: 1.125rem; {{ ($daysRemaining !== null && $daysRemaining <= 3 && $goal->status !== 'completed') ? 'color:#dc2626;' : '' }}">{{ $daysRemaining !== null ? $daysRemaining . ' days' : '—' }}</div>
        </div>
    </div>

    <!-- Duration window -->
    <div class="card">
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:0.75rem; font-size:0.85rem;">
            <span><strong>Start:</strong> {{ $goal->start_date ? user_date($goal->start_date) : 'Not set' }}</span>
            <span><strong>Target:</strong> {{ $goal->target_date ? user_date($goal->target_date) : 'Not set' }}</span>
            @if($goal->target_value)
                <span><strong>Target value:</strong> {{ $goal->target_value }}</span>
            @endif
            @if($goal->progress_type === 'measurable' && $goal->target_amount)
                <span><strong>Measured:</strong> {{ rtrim(rtrim(number_format((float) $goal->current_amount, 2), '0'), '.') }} / {{ rtrim(rtrim(number_format((float) $goal->target_amount, 2), '0'), '.') }}</span>
            @endif
            <span><strong>Time logged:</strong> {{ intdiv($totalMinutes, 60) }}h {{ $totalMinutes % 60 }}m</span>
        </div>

        @if($progressPercent !== null)
            <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 12px; overflow: hidden; margin-top: 0.75rem;">
                <div style="width: {{ min(100, $progressPercent) }}%; background-color: var(--primary-color); height: 100%; transition: width 0.3s;"></div>
            </div>
        @endif
    </div>

    <!-- Add Today's Progress -->
    <div class="card">
        <div class="card-header-flex" style="margin-bottom: 0.75rem;">
            <div>
                <h3 class="card-title">Add Today's Progress</h3>
                <p class="card-subtitle">Log what you did toward this goal. Every update is kept as history.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('goal-progress.store', $goal) }}">
            @csrf
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
                <div class="form-group">
                    <label for="date" class="form-label">Date <span style="color: var(--danger-color);">*</span></label>
                    <input id="date" type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required class="form-control">
                    @error('date')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                @if($goal->progress_type === 'measurable')
                    <div class="form-group">
                        <label for="progress_value" class="form-label">Progress Amount</label>
                        <input id="progress_value" type="number" step="0.01" name="progress_value" value="{{ old('progress_value') }}" class="form-control" placeholder="e.g. 10">
                        <div style="font-size:0.72rem; color:#94a3b8;">Added to your current total.</div>
                    </div>
                @endif
                <div class="form-group">
                    <label for="time_spent_minutes" class="form-label">Time Spent (minutes)</label>
                    <input id="time_spent_minutes" type="number" min="0" name="time_spent_minutes" value="{{ old('time_spent_minutes') }}" class="form-control" placeholder="e.g. 120">
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">What I did today <span style="color: var(--danger-color);">*</span></label>
                <textarea id="description" name="description" rows="2" class="form-control" placeholder="e.g. Studied Eloquent &amp; practiced relationships" required>{{ old('description') }}</textarea>
                @error('description')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <input id="notes" type="text" name="notes" value="{{ old('notes') }}" class="form-control">
            </div>

            <button type="submit" class="btn-primary">Add Today's Progress</button>
        </form>
    </div>

    <!-- Progress Timeline / History -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="card-title">Progress Timeline</h3>
                <p class="card-subtitle">Every daily progress update, newest first.</p>
            </div>
        </div>

        @if($progressUpdates->count() > 0)
            @foreach($progressUpdates as $update)
                <div style="border-left: 3px solid var(--primary-color); padding: 0.5rem 0 0.5rem 0.85rem; margin-bottom: 0.85rem;">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.75rem; flex-wrap:wrap;">
                        <div>
                            <div style="font-weight:700; font-size:0.85rem;">{{ user_date($update->date) }}</div>
                            <div style="font-size:0.9rem; white-space:pre-line;">{{ $update->description }}</div>
                            <div style="display:flex; flex-wrap:wrap; gap:0.35rem; margin-top:0.35rem;">
                                @if($update->progress_value !== null)
                                    <span class="badge badge-income">+{{ rtrim(rtrim(number_format((float) $update->progress_value, 2), '0'), '.') }}</span>
                                @endif
                                @if($update->time_spent_minutes)
                                    <span class="badge badge-active">{{ $update->timeSpentLabel() }}</span>
                                @endif
                                @if($update->notes)
                                    <span style="font-size:0.75rem; color:#94a3b8;">{{ $update->notes }}</span>
                                @endif
                            </div>
                        </div>
                        <form method="POST" action="{{ route('goal-progress.destroy', $update) }}"
                              data-confirm
                              data-confirm-title="Delete progress update?"
                              data-confirm-message="Are you sure you want to delete this progress update? This action cannot be undone."
                              data-confirm-action="Delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
            <div class="pagination-wrapper">{{ $progressUpdates->links() }}</div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No progress updates yet</div>
                <p style="font-size:0.875rem;">Use “Add Today's Progress” above to start tracking your daily work toward this goal.</p>
            </div>
        @endif
    </div>

    <!-- Related Daily Activities -->
    @if($relatedActivities->count() > 0)
        <div class="card">
            <h3 class="card-title">Related Daily Activities</h3>
            <p class="card-subtitle">Journal entries linked to this goal.</p>
            <ul style="list-style:none; padding:0; margin:0.75rem 0 0 0;">
                @foreach($relatedActivities as $activity)
                    <li style="display:flex; justify-content:space-between; gap:0.5rem; padding:0.4rem 0; border-bottom:1px solid #f1f5f9; font-size:0.85rem;">
                        <a href="{{ route('daily-activities.show', $activity) }}" style="color:var(--text-main); text-decoration:none;">{{ $activity->title }}</a>
                        <span style="color:#94a3b8; white-space:nowrap;">{{ user_date($activity->activity_date) }} · {{ $activity->durationLabel() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card">
        <h3 class="card-title">Details</h3>
        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.75rem;">
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Progress Type</span>
                <span style="font-weight: 600;">{{ ucfirst($goal->progress_type ?? 'qualitative') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Created</span>
                <span style="font-weight: 600;">{{ user_date($goal->created_at) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Completed At</span>
                <span style="font-weight: 600;">{{ $goal->completed_at ? user_date($goal->completed_at) : '—' }}</span>
            </div>
        </div>
    </div>
</x-app-layout>