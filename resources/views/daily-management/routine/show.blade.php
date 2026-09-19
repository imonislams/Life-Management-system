@php
use Carbon\Carbon;
@endphp
<x-app-layout>
    <x-slot name="title">{{ $routineItem->title }} - Routine Details</x-slot>
    <x-slot name="pageTitle">Routine Details</x-slot>

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">{{ $routineItem->title }}</h2>
                <p class="card-subtitle">
                    {{ $routineItem->startTimeLabel() }} – {{ $routineItem->endTimeLabel() }}
                </p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('routine.edit', $routineItem) }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Edit</a>
                <a href="{{ route('routine.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Routine</a>
            </div>
        </div>
    </div>

<div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Status</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                @if($routineItem->status === 'completed')
                <span class="badge badge-income">Completed</span>
                @elseif($routineItem->status === 'skipped')
                <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5;">Skipped</span>
                @elseif($routineItem->status === 'paused')
                <span class="badge badge-inactive">Paused</span>
                @else
                <span class="badge badge-active">Active</span>
                @endif
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Start Time</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ $routineItem->startTimeLabel() }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">End Time</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ $routineItem->endTimeLabel() }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Recurrence</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ $routineItem->recurrenceLabel() }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Scheduled Today</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                {{ $routineItem->isDueOn(\Carbon\Carbon::today()) ? 'Yes' : 'No' }}
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title">Description</h3>
        @if($routineItem->description)
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.5rem;">{{ $routineItem->description }}</p>
        @else
        <div class="empty-state" style="padding: 1.5rem 0;">
            <div class="empty-state-title">No description provided</div>
            <p style="font-size: 0.875rem;">Edit this routine item to add notes.</p>
        </div>
        @endif
    </div>

    <!-- Quick marking of today's occurrence -->
    <div class="card">
        <h3 class="card-title">Today's Occurrence</h3>
        <p class="card-subtitle" style="margin-bottom:0.75rem;">Mark today independent of the routine template. History is kept separately.</p>
        <div class="action-buttons">
            <form method="POST" action="{{ route('routine.mark', $routineItem) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="occurrence_date" value="{{ now()->toDateString() }}">
                <input type="hidden" name="status" value="completed">
                <button type="submit" class="btn-primary btn-sm" style="padding:0.5rem 1rem;">Mark Completed</button>
            </form>
            <form method="POST" action="{{ route('routine.mark', $routineItem) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="occurrence_date" value="{{ now()->toDateString() }}">
                <input type="hidden" name="status" value="skipped">
                <button type="submit" class="btn-secondary btn-sm" style="padding:0.5rem 1rem;">Mark Skipped</button>
            </form>
            <a href="{{ route('routine.occurrences', $routineItem) }}" class="btn-secondary btn-sm" style="padding:0.5rem 1rem;">View Full History</a>
        </div>
    </div>
</x-app-layout>