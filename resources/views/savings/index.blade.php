<x-app-layout>
    <x-slot name="title">Savings - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Savings</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

@if ($errors->any())
        <div class="alert-danger">
            <ul style="margin: 0; padding-left: 1.1rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">Savings</h1>
                <p class="card-subtitle">Track your savings accounts, deposits and withdrawals.</p>
            </div>
            <a href="{{ route('savings.goals.create') }}" class="btn-primary">+ Add New Saving Goal</a>
        </div>
    </div>

    <!-- Per-currency totals: unlike currencies are never added together. -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Total Deposits</div>
            <div class="summary-card-value income-color"><x-money :amount="$totalDeposits" /></div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Total Withdrawals</div>
            <div class="summary-card-value" style="color:#dc2626;"><x-money :amount="$totalWithdrawals" /></div>
        </div>
        @foreach($totalsByCurrency as $total)
            <div class="summary-card">
                <div class="summary-card-title">Balance ({{ $total['code'] }})</div>
                <div class="summary-card-value balance-color">
                    {{ number_format($total['total'], 2) }} <span style="font-size:0.75rem; color:#94a3b8;">{{ $total['code'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add New Saving (deposit / withdrawal) -->
    @if($goals->count() > 0)
        <div class="card" style="margin-bottom: 1.5rem;">
            <h2 class="card-title">Add New Saving</h2>
            <p class="card-subtitle" style="margin-bottom: 1rem;">Record a deposit into, or a withdrawal from, any of your savings goals.</p>

            <form method="POST" action="{{ route('savings.transactions.store') }}" style="max-width: 720px;">
                @csrf
                <div class="form-row" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                    <div class="form-group">
                        <label for="savings_goal_id" class="form-label">Savings Goal</label>
                        <select id="savings_goal_id" name="savings_goal_id" class="form-control" required>
                            @foreach($goals as $goalOption)
                                <option value="{{ $goalOption->id }}" {{ old('savings_goal_id', $selectedGoalId) == $goalOption->id ? 'selected' : '' }}>
                                    {{ $goalOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="txn_type" class="form-label">Type</label>
                        <select id="txn_type" name="type" class="form-control" required>
                            <option value="deposit" {{ old('type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                            <option value="withdrawal" {{ old('type') === 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="txn_amount" class="form-label">Amount</label>
                        <input id="txn_amount" type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required placeholder="e.g. 5000.00">
                    </div>

                    <div class="form-group">
                        <label for="txn_date" class="form-label">Date</label>
                        <input id="txn_date" type="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}" required>
                    </div>
                </div>

                @include('partials.currency-select', [
                    'currencies' => $currencies,
                    'defaultCurrency' => $defaultCurrency,
                    'selectedId' => null,
                ])

                <div class="form-group">
                    <label for="txn_note" class="form-label">Note</label>
                    <input id="txn_note" type="text" name="note" class="form-control" value="{{ old('note') }}" placeholder="Optional note">
                </div>

                <button type="submit" class="btn-primary">Record Saving</button>
            </form>
        </div>
    @endif
    <!-- Savings Goals -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <h2 class="card-title">Savings Goals</h2>
        @if($goals->count() > 0)
            <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
                @foreach($goals as $goal)
                    <div class="summary-card" style="text-align:left;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:0.5rem;">
                            <div>
                                <div class="summary-card-title">{{ $goal->name }}</div>
                                <div style="font-size:1.35rem; font-weight:700; color:var(--primary-color);">
                                    <x-money :amount="$goal->current_amount" :currency="$goal->currency" />
                                </div>
                                <div style="font-size:0.8rem; color:#94a3b8;">of <x-money :amount="$goal->target_amount" :currency="$goal->currency" /></div>
                            </div>
                            <span class="badge {{ $goal->status === 'completed' ? 'badge-income' : ($goal->status === 'paused' ? 'badge-inactive' : 'badge-active') }}">
                                {{ ucfirst($goal->status) }}
                            </span>
                        </div>

                        <div style="margin-top:0.75rem;">
                            <div style="display:flex; justify-content:space-between; font-size:0.75rem; font-weight:600; margin-bottom:0.25rem;">
                                <span>{{ $goal->progressPercentage() }}%</span>
                                <span>Remaining <x-money :amount="$goal->remainingAmount()" :currency="$goal->currency" /></span>
                            </div>
                            <div style="background-color:#e2e8f0; border-radius:0.375rem; height:10px; overflow:hidden;">
                                <div style="width: {{ $goal->progressPercentage() }}%; background-color: var(--primary-color); height:100%;"></div>
                            </div>
                        </div>

                        <div class="action-buttons" style="margin-top:0.75rem;">
                            <a href="{{ route('savings.goals.show', $goal) }}" class="btn-sm btn-secondary">Details</a>
                            <a href="{{ route('savings.goals.edit', $goal) }}" class="btn-sm btn-secondary">Edit</a>
                            <form method="POST" action="{{ route('savings.goals.destroy', $goal) }}"
                                  data-confirm
                                  data-confirm-title="Delete savings goal?"
                                  data-confirm-message="This deletes the goal and all of its transactions. This action cannot be undone."
                                  data-confirm-action="Delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No savings goals yet</div>
                <p style="font-size:0.875rem; margin-bottom:1rem;">Create your first savings goal to start tracking deposits and withdrawals.</p>
                <a href="{{ route('savings.goals.create') }}" class="btn-primary btn-sm" style="padding:0.5rem 1rem;">+ Add New Saving Goal</a>
            </div>
        @endif
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
                            <th>Goal</th>
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
                                    @if($txn->goal)
                                        <a href="{{ route('savings.goals.show', $txn->goal) }}" style="color:var(--text-main); text-decoration:none; font-weight:600;">{{ $txn->goal->name }}</a>
                                    @else
                                        <span style="color:#94a3b8;">—</span>
                                    @endif
                                </td>
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
                                <td style="font-size:0.85rem; color:#64748b;">{{ \Illuminate\Support\Str::limit($txn->note, 40) ?: '—' }}</td>
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
                <p style="font-size:0.875rem;">Deposits and withdrawals you record will appear here.</p>
            </div>
        @endif
    </div>

    </x-app-layout>
