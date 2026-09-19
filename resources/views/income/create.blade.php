<x-app-layout>
    <x-slot name="title">Add Income - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Income</x-slot>

    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h1 class="card-title">Add New Income</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Enter details for your new income record</p>

        <form method="POST" action="{{ route('income.store') }}">
            @csrf

            <div class="form-group">
                <label for="amount" class="form-label">Amount</label>
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
                    placeholder="e.g. 5000.00"
                >
                @error('amount')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            @include('partials.currency-select', ['currencies' => $currencies, 'defaultCurrency' => $defaultCurrency])

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

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label for="source" class="form-label">Source (Optional)</label>
                    <input id="source" type="text" name="source" value="{{ old('source') }}" class="form-control" placeholder="e.g. Upwork">
                    @error('source')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="category" class="form-label">Category (Optional)</label>
                    <input id="category" type="text" name="category" value="{{ old('category') }}" class="form-control" placeholder="e.g. Freelance">
                    @error('category')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <input
                    id="description"
                    type="text"
                    name="description"
                    value="{{ old('description') }}"
                    class="form-control"
                    placeholder="e.g. Freelance project payment"
                >
                @error('description')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes" class="form-label">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="2" class="form-control" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                @error('notes')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Income</button>
                <a href="{{ route('income.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
