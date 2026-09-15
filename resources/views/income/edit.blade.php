<x-app-layout>
    <x-slot name="title">Edit Income - Personal Finance Management System</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Edit Income</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Update details for this income record</p>

        <form method="POST" action="{{ route('income.update', $income) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="amount" class="form-label">Amount (TK)</label>
                <input
                    id="amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="amount"
                    value="{{ old('amount', $income->amount) }}"
                    required
                    autofocus
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
                    value="{{ old('date', \Carbon\Carbon::parse($income->date)->format('Y-m-d')) }}"
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
                    value="{{ old('description', $income->description) }}"
                    class="form-control"
                >
                @error('description')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Update Income</button>
                <a href="{{ route('income.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
