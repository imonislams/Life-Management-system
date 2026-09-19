@php
use App\Models\Currency;
@endphp
<x-app-layout>
    <x-slot name="title">Currencies - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

        @if (session('status'))
            <div class="alert-success">
                {{ session('status') }}
            </div>
        @endif

    <!-- Page header -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Currency Management</h2>
                <p class="card-subtitle">Add and manage currencies from anywhere in the world. Each currency keeps its own formatting rules.</p>
            </div>
            <a href="{{ route('settings.currencies.create') }}" class="btn-primary">+ Add Currency</a>
        </div>
    </div>

@if($errors->any())
    <div class="alert-danger">
        @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    <div class="card" style="padding: 0.75rem;">
        <div style="display: flex; flex-wrap: wrap; gap: 0.375rem;">
            <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.75rem; text-decoration: none;">⚙️ Overview</a>
            <a href="{{ route('settings.currency') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.75rem; text-decoration: none;">💱 Currency Settings</a>
            <a href="{{ route('settings.currencies.index') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.75rem; text-decoration: none; background-color: #eff6ff; color: var(--primary-color); border-color: #bfdbfe; font-weight: 600;">🌍 Currencies</a>
            <a href="{{ route('settings.general') }}" class="btn-secondary btn-sm" style="padding: 0.5rem 0.75rem; text-decoration: none;">🏷️ General</a>
        </div>
    </div>

    <!-- Current default currency summary -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Default Currency</div>
            <div class="summary-card-value" style="font-size: 1.25rem;">
                @if($defaultCurrency)
                {{ $defaultCurrency->symbol }} {{ $defaultCurrency->code }}
                @else
                <span style="color: var(--text-muted); font-size: 1rem;">Not set</span>
                @endif
            </div>
            @if($defaultCurrency)
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                {{ $defaultCurrency->name }}
            </div>
            @endif
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Formatting Preview</div>
            <div class="summary-card-value" style="font-size: 1.25rem;">
                {{ $defaultCurrency ? $defaultCurrency->preview(1234.5) : '—' }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                Large: {{ $defaultCurrency ? $defaultCurrency->preview(1234567.89) : '—' }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Active Currencies</div>
            <div class="summary-card-value income-color">{{ $activeCurrencies }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">of {{ $totalCurrencies }} total</div>
        </div>

        <div class="summary-card">
            <div class="summary-card-title">Inactive Currencies</div>
            <div class="summary-card-value" style="color: #64748b;">{{ $inactiveCurrencies }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Hidden from new records</div>
        </div>
    </div>

    <!-- Quick add from world catalog -->
    @if($catalogSuggestions->count() > 0)
    <div class="card">
        <div class="card-header-flex" style="margin-bottom: 0.5rem;">
            <div>
                <h3 class="card-title" style="font-size: 1.05rem;">Quick Add World Currencies</h3>
                <p class="card-subtitle">One click adds the currency with its standard symbol and precision.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('settings.currencies.catalog') }}">
            @csrf
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; max-height: 190px; overflow-y: auto; padding: 0.25rem;">
                @foreach($catalogSuggestions as $suggestion)
                <button
                    type="submit"
                    name="code"
                    value="{{ $suggestion['code'] }}"
                    class="btn-secondary btn-sm"
                    style="padding: 0.4rem 0.65rem; cursor: pointer;"
                    title="{{ $suggestion['name'] }} ({{ $suggestion['country'] }})">
                    {{ $suggestion['symbol'] }} {{ $suggestion['code'] }}
                </button>
                @endforeach
            </div>
        </form>
    </div>
    @endif

    <!-- Search and filter -->
    <div class="card" style="padding: 1rem;">
        <form method="GET" action="{{ route('settings.currencies.index') }}" class="filter-bar" style="margin-bottom: 0;">
            <div class="filter-group" style="flex: 1; min-width: 200px;">
                <label for="search" class="filter-label">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, code, symbol or country...">
            </div>

            <div class="filter-group">
                <label for="status" class="filter-label">Status</label>
                <select name="status" id="status" class="form-control" onchange="this.form.submit()">
                    <option value="">All Currencies</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <div class="filter-group">
                <button type="submit" class="btn-primary btn-sm" style="padding: 0.625rem 0.75rem;">Search</button>
            </div>

            @if(request()->hasAny(['search', 'status']))
            <div class="filter-group">
                <a href="{{ route('settings.currencies.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 0.75rem;">Reset</a>
            </div>
            @endif
        </form>
    </div>

    <!-- Currency table -->
    <div class="card">
        @if($currencies->count() > 0)
        <div class="data-table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Precision</th>
                        <th>Formatting Preview</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currencies as $currency)
                    @php
                    $usage = $currency->income_records_count
                    + $currency->expense_records_count
                    + $currency->salaries_count
                    + $currency->savings_goals_count
                    + $currency->recurring_transactions_count;
                    @endphp
                    <tr style="{{ $currency->is_active ? '' : 'opacity: 0.65;' }}">
                        <td>
                            <span style="font-weight: 700;">{{ $currency->code }}</span>
                            @if($currency->is_default)
                            <span class="badge badge-income" style="margin-left: 0.35rem;">Default</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 600;">{{ $currency->name }}</div>
                            @if($currency->country)
                            <div style="font-size: 0.75rem; color: #64748b;">{{ $currency->country }}</div>
                            @endif
                            @if($usage > 0)
                            <div style="font-size: 0.7rem; color: #64748b;">{{ $usage }} record(s) use this</div>
                            @endif
                        </td>
                        <td style="font-size: 1.05rem; font-weight: 600;">{{ $currency->symbol }}</td>
                        <td>{{ $currency->decimal_precision }}</td>
                        <td style="font-weight: 600; white-space: nowrap;">
                            {{ $currency->preview(1234.5) }}
                        </td>
                        <td>
                            @if($currency->is_active)
                            <span class="badge badge-income">Active</span>
                            @else
                            <span class="badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons" style="justify-content: flex-end; flex-wrap: wrap;">
                                @unless($currency->is_default)
                                <form method="POST" action="{{ route('settings.currencies.default', $currency) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-sm btn-secondary" title="Make this the default currency">Set Default</button>
                                </form>
                                @endunless

                                <form method="POST" action="{{ route('settings.currencies.toggle', $currency) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-sm btn-secondary">
                                        {{ $currency->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>

                                <a href="{{ route('settings.currencies.edit', $currency) }}" class="btn-sm btn-secondary">Edit</a>

                                @if($usage === 0 && ! $currency->is_default)
                                <form method="POST" action="{{ route('settings.currencies.destroy', $currency) }}"
                                      data-confirm
                                      data-confirm-title="Delete {{ $currency->code }}?"
                                      data-confirm-message="Are you sure you want to delete {{ $currency->code }}? This cannot be undone."
                                      data-confirm-action="Delete">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                                </form>
                                @else
                                <button type="button" class="btn-sm btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;" title="{{ $currency->is_default ? 'The default currency cannot be deleted.' : 'Used by existing financial records — deactivate instead.' }}">
                                    Delete
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $currencies->links() }}
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-title">No currencies found</div>
            <p style="font-size: 0.875rem; margin-bottom: 1rem;">
                @if(request()->hasAny(['search', 'status']))
                Try a different search or reset the filters.
                @else
                Add your first currency to start formatting amounts across the application.
                @endif
            </p>
            <a href="{{ route('settings.currencies.create') }}" class="btn-primary btn-sm" style="padding: 0.5rem 1rem;">+ Add Currency</a>
        </div>
        @endif
    </div>
</x-app-layout>