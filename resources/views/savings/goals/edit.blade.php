@php
use App\Models\SavingsGoal;
@endphp
<x-app-layout>
    <x-slot name="title">Edit Savings Goal - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Savings</x-slot>

    <div class="card" style="max-width: 640px; margin: 0 auto;">
        <h1 class="card-title">Edit Savings Goal</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Update the details of “{{ $goal->name }}”.</p>

        <form method="POST" action="{{ route('savings.goals.update', $goal) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name" class="form-label">Goal Name <span style="color: var(--danger-color);">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name', $goal->name) }}" required autofocus class="form-control">
                @error('name')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea id="description" name="description" rows="2" class="form-control">{{ old('description', $goal->description) }}</textarea>
                @error('description')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            @include('partials.currency-select', [
            'currencies' => $currencies,
            'defaultCurrency' => $defaultCurrency,
            'selectedId' => $goal->currency_id,
            ])

            <div class="form-group">
                <label for="target_amount" class="form-label">Target Amount <span style="color: var(--danger-color);">*</span></label>
                <input id="target_amount" type="number" step="0.01" min="0.01" name="target_amount" value="{{ old('target_amount', $goal->target_amount) }}" required class="form-control">
                @error('target_amount')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input id="start_date" type="date" name="start_date" value="{{ old('start_date', optional($goal->start_date)->format('Y-m-d')) }}" class="form-control">
                </div>

                <div class="form-group">
                    <label for="target_date" class="form-label">Target Date</label>
                    <input id="target_date" type="date" name="target_date" value="{{ old('target_date', optional($goal->target_date)->format('Y-m-d')) }}" class="form-control">
                    @error('target_date')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    @foreach(SavingsGoal::STATUSES as $status)
                    <option value="{{ $status }}" {{ old('status', $goal->status) === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:0.5rem; padding:0.75rem 1rem; font-size:0.8rem; color:#64748b; margin-bottom:1rem;">
                The current balance is managed through deposits and withdrawals on the goal's detail page, so it always matches the transaction history.
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Update Goal</button>
                <a href="{{ route('savings.goals.show', $goal) }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>