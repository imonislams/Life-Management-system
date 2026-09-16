<x-app-layout>
    <x-slot name="title">Edit Salary - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Salary Management</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Edit Salary Record</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Update details for your fixed monthly salary.</p>

        <form method="POST" action="{{ route('salary.update', $salary) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="amount" class="form-label">Monthly Salary Amount (TK) <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="amount"
                    value="{{ old('amount', $salary->amount) }}"
                    required
                    class="form-control"
                    placeholder="e.g. 30000.00"
                >
                @error('amount')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="payment_day" class="form-label">Salary Payment Day (Optional)</label>
                <input
                    id="payment_day"
                    type="number"
                    min="1"
                    max="31"
                    name="payment_day"
                    value="{{ old('payment_day', $salary->payment_day) }}"
                    class="form-control"
                    placeholder="e.g. 1"
                >
                <small style="display: block; color: var(--text-muted); font-size: 0.75rem; margin-top: 0.25rem;">
                    Enter the day of the month when you usually receive your salary.
                </small>
                @error('payment_day')
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
                    placeholder="e.g. Primary job monthly salary"
                >{{ old('description', $salary->description) }}</textarea>
                @error('description')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
                <input
                    id="is_active"
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $salary->is_active) ? 'checked' : '' }}
                    style="width: 18px; height: 18px; cursor: pointer;"
                >
                <label for="is_active" class="form-label" style="margin-bottom: 0; cursor: pointer;">
                    Active Status
                </label>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Update Salary</button>
                <a href="{{ route('salary.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
