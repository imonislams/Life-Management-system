@php
use App\Models\Habit;
@endphp
<x-app-layout>
    <x-slot name="title">Edit Habit - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Habits</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Edit Habit</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Update the details of "{{ $habit->title }}".</p>

        <form method="POST" action="{{ route('habits.update', $habit) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title" class="form-label">Habit Title <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $habit->title) }}"
                    required
                    autofocus
                    class="form-control">
                @error('title')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $habit->description) }}</textarea>
                @error('description')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="frequency" class="form-label">Frequency <span style="color: var(--danger-color);">*</span></label>
                    <select id="frequency" name="frequency" class="form-control" required>
                        @foreach(Habit::FREQUENCIES as $freqOption)
                        <option value="{{ $freqOption }}" {{ old('frequency', $habit->frequency) === $freqOption ? 'selected' : '' }}>
                            {{ ucfirst($freqOption) }}
                        </option>
                        @endforeach
                    </select>
                    @error('frequency')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="start_date" class="form-label">Start Date (Optional)</label>
                    <input
                        id="start_date"
                        type="date"
                        name="start_date"
                        value="{{ old('start_date', optional($habit->start_date)->format('Y-m-d')) }}"
                        class="form-control">
                    @error('start_date')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    @foreach(Habit::STATUSES as $statusOption)
                    <option value="{{ $statusOption }}" {{ old('status', $habit->status) === $statusOption ? 'selected' : '' }}>
                        {{ ucfirst($statusOption) }}
                    </option>
                    @endforeach
                </select>
                @error('status')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            @include('daily-management.habits.partials.activities-input', ['habit' => $habit])

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Update Habit</button>
                <a href="{{ route('habits.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>