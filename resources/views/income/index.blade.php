<x-app-layout>
    <x-slot name="title">Income - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Income</x-slot>
    @if (session('status'))
        <div class="alert-success">
            {{ session('status') }}
        </div>
    @endif
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">Income</h1>
                <p class="card-subtitle">Manage your income records.</p>
            </div>
            <div>
                <a href="{{ route('income.create') }}" class="btn-primary">+ Add Income</a>
            </div>
        </div>

        <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin: 0.5rem 0 1rem;">
            <div class="summary-card">
                <div class="summary-card-title">Filtered Total</div>
                <div class="summary-card-value income-color"><x-money :amount="$totalAmount" /></div>
                <div style="font-size:0.75rem;color:#94a3b8;">All-time income (grouped per currency on the Analytics page)</div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('income.index') }}" class="filter-bar">
            <div class="filter-group">
                <label for="search" class="filter-label">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Description, source, category">
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
                @if(request('month') || request('date') || request('search'))
                    <a href="{{ route('income.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.875rem;">Clear</a>
                @endif
            </div>
        </form>

        <!-- Income Table -->
        @if ($incomeRecords->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Currency</th>
                            <th>Source / Category</th>
                            <th>Description</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incomeRecords as $income)
                            <tr>
                                <td>{{ user_date($income->date) }}</td>
                                <td class="text-amount-income"><x-money :amount="$income->amount" :currency="$income->currency" /></td>
                                <td>
                                    @if($income->currency)
                                        <span class="badge badge-active">{{ $income->currency->code }}</span>
                                    @elseif($income->currency_code)
                                        <span class="badge badge-inactive">{{ $income->currency_code }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">—</span>
                                    @endif
                                </td>
                                <td style="font-size:0.85rem;">
                                    {{ $income->source ?? '—' }}
                                    @if($income->category)
                                        <span class="badge badge-inactive" style="margin-left:0.25rem;">{{ $income->category }}</span>
                                    @endif
                                </td>
                                <td>{{ $income->description ?? '-' }}</td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('income.edit', $income) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('income.destroy', $income) }}"
                                              data-confirm
                                              data-confirm-title="Delete income record?"
                                              data-confirm-message="Are you sure you want to delete this income record? This action cannot be undone."
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
                {{ $incomeRecords->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-state-title">No income records found.</div>
                <p>Add your first income record to get started.</p>
            </div>
        @endif
    </div>
</x-app-layout>
