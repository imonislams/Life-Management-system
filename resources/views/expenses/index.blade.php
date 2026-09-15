<x-app-layout>
    <x-slot name="title">Expenses - Personal Life Management System</x-slot>
    <x-slot name="pageTitle">Expenses</x-slot>

    @if (session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">Expense Management</h1>
                <p class="card-subtitle">Track and organize your expense transactions</p>
            </div>
            <div>
                <a href="{{ route('expenses.create') }}" class="btn-primary">+ Add Expense</a>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('expenses.index') }}" class="filter-bar">
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
                            <th>Description</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenseRecords as $expense)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</td>
                                <td class="text-amount-expense">৳ {{ number_format($expense->amount, 2) }} TK</td>
                                <td>{{ $expense->description ?? '-' }}</td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('expenses.edit', $expense) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Are you sure you want to delete this expense record?');">
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
                <div class="empty-state-title">No expense records found</div>
                <p>Add your first expense record or adjust your filter parameters.</p>
            </div>
        @endif
    </div>
</x-app-layout>
