@php
use Carbon\Carbon;
@endphp
<x-app-layout>
    <x-slot name="title">{{ $event->title }} - Event Details</x-slot>
    <x-slot name="pageTitle">Event Details</x-slot>

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">{{ $event->title }}</h2>
                <p class="card-subtitle">
                    {{ $event->event_date->format('l, M d, Y') }}
                    @if($event->start_time)
                    · {{ $event->startTimeLabel() }}@if($event->end_time) – {{ $event->endTimeLabel() }}@endif
                    @endif
                </p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('events.edit', $event) }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Edit</a>
                <a href="{{ route('events.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Events</a>
            </div>
        </div>
    </div>

<div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Status</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                @if($event->status === 'completed')
                <span class="badge badge-income">Completed</span>
                @elseif($event->status === 'cancelled')
                <span class="badge badge-expense">Cancelled</span>
                @else
                <span class="badge badge-active">Upcoming</span>
                @endif
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Date</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ user_date($event->event_date) }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Time</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                {{ $event->start_time ? $event->startTimeLabel() : 'All day' }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Days Away</div>
            <div class="summary-card-value">
                {{ Carbon::today()->diffInDays($event->event_date, false) }}
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="card-title">Event Information</h3>
        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.75rem;">
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Location</span>
                <span style="font-weight: 600;">{{ $event->location ?: '—' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f1f5f9;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Created</span>
                <span style="font-weight: 600;">{{ user_date($event->created_at) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                <span style="font-size: 0.875rem; color: var(--text-muted);">Last Updated</span>
                <span style="font-weight: 600;">{{ user_date($event->updated_at) }}</span>
            </div>
        </div>

        @if($event->description)
        <div style="margin-top: 1rem;">
            <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">Description</h4>
            <p style="font-size: 0.9rem;">{{ $event->description }}</p>
        </div>
        @endif
    </div>
</x-app-layout>