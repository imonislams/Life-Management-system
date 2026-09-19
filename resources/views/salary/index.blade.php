<x-app-layout>
    <x-slot name="title">Salary Management - Personal Finance Management System</x-slot>
    <x-slot name="pageTitle">Salary Management</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

<div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header-flex">
            <div>
                <h1 class="card-title">Salary Management</h1>
                <p class="card-subtitle">Manage your fixed monthly salary.</p>
            </div>
            <div>
                <a href="{{ route('salary.create') }}" class="btn-primary">+ Add Salary</a>
            </div>
        </div>

        <!-- Summary Grid -->
        <div class="dashboard-grid-5" style="margin-top: 1rem; margin-bottom: 0;">
            <div class="summary-card">
                <div class="summary-card-title">Monthly Salary</div>
                <div class="summary-card-value balance-color">
                    <x-money :amount="$totalActiveSalary" />
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-title">Payment Day</div>
                <div class="summary-card-value" style="font-size: 1.125rem;">
                    @if ($activeSalaryRecord && $activeSalaryRecord->payment_day)
                        Every month on the {{ $activeSalaryRecord->payment_day }}{{ ordinal_suffix($activeSalaryRecord->payment_day) }}
                    @else
                        Not Set
                    @endif
                </div>
            </div>

            <div class="summary-card">
                <div class="summary-card-title">Salary Status</div>
                <div class="summary-card-value" style="font-size: 1.125rem;">
                    @if ($activeSalaryRecord)
                        <span class="badge badge-active">Active</span>
                    @else
                        <span class="badge badge-inactive">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Records Table/List -->
    <div class="card">
        <h2 class="card-title" style="margin-bottom: 1rem;">Salary Records</h2>

        @if ($salaries->count() > 0)
            <div class="data-table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Monthly Salary</th>
                            <th>Currency</th>
                            <th>Payment Day</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($salaries as $record)
                            <tr>
                                <td class="text-amount-income"><x-money :amount="$record->amount" :currency="$record->currency" /></td>
                                <td>
                                    @if($record->currency)
                                        <span class="badge badge-active">{{ $record->currency->code }}</span>
                                    @elseif($record->currency_code)
                                        <span class="badge badge-inactive">{{ $record->currency_code }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic;">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($record->payment_day)
                                        Day {{ $record->payment_day }} of month
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $record->description ?? '-' }}</td>
                                <td>
                                    @if ($record->is_active)
                                        <span class="badge badge-active">Active</span>
                                    @else
                                        <span class="badge badge-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $record->created_at ? user_date($record->created_at) : '-' }}</td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="{{ route('salary.edit', $record) }}" class="btn-secondary btn-sm">Edit</a>
                                        <form method="POST" action="{{ route('salary.destroy', $record) }}"
                                              data-confirm
                                              data-confirm-title="Delete salary record?"
                                              data-confirm-message="Are you sure you want to delete this salary record? This action cannot be undone."
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
        @else
            <div class="empty-state">
                <div class="empty-state-title">No salary records found.</div>
                <p>Add your monthly salary record to get started.</p>
            </div>
        @endif
    </div>
</x-app-layout>

@php
function ordinal_suffix($num) {
    if (!in_array(($num % 100), array(11, 12, 13))) {
        switch ($num % 10) {
            case 1: return 'st';
            case 2: return 'nd';
            case 3: return 'rd';
        }
    }
    return 'th';
}
@endphp
