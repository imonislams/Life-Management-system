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

        <form method="GET" action="{{ route('recurring-transactions.index') }}" class="filter-bar" style="margin: 1rem 0;">
            <div class="filter-group">
                <label for="search" class="filter-label">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Title">
            </div>
            <div class="filter-group">
                <label for="type" class="filter-label">Type</label>
                <select name="type" id="type" class="form-control" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="status" class="filter-label">Status</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="filter-group">
                <button type="submit" class="btn-primary btn-sm" style="padding:0.625rem 1rem;">Filter</button>
            </div>
            @if(request()->hasAny(['search', 'type', 'status']))
                <div class="filter-group">
                    <a href="{{ route('recurring-transactions.index') }}" class="btn-secondary btn-sm" style="padding:0.625rem 0.75rem;">Reset</a>
                </div>
            @endif
        </form>

        @if ($recurringTransactions->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Currency</th>
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
                                    <x-money :amount="$record->amount" :currency="$record->currency" />
                                </td>
                                <td>
                                    @if($record->currency)
                                        <span class="badge badge-active">{{ $record->currency->code }}</span>
                                    @elseif($record->currency_code)
                                        <span class="badge badge-inactive">{{ $record->currency_code }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">—</span>
                                    @endif
                                </td>
                                <td style="text-transform: capitalize;">
                                    {{ $record->recurrence_type }}
                                    @if($record->recurrence_type === 'custom' && $record->interval_days)
                                        (every {{ $record->interval_days }} days)
                                    @endif
                                </td>
                                <td>{{ user_date($record->next_due_date) }}</td>
                                <td>
                                    @php $effStatus = $record->effectiveStatus(); @endphp
                                    @if ($effStatus === 'active')
                                        <span class="badge badge-active">Active</span>
                                    @elseif ($effStatus === 'paused')
                                        <span class="badge badge-inactive">Paused</span>
                                    @else
                                        <span class="badge badge-income">Completed</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('recurring-transactions.edit', $record) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('recurring-transactions.destroy', $record) }}"
                                              data-confirm
                                              data-confirm-title="Delete recurring record?"
                                              data-confirm-message="Are you sure you want to delete this recurring record? This action cannot be undone."
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
