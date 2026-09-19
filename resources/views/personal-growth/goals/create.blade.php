@php
use App\Models\Goal;
@endphp
<x-app-layout>
    <x-slot name="title">Add Goal - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Goals</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Create New Goal</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Describe the goal you want to work toward.</p>

        <form method="POST" action="{{ route('goals.store') }}">
            @csrf

            <div class="form-group">
                <label for="title" class="form-label">Goal Title <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    autofocus
                    class="form-control"
                    placeholder="e.g. Save for a new laptop">
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
                    placeholder="What does success look like for this goal?">{{ old('description') }}</textarea>
                @error('description')
                <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="target_value" class="form-label">Target (Optional)</label>
                <input id="target_value" type="text" name="target_value" value="{{ old('target_value') }}" class="form-control" placeholder="e.g. Run 5km, Save 100000">
                @error('target_value')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label for="progress_type" class="form-label">How is progress measured? <span style="color: var(--danger-color);">*</span></label>
                    <select id="progress_type" name="progress_type" class="form-control" required onchange="document.getElementById('targetAmountGroup').style.display = this.value === 'measurable' ? '' : 'none';">
                        <option value="qualitative" {{ old('progress_type', 'qualitative') === 'qualitative' ? 'selected' : '' }}>Qualitative (log daily updates)</option>
                        <option value="measurable" {{ old('progress_type') === 'measurable' ? 'selected' : '' }}>Measurable (numeric target)</option>
                    </select>
                    @error('progress_type')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" id="targetAmountGroup" style="display: {{ old('progress_type') === 'measurable' ? '' : 'none' }};">
                    <label for="target_amount" class="form-label">Target Amount</label>
                    <input id="target_amount" type="number" step="0.01" min="0.01" name="target_amount" value="{{ old('target_amount') }}" class="form-control" placeholder="e.g. 100">
                    <div style="font-size:0.72rem; color:#94a3b8;">Progress is built from your logged updates.</div>
                    @error('target_amount')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="start_date" class="form-label">Start Date (Optional)</label>
                    <input id="start_date" type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="form-control">
                    @error('start_date')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="target_date" class="form-label">Target Date (Optional)</label>
                    <input
                        id="target_date"
                        type="date"
                        name="target_date"
                        value="{{ old('target_date') }}"
                        class="form-control">
                    @error('target_date')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="priority" class="form-label">Priority <span style="color: var(--danger-color);">*</span></label>
                    <select id="priority" name="priority" class="form-control" required>
                        @foreach(Goal::PRIORITIES as $priorityOption)
                        <option value="{{ $priorityOption }}" {{ old('priority', 'medium') === $priorityOption ? 'selected' : '' }}>
                            {{ ucfirst($priorityOption) }}
                        </option>
                        @endforeach
                    </select>
                    @error('priority')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        @foreach(Goal::STATUSES as $statusOption)
                        <option value="{{ $statusOption }}" {{ old('status', 'not_started') === $statusOption ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', ucfirst($statusOption)) }}
                        </option>
                        @endforeach
                    </select>
                    @error('status')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="progress" class="form-label">Progress % (0-100) <span style="color: var(--danger-color);">*</span></label>
                    <input
                        id="progress"
                        type="number"
                        name="progress"
                        min="0"
                        max="100"
                        value="{{ old('progress', 0) }}"
                        required
                        class="form-control">
                    @error('progress')
                    <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="2" class="form-control" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                @error('notes')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Goal</button>
                <a href="{{ route('goals.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>