@php
use App\Models\Event;
@endphp
<x-app-layout>
    <x-slot name="title">Add Event - Important Dates</x-slot>
    <x-slot name="pageTitle">Events</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Create New Event</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Record an important date you want to remember.</p>

        <form method="POST" action="{{ route('events.store') }}">
            @csrf

            <div class="form-group">
                <label for="title" class="form-label">Event Title <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    autofocus
                    class="form-control"
                    placeholder="e.g. Team meeting">
                @error('title')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="form-control"
                    placeholder="Additional details about this event...">{{ old('description') }}</textarea>
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
                    value="{{ old('event_date', $presetDate ?? date('Y-m-d')) }}"
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
                        value="{{ old('start_time') }}"
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
                        value="{{ old('end_time') }}"
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
                    value="{{ old('location') }}"
                    class="form-control"
                    placeholder="e.g. Office conference room">
                @error('location')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="2" class="form-control" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                @error('notes')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    @foreach(Event::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ old('status', 'upcoming') === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst($statusOption) }}
                    </option>
                    @endforeach
                </select>
                @error('status')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Event</button>
                <a href="{{ route('events.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>