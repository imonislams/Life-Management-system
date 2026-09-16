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

        <!-- Filter Form -->
        <form method="GET" action="{{ route('income.index') }}" class="filter-bar">
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
                            <th>Description</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incomeRecords as $income)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($income->date)->format('M d, Y') }}</td>
                                <td class="text-amount-income">৳ {{ number_format($income->amount, 2) }} TK</td>
                                <td>{{ $income->description ?? '-' }}</td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('income.edit', $income) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('income.destroy', $income) }}" onsubmit="return confirm('Are you sure you want to delete this income record?');">
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
