@php
use App\Models\RoutineOccurrence;
@endphp
<x-app-layout>
    <x-slot name="title">{{ $routine->title }} Occurrences - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Routine Occurrence History</x-slot>

<div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">{{ $routine->title }}</h2>
                <p class="card-subtitle">
                    {{ $routine->recurrenceLabel() }} · {{ $routine->startTimeLabel() }} – {{ $routine->endTimeLabel() }}
                </p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('routine.show', $routine) }}" class="btn-secondary btn-sm" style="padding:0.625rem 1rem;">Routine Details</a>
                <a href="{{ route('routine.index') }}" class="btn-secondary btn-sm" style="padding:0.625rem 1rem;">Back to Routine</a>
            </div>
        </div>
    </div>

    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Completed</div>
            <div class="summary-card-value income-color">{{ $completed }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Skipped</div>
            <div class="summary-card-value" style="color:#ea580c;">{{ $skipped }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Pending</div>
            <div class="summary-card-value balance-color">{{ $pending }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Completion Rate</div>
            <div class="summary-card-value">{{ $completionRate }}%</div>
        </div>
    </div>

    <div class="card">
        @if($occurrences->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th style="text-align: right;">Update</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($occurrences as $occurrence)
                    <tr>
                        <td style="white-space:nowrap;">{{ user_date($occurrence->occurrence_date) }}</td>
                        <td>
                            @if($occurrence->status === 'completed')
                            <span class="badge badge-income">Completed</span>
                            @elseif($occurrence->status === 'skipped')
                            <span class="badge" style="background:#fff7ed; color:#c2410c; border:1px solid #ffedd5;">Skipped</span>
                            @else
                            <span class="badge badge-active">Pending</span>
                            @endif
                        </td>
                        <td style="font-size:0.85rem; color:#64748b;">{{ \Illuminate\Support\Str::limit($occurrence->notes, 60) ?: '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('routine-occurrences.update', $occurrence) }}" style="display:flex; gap:0.5rem; justify-content:flex-end; align-items:center;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="occurrence_date" value="{{ optional($occurrence->occurrence_date)->format('Y-m-d') }}">
                                <select name="status" class="form-control" style="width:auto; padding:0.3rem 0.5rem; font-size:0.8rem;">
                                    @foreach(RoutineOccurrence::STATUSES as $status)
                                    <option value="{{ $status }}" {{ $occurrence->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-sm btn-secondary">Save</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">{{ $occurrences->links() }}</div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No occurrences yet</div>
            <p style="font-size:0.875rem;">Occurrences are generated automatically for the days this routine is scheduled.</p>
        </div>
        @endif
    </div>
</x-app-layout>