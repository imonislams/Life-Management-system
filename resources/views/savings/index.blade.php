<x-app-layout>
    <x-slot name="title">Savings - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Savings</x-slot>

    @if (session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card" style="margin-bottom: 1.5rem;">
        <h1 class="card-title">Savings Goal</h1>
        <p class="card-subtitle">Track and manage your savings objective.</p>
    </div>

    @if ($savingsGoal)
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header-flex">
                <div>
                    <h2 class="card-title">{{ $savingsGoal->name }}</h2>
                    <p class="card-subtitle">Target: ৳ {{ number_format($savingsGoal->target_amount, 2) }} TK</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                        ৳ {{ number_format($savingsGoal->current_amount, 2) }} TK
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">Current Saved Amount</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="margin-top: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.375rem; font-size: 0.875rem; font-weight: 600;">
                    <span>Progress: {{ $progressPercentage }}%</span>
                    <span>Remaining: ৳ {{ number_format($remainingAmount, 2) }} TK</span>
                </div>
                <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 12px; overflow: hidden;">
                    <div style="width: {{ $progressPercentage }}%; background-color: var(--primary-color); height: 100%; transition: width 0.3s;"></div>
                </div>
            </div>
        </div>
    @else
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="empty-state">
                <div class="empty-state-title">No Savings Goal Set</div>
                <p>Set a goal name, target amount, and current saved amount below to begin tracking your savings.</p>
            </div>
        </div>
    @endif

    <div class="card">
        <h2 class="card-title">{{ $savingsGoal ? 'Edit Savings Goal' : 'Create Savings Goal' }}</h2>

        <form id="savingsGoalForm" method="POST" action="{{ route('savings.store') }}" style="max-width: 500px; margin-top: 1rem;">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Goal Name</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    value="{{ old('name', $savingsGoal?->name) }}"
                    required
                    class="form-control"
                    placeholder="e.g. New Laptop"
                >
                @error('name')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="target_amount" class="form-label">Target Amount (TK)</label>
                <input
                    id="target_amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="target_amount"
                    value="{{ old('target_amount', $savingsGoal?->target_amount) }}"
                    required
                    class="form-control"
                    placeholder="e.g. 100000.00"
                >
                @error('target_amount')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="current_amount" class="form-label">Current Saved Amount (TK)</label>
                <input
                    id="current_amount"
                    type="number"
                    step="0.01"
                    min="0"
                    name="current_amount"
                    value="{{ old('current_amount', $savingsGoal?->current_amount ?? 0) }}"
                    required
                    class="form-control"
                    placeholder="e.g. 25000.00"
                >
                @error('current_amount')
                    <div class="error-msg">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                {{ $savingsGoal ? 'Update Savings Goal' : 'Create Savings Goal' }}
            </button>
        </form>
    </div>
</x-app-layout>
