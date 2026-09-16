<x-app-layout>
    <x-slot name="title">Add Recurring Record - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Recurring Finance</x-slot>

    <div class="card" style="max-width: 650px; margin: 0 auto;">
        <h1 class="card-title">Add Recurring Record</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Create a new regular income or expense record.</p>

        <form method="POST" action="{{ route('recurring-transactions.store') }}">
            @csrf

            <div class="form-group">
                <label for="type" class="form-label">Type <span style="color: var(--danger-color);">*</span></label>
                <select id="type" name="type" required class="form-control">
                    <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                    <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Income</option>
                </select>
                @error('type')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

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
                    placeholder="e.g. House Rent, Internet Bill, Monthly Salary"
                >
                @error('title')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount" class="form-label">Amount (TK) <span style="color: var(--danger-color);">*</span></label>
                <input
                    id="amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="amount"
                    value="{{ old('amount') }}"
                    required
                    class="form-control"
                    placeholder="e.g. 15000.00"
                >
                @error('amount')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="recurrence_type" class="form-label">Recurrence <span style="color: var(--danger-color);">*</span></label>
                <select id="recurrence_type" name="recurrence_type" required class="form-control">
                    <option value="monthly" {{ old('recurrence_type') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="weekly" {{ old('recurrence_type') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                </select>
                @error('recurrence_type')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="start_date" class="form-label">Start Date <span style="color: var(--danger-color);">*</span></label>
                    <input
                        id="start_date"
                        type="date"
                        name="start_date"
                        value="{{ old('start_date', date('Y-m-d')) }}"
                        required
                        class="form-control"
                    >
                    @error('start_date')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="next_due_date" class="form-label">Next Due Date <span style="color: var(--danger-color);">*</span></label>
                    <input
                        id="next_due_date"
                        type="date"
                        name="next_due_date"
                        value="{{ old('next_due_date', date('Y-m-d')) }}"
                        required
                        class="form-control"
                    >
                    @error('next_due_date')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="form-control"
                    placeholder="e.g. Paid on the 1st of every month"
                >{{ old('description') }}</textarea>
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
                    {{ old('is_active', '1') ? 'checked' : '' }}
                    style="width: 18px; height: 18px; cursor: pointer;"
                >
                <label for="is_active" class="form-label" style="margin-bottom: 0; cursor: pointer;">
                    Active Status
                </label>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Record</button>
                <a href="{{ route('recurring-transactions.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
