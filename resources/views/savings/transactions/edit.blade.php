@php
use App\Models\SavingsTransaction;
@endphp
<x-app-layout>
    <x-slot name="title">Edit Savings Transaction - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Savings</x-slot>

    <div class="card" style="max-width: 640px; margin: 0 auto;">
        <h1 class="card-title">Edit Savings Transaction</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Adjust the details of this deposit or withdrawal.</p>

        <form method="POST" action="{{ route('savings.transactions.update', $transaction) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="savings_goal_id" class="form-label">Savings Goal</label>
                <select id="savings_goal_id" name="savings_goal_id" class="form-control" required>
                    @foreach($goals as $goalOption)
                    <option value="{{ $goalOption->id }}" {{ old('savings_goal_id', $transaction->savings_goal_id) == $goalOption->id ? 'selected' : '' }}>
                        {{ $goalOption->name }}
                    </option>
                    @endforeach
                </select>
                @error('savings_goal_id')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label for="type" class="form-label">Type</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="deposit" {{ old('type', $transaction->type) === 'deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="withdrawal" {{ old('type', $transaction->type) === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount" class="form-label">Amount</label>
                    <input id="amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $transaction->amount) }}" required class="form-control">
                    @error('amount')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="date" class="form-label">Date</label>
                <input id="date" type="date" name="date" value="{{ old('date', optional($transaction->date)->format('Y-m-d')) }}" required class="form-control">
            </div>

            @include('partials.currency-select', [
            'currencies' => $currencies,
            'defaultCurrency' => $defaultCurrency,
            'selectedId' => $transaction->currency_id,
            ])

            <div class="form-group">
                <label for="note" class="form-label">Note (Optional)</label>
                <input id="note" type="text" name="note" value="{{ old('note', $transaction->note) }}" class="form-control">
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Update Transaction</button>
                <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>