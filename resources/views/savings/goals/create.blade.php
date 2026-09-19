@php
use App\Models\SavingsGoal;
@endphp
<x-app-layout>
    <x-slot name="title">Create Savings Goal - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Savings</x-slot>

    <div class="card" style="max-width: 640px; margin: 0 auto;">
        <h1 class="card-title">New Savings Goal</h1>
        <p class="card-subtitle" style="margin-bottom: 1.5rem;">Define a savings account or goal you want to fund.</p>

        <form method="POST" action="{{ route('savings.goals.store') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Goal Name <span style="color: var(--danger-color);">*</span></label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="form-control" placeholder="e.g. New Laptop">
                @error('name')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea id="description" name="description" rows="2" class="form-control" placeholder="What are you saving for?">{{ old('description') }}</textarea>
                @error('description')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            @include('partials.currency-select', [
            'currencies' => $currencies,
            'defaultCurrency' => $defaultCurrency,
            'selectedId' => null,
            ])

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label for="target_amount" class="form-label">Target Amount <span style="color: var(--danger-color);">*</span></label>
                    <input id="target_amount" type="number" step="0.01" min="0.01" name="target_amount" value="{{ old('target_amount') }}" required class="form-control" placeholder="e.g. 100000.00">
                    @error('target_amount')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="current_amount" class="form-label">Opening Balance</label>
                    <input id="current_amount" type="number" step="0.01" min="0" name="current_amount" value="{{ old('current_amount', 0) }}" class="form-control" placeholder="e.g. 0.00">
                    @error('current_amount')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input id="start_date" type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" class="form-control">
                    @error('start_date')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="target_date" class="form-label">Target Date</label>
                    <input id="target_date" type="date" name="target_date" value="{{ old('target_date') }}" class="form-control">
                    @error('target_date')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status <span style="color: var(--danger-color);">*</span></label>
                <select id="status" name="status" class="form-control" required>
                    @foreach(SavingsGoal::STATUSES as $status)
                    <option value="{{ $status }}" {{ old('status', 'active') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                @error('status')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Create Goal</button>
                <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>