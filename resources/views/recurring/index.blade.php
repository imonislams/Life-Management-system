<x-app-layout>
    <x-slot name="title">Recurring Finance - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Recurring Finance</x-slot>

    @if (session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">Recurring Finance</h1>
                <p class="card-subtitle">Manage your regular income and expenses.</p>
            </div>
            <div>
                <a href="{{ route('recurring-transactions.create') }}" class="btn-primary">+ Add Recurring Record</a>
            </div>
        </div>

        @if ($recurringTransactions->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Recurrence</th>
                            <th>Next Due Date</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recurringTransactions as $record)
                            <tr>
                                <td>
                                    @if ($record->type === 'income')
                                        <span class="badge badge-income">Income</span>
                                    @else
                                        <span class="badge badge-expense">Expense</span>
                                    @endif
                                </td>
                                <td><strong>{{ $record->title }}</strong></td>
                                <td class="{{ $record->type === 'income' ? 'text-amount-income' : 'text-amount-expense' }}">
                                    ৳ {{ number_format($record->amount, 2) }} TK
                                </td>
                                <td style="text-transform: capitalize;">{{ $record->recurrence_type }}</td>
                                <td>{{ \Carbon\Carbon::parse($record->next_due_date)->format('M d, Y') }}</td>
                                <td>
                                    @if ($record->is_active)
                                        <span class="badge badge-active">Active</span>
                                    @else
                                        <span class="badge badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('recurring-transactions.edit', $record) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('recurring-transactions.destroy', $record) }}" onsubmit="return confirm('Are you sure you want to delete this recurring record?');">
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
                {{ $recurringTransactions->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No recurring records found.</div>
                <p>Add your first recurring income or expense record to get started.</p>
            </div>
        @endif
    </div>
</x-app-layout>
