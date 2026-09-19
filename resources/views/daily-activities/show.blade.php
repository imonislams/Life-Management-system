@php
$activity = $activity ?? null;
@endphp
<x-app-layout>
    <x-slot name="title">{{ $activity->title }} - Activity Details</x-slot>
    <x-slot name="pageTitle">Daily Activities</x-slot>

<div class="card" style="max-width: 760px; margin: 0 auto;">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">{{ $activity->title }}</h1>
                <p class="card-subtitle">{{ user_date($activity->activity_date) }}</p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('daily-activities.edit', $activity) }}" class="btn-secondary btn-sm" style="padding:0.625rem 1rem;">Edit</a>
                <a href="{{ route('daily-activities.index') }}" class="btn-secondary btn-sm" style="padding:0.625rem 1rem;">Back</a>
            </div>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); margin-top:1rem;">
            <div class="summary-card">
                <div class="summary-card-title">Duration</div>
                <div class="summary-card-value balance-color" style="font-size:1.15rem;">{{ $activity->durationLabel() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Start</div>
                <div class="summary-card-value" style="font-size:1.15rem;">{{ $activity->startTimeLabel() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">End</div>
                <div class="summary-card-value" style="font-size:1.15rem;">{{ $activity->endTimeLabel() }}</div>
            </div>
            <div class="summary-card">
                <div class="summary-card-title">Status</div>
                <div class="summary-card-value" style="font-size:1.15rem;">{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</div>
            </div>
        </div>

        <div style="margin-top:1rem;">
            <div class="filter-label">Category</div>
            <div>{{ $activity->category ?: '—' }}</div>
        </div>

        <div style="margin-top:1rem;">
            <div class="filter-label">Related Goal</div>
            <div>
                @if($activity->goal)
                <a href="{{ route('goals.show', $activity->goal) }}" style="font-weight:600;">{{ $activity->goal->title }}</a>
                @else
                —
                @endif
            </div>
        </div>

        @if($activity->description)
        <div style="margin-top:1rem;">
            <div class="filter-label">Notes</div>
            <p style="white-space:pre-line; color:#475569;">{{ $activity->description }}</p>
        </div>
        @endif
    </div>
</x-app-layout>