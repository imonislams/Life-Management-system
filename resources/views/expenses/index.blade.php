<x-app-layout>
    <x-slot name="title">Expenses - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Expenses</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

<div class="card">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">Expenses</h1>
                <p class="card-subtitle">Manage your daily expenses.</p>
            </div>
            <div>
                <a href="{{ route('expenses.create') }}" class="btn-primary">+ Add Expense</a>
            </div>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin: 0.5rem 0 1rem;">
            <div class="summary-card">
                <div class="summary-card-title">Total Expenses</div>
                <div class="summary-card-value text-amount-expense"><x-money :amount="$totalAmount" /></div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('expenses.index') }}" class="filter-bar">
            <div class="filter-group">
                <label for="search" class="filter-label">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Description">
            </div>

            <div class="filter-group">
                <label for="month" class="filter-label">Filter by Month</label>
                <input
                    type="month"
                    id="month"
                    name="month"
                    value="{{ request('month') }}"
                    class="form-control"
                    style="width: 170px;"
                >
            </div>

            <div class="filter-group">
                <label for="date" class="filter-label">Filter by Date</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    value="{{ request('date') }}"
                    class="form-control"
                    style="width: 170px;"
                >
            </div>

            <div class="filter-group" style="flex-direction: row; gap: 0.5rem; align-items: flex-end;">
                <button type="submit" class="btn-primary" style="padding: 0.625rem 0.875rem;">Filter</button>
                @if(request('month') || request('date'))
                    <a href="{{ route('expenses.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.875rem;">Clear</a>
                @endif
            </div>
        </form>

        <!-- Expense Table -->
        @if ($expenseRecords->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Description</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenseRecords as $expense)
                            <tr>
                                <td>{{ user_date($expense->date) }}</td>
                                <td class="text-amount-expense"><x-money :amount="$expense->amount" :currency="$expense->currency" /></td>
                                <td>
                                    @if($expense->currency)
                                        <span class="badge badge-active">{{ $expense->currency->code }}</span>
                                    @elseif($expense->currency_code)
                                        <span class="badge badge-inactive">{{ $expense->currency_code }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">—</span>
                                    @endif
                                </td>
                                <td>{{ $expense->description ?? '-' }}</td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('expenses.edit', $expense) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                              data-confirm
                                              data-confirm-title="Delete expense record?"
                                              data-confirm-message="Are you sure you want to delete this expense record? This action cannot be undone."
                                              data-confirm-action="Delete">
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
                {{ $expenseRecords->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No expense records found.</div>
                <p>Add your first expense record to get started.</p>
            </div>
        @endif
    </div>
</x-app-layout>
