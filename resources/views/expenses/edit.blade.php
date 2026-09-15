<x-app-layout>
    <x-slot name="title">Edit Expense - Personal Finance Management System</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Edit Expense</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Update details for this expense record</p>

        <form method="POST" action="{{ route('expenses.update', $expense) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="expense_category_id" class="form-label">Category</label>
                <select id="expense_category_id" name="expense_category_id" required autofocus class="form-control">
                    <option value="">Select a Category</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
                @error('expense_category_id')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount" class="form-label">Amount (TK)</label>
                <input
                    id="amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="amount"
                    value="{{ old('amount', $expense->amount) }}"
                    required
                    class="form-control"
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
                    value="{{ old('date', \Carbon\Carbon::parse($expense->date)->format('Y-m-d')) }}"
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
                    value="{{ old('description', $expense->description) }}"
                    class="form-control"
                >
                @error('description')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Update Expense</button>
                <a href="{{ route('expenses.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
