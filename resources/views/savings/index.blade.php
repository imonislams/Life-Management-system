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
                    <p class="card-subtitle">Target: {{ currency($savingsGoal->target_amount) }}</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);">
                        {{ currency($savingsGoal->current_amount) }}
                    </div>
                    <div style="font-size: 0.875rem; color: var(--text-muted);">Current Saved Amount</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div style="margin-top: 1rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.375rem; font-size: 0.875rem; font-weight: 600;">
                    <span>Progress: {{ $progressPercentage }}%</span>
                    <span>Remaining: {{ currency($remainingAmount) }}</span>
                </div>
                <div style="background-color: #e2e8f0; border-radius: 0.375rem; height: 12px; overflow: hidden;">
                    <div style="width: {{ $progressPercentage }}%; background-color: var(--primary-color); height: 100%; transition: width 0.3s;"></div>
                </div>
            </div>

            <!-- Savings Summary Grid -->
            <div class="dashboard-grid-5" style="margin-top: 1.5rem; margin-bottom: 0;">
                <div class="summary-card">
                    <div class="summary-card-title">Target Amount</div>
                    <div class="summary-card-value">{{ currency($savingsGoal->target_amount) }}</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-title">Current Saved</div>
                    <div class="summary-card-value" style="color: #2563eb;">{{ currency($savingsGoal->current_amount) }}</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-title">Remaining</div>
                    <div class="summary-card-value">{{ currency($remainingAmount) }}</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-title">Available Balance</div>
                    <div class="summary-card-value balance-color">{{ currency($availableBalance) }}</div>
                </div>
            </div>

            <!-- Delete Savings Goal -->
            <form method="POST" action="{{ route('savings.destroy') }}" style="margin-top: 1.5rem;" onsubmit="return confirm('Are you sure you want to delete your savings goal? This cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger-sm btn-sm" style="padding: 0.5rem 0.875rem;">Delete Savings Goal</button>
            </form>
        </div>

        <!-- Save Money Card -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header-flex">
                <div>
                    <h2 class="card-title">Save Money</h2>
                    <p class="card-subtitle">
                        Available Balance: <strong style="color: var(--primary-color);">{{ currency($availableBalance) }}</strong>
                    </p>
                </div>
            </div>

            <form id="saveMoneyForm" method="POST" action="{{ route('savings.records.store') }}" style="max-width: 600px;">
                @csrf
                <div class="form-group">
                    <label for="amount" class="form-label">Amount ({{ currency_label() }})</label>
                    <input
                        id="amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="amount"
                        value="{{ old('amount') }}"
                        required
                        class="form-control"
                        placeholder="e.g. 5000.00"
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
                        placeholder="e.g. Monthly savings deposit"
                    >
                    @error('description')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">Save Money</button>
            </form>
        </div>
    @else
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="empty-state">
                <div class="empty-state-title">No Savings Goal Set</div>
                <p>Set a goal name, target amount, and current saved amount below to begin tracking your savings.</p>
            </div>
        </div>

        <!-- Save Money Card (no goal yet) -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <h2 class="card-title">Save Money</h2>
            <p class="card-subtitle" style="margin-bottom: 1rem;">
                Available Balance: <strong style="color: var(--primary-color);">{{ currency($availableBalance) }}</strong>
            </p>

            <form id="saveMoneyForm" method="POST" action="{{ route('savings.records.store') }}" style="max-width: 600px;">
                @csrf
                <div class="form-group">
                    <label for="amount" class="form-label">Amount ({{ currency_label() }})</label>
                    <input
                        id="amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        name="amount"
                        value="{{ old('amount') }}"
                        required
                        class="form-control"
                        placeholder="e.g. 5000.00"
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
                        placeholder="e.g. Monthly savings deposit"
                    >
                    @error('description')
                        <div class="error-msg">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary">Save Money</button>
            </form>
        </div>
    @endif
    <!-- Savings History -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Savings History</h2>
                <p class="card-subtitle">All savings you have added. Total: <strong style="color: var(--primary-color);">{{ currency($totalSaved) }}</strong></p>
            </div>
        </div>

        @if ($savingsRecords->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($savingsRecords as $record)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($record->date)->format('M d, Y') }}</td>
                                <td class="text-amount-income">{{ currency($record->amount) }}</td>
                                <td>{{ $record->description ?? '-' }}</td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <form method="POST" action="{{ route('savings.records.destroy', $record) }}" onsubmit="return confirm('Are you sure you want to delete this savings record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger-sm btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $savingsRecords->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No savings entries found.</div>
                <p>Use the Save Money form above to add your first savings deposit.</p>
            </div>
        @endif
    </div>

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
                <label for="target_amount" class="form-label">Target Amount ({{ currency_label() }})</label>
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
                <label for="current_amount" class="form-label">Current Saved Amount ({{ currency_label() }})</label>
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
