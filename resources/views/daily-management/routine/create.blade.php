@php
use App\Models\RoutineItem;
@endphp
<x-app-layout>
    <x-slot name="title">Add Routine Item - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Daily Routine</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Create Routine Item</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Add a scheduled block to your daily routine.</p>

        <form method="POST" action="{{ route('routine.store') }}">
            @csrf

            <div class="form-group">
                <label for="title" class="form-label">Title <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    autofocus
                    class="form-control"
                    placeholder="e.g. Morning workout">
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
                    placeholder="Notes about this routine block...">{{ old('description') }}</textarea>
                @error('description')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            @include('daily-management.routine.partials.recurrence-fields', ['routineItem' => null])

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="start_time" class="form-label">Start Time <span style="color: var(--danger-color);">*</span></label>
                    <input
                        id="start_time"
                        type="time"
                        name="start_time"
                        value="{{ old('start_time', '08:00') }}"
                        required
                        class="form-control">
                    @error('start_time')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="end_time" class="form-label">End Time <span style="color: var(--danger-color);">*</span></label>
                    <input
                        id="end_time"
                        type="time"
                        name="end_time"
                        value="{{ old('end_time', '09:00') }}"
                        required
                        class="form-control">
                    @error('end_time')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    @foreach(RoutineItem::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ old('status', 'active') === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $statusOption)) }}
                    </option>
                    @endforeach
                </select>
                @error('status')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Routine Item</button>
                <a href="{{ route('routine.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>