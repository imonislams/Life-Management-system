@php
use App\Models\SavingsTransaction;
@endphp
<x-app-layout>
    <x-slot name="title">{{ $goal->name }} - Savings - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Savings</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

@if($errors->any())
    <div class="alert-danger">
        <ul style="margin:0; padding-left:1.1rem;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">{{ $goal->name }}</h1>
                <p class="card-subtitle">{{ $goal->description ?: 'Savings goal details and transaction history.' }}</p>
            </div>
            <div class="action-buttons">
                <a href="{{ route('savings.goals.edit', $goal) }}" class="btn-secondary btn-sm">Edit Goal</a>
                <a href="{{ route('savings.index') }}" class="btn-secondary btn-sm">Back to Savings</a>
            </div>
        </div>
    </div>

    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Current Balance</div>
            <div class="summary-card-value balance-color"><x-money :amount="$goal->current_amount" :currency="$goal->currency" /></div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Target</div>
            <div class="summary-card-value"><x-money :amount="$goal->target_amount" :currency="$goal->currency" /></div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Total Deposits</div>
            <div class="summary-card-value income-color"><x-money :amount="$totalDeposits" :currency="$goal->currency" /></div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Total Withdrawals</div>
            <div class="summary-card-value" style="color:#dc2626;"><x-money :amount="$totalWithdrawals" :currency="$goal->currency" /></div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="display:flex; justify-content:space-between; font-size:0.85rem; font-weight:600; margin-bottom:0.35rem;">
            <span>Progress: {{ $goal->progressPercentage() }}%</span>
            <span>Remaining: <x-money :amount="$goal->remainingAmount()" :currency="$goal->currency" /></span>
        </div>
        <div style="background-color:#e2e8f0; border-radius:0.375rem; height:12px; overflow:hidden;">
            <div style="width: {{ $goal->progressPercentage() }}%; background-color: var(--primary-color); height:100%;"></div>
        </div>
        <div style="margin-top:0.75rem; font-size:0.8rem; color:#94a3b8;">
            @if($goal->start_date) Started {{ user_date($goal->start_date) }}@endif
            @if($goal->target_date) · Target {{ user_date($goal->target_date) }} @endif
            · Status: <span class="badge {{ $goal->status === 'completed' ? 'badge-income' : ($goal->status === 'paused' ? 'badge-inactive' : 'badge-active') }}">{{ ucfirst($goal->status) }}</span>
        </div>
    </div>

    <!-- Add New Saving -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 class="card-title">Add New Saving</h2>
        <p class="card-subtitle" style="margin-bottom:1rem;">Record a deposit or withdrawal for this goal.</p>

        <form method="POST" action="{{ route('savings.transactions.store') }}">
            @csrf
            <input type="hidden" name="savings_goal_id" value="{{ $goal->id }}">

            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
                <div class="form-group">
                    <label for="type" class="form-label">Type</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="deposit" {{ old('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                        <option value="withdrawal" {{ old('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount" class="form-label">Amount</label>
                    <input id="amount" type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" required class="form-control" placeholder="e.g. 5000.00">
                </div>

                <div class="form-group">
                    <label for="date" class="form-label">Date</label>
                    <input id="date" type="date" name="date" value="{{ old('date', now()->toDateString()) }}" required class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label for="note" class="form-label">Note (Optional)</label>
                <input id="note" type="text" name="note" value="{{ old('note') }}" class="form-control" placeholder="Optional note">
            </div>

            <button type="submit" class="btn-primary">Record Saving</button>
        </form>
    </div>

    <!-- Transaction History -->
    <div class="card">
        <h2 class="card-title">Transaction History</h2>
        @if($transactions->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th style="text-align:right;">Amount</th>
                        <th>Note</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $txn)
                    <tr>
                        <td>{{ user_date($txn->date) }}</td>
                        <td>
                            @if($txn->type === 'withdrawal')
                            <span class="badge badge-expense">Withdrawal</span>
                            @else
                            <span class="badge badge-income">Deposit</span>
                            @endif
                        </td>
                        <td style="text-align:right; font-weight:600; {{ $txn->type === 'withdrawal' ? 'color:#dc2626;' : 'color:#16a34a;' }}">
                            {{ $txn->type === 'withdrawal' ? '-' : '+' }}<x-money :amount="$txn->amount" :currency="$txn->currency" />
                        </td>
                        <td style="font-size:0.85rem; color:#64748b;">{{ \Illuminate\Support\Str::limit($txn->note, 50) ?: '—' }}</td>
                        <td>
                            <div class="action-buttons" style="justify-content:flex-end;">
                                <a href="{{ route('savings.transactions.edit', $txn) }}" class="btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('savings.transactions.destroy', $txn) }}"
                                      data-confirm
                                      data-confirm-title="Delete transaction?"
                                      data-confirm-message="Are you sure you want to delete this transaction? This action cannot be undone."
                                      data-confirm-action="Delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-wrapper">{{ $transactions->links() }}</div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No transactions yet</div>
            <p style="font-size:0.875rem;">Deposits and withdrawals for this goal will appear here.</p>
        </div>
        @endif
    </div>
</x-app-layout>