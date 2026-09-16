<x-app-layout>
    <x-slot name="title">Add Expense - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Expenses</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Add New Expense</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Enter details for your new expense record</p>

        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf

            <div class="form-group">
                <label for="amount" class="form-label">Amount (TK)</label>
                <input
                    id="amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="amount"
                    value="{{ old('amount') }}"
                    required
                    autofocus
                    class="form-control"
                    placeholder="e.g. 250.00"
                >
                @error('amount')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="date" class="form-label">Date</label>
                <input
                    id="date"
                    type="date"
                    name="date"
                    value="{{ old('date', date('Y-m-d')) }}"
                    required
                    class="form-control"
                >
                @error('date')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <input
                    id="description"
                    type="text"
                    name="description"
                    value="{{ old('description') }}"
                    class="form-control"
                    placeholder="e.g. Grocery shopping"
                >
                @error('description')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Expense</button>
                <a href="{{ route('expenses.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
