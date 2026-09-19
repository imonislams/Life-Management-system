@php
use App\Models\DailyActivity;
@endphp
<x-app-layout>
    <x-slot name="title">Edit Activity - Daily Activities</x-slot>
    <x-slot name="pageTitle">Daily Activities</x-slot>

    <div class="card" style="max-width: 680px; margin: 0 auto;">
        <h1 class="card-title">Edit Activity</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Update this journal entry.</p>

        <form method="POST" action="{{ route('daily-activities.update', $activity) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title" class="form-label">Title <span style="color: var(--danger-color);">*</span></label>
                <input id="title" type="text" name="title" value="{{ old('title', $activity->title) }}" required autofocus class="form-control">
                @error('title')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description / Notes (Optional)</label>
                <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $activity->description) }}</textarea>
                @error('description')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label for="activity_date" class="form-label">Date <span style="color: var(--danger-color);">*</span></label>
                    <input id="activity_date" type="date" name="activity_date" value="{{ old('activity_date', optional($activity->activity_date)->format('Y-m-d')) }}" required class="form-control">
                    @error('activity_date')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category (Optional)</label>
                            <input id="category" type="text" name="category" value="{{ old('category', $activity->category) }}" class="form-control">
                            @error('category')<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                    </div>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input id="start_time" type="time" name="start_time" value="{{ old('start_time', $activity->start_time) }}" class="form-control">
                        @error('start_time')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="end_time" class="form-label">End Time</label>
                        <input id="end_time" type="time" name="end_time" value="{{ old('end_time', $activity->end_time) }}" class="form-control">
                        @error('end_time')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="duration_minutes" class="form-label">Duration (min)</label>
                        <input id="duration_minutes" type="number" min="0" name="duration_minutes" value="{{ old('duration_minutes', $activity->duration_minutes) }}" class="form-control">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label for="goal_id" class="form-label">Related Goal (Optional)</label>
                        <select id="goal_id" name="goal_id" class="form-control">
                            <option value="">— None —</option>
                            @foreach($goals as $goalOption)
                            <option value="{{ $goalOption->id }}" {{ (string) old('goal_id', $activity->goal_id) === (string) $goalOption->id ? 'selected' : '' }}>{{ $goalOption->title }}</option>
                            @endforeach
                        </select>
                        @error('goal_id')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-control">
                            @foreach(DailyActivity::STATUSES as $status)
                            <option value="{{ $status }}" {{ old('status', $activity->status) === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                    <button type="submit" class="btn-primary">Update Activity</button>
                    <a href="{{ route('daily-activities.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
                </div>
        </form>
    </div>
</x-app-layout>