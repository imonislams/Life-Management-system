@php
use App\Models\Event;
@endphp
<x-app-layout>
    <x-slot name="title">Edit Event - Important Dates</x-slot>
    <x-slot name="pageTitle">Events</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Edit Event</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Update the details of "{{ $event->title }}".</p>

        <form method="POST" action="{{ route('events.update', $event) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title" class="form-label">Event Title <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $event->title) }}"
                    required
                    autofocus
                    class="form-control">
                @error('title')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $event->description) }}</textarea>
                @error('description')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="event_date" class="form-label">Event Date <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="event_date"
                    type="date"
                    name="event_date"
                    value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}"
                    required
                    class="form-control">
                @error('event_date')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="start_time" class="form-label">Start Time (Optional)</label>
                    <input
                        id="start_time"
                        type="time"
                        name="start_time"
                        value="{{ old('start_time', $event->start_time ? substr($event->start_time, 0, 5) : '') }}"
                        class="form-control">
                    @error('start_time')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_time" class="form-label">End Time (Optional)</label>
                    <input
                        id="end_time"
                        type="time"
                        name="end_time"
                        value="{{ old('end_time', $event->end_time ? substr($event->end_time, 0, 5) : '') }}"
                        class="form-control">
                    @error('end_time')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="location" class="form-label">Location (Optional)</label>
                <input
                    id="location"
                    type="text"
                    name="location"
                    value="{{ old('location', $event->location) }}"
                    class="form-control">
                @error('location')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="2" class="form-control">{{ old('notes', $event->notes) }}</textarea>
                @error('notes')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    @foreach(Event::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ old('status', $event->status) === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst($statusOption) }}
                    </option>
                    @endforeach
                </select>
                @error('status')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Update Event</button>
                <a href="{{ route('events.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>