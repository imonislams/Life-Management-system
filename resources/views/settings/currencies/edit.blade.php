@php
$usageCount = $currency->usageCount();
@endphp
<x-app-layout>
    <x-slot name="title">Edit {{ $currency->code }} - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Edit {{ $currency->code }} — {{ $currency->name }}</h2>
                <p class="card-subtitle">Update this currency's formatting rules.</p>
            </div>
            <a href="{{ route('settings.currencies.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Currencies</a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert-danger">
        @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
    @endif

    @if($usageCount > 0)
    <div class="alert-success" style="background-color: #eff6ff; border-color: #bfdbfe; color: #1e40af;">
        <strong>{{ $usageCount }}</strong> existing financial record(s) use this currency.
        Changing the symbol, precision or separators only affects how amounts are displayed —
        no stored amount is ever converted or modified.
    </div>
    @endif

    <div class="card" style="max-width: 780px;">
        <form method="POST" action="{{ route('settings.currencies.update', $currency) }}">
            @csrf
            @method('PUT')

            @include('settings.currencies.partials.form', ['currency' => $currency])

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('settings.currencies.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>

    @unless($currency->is_default)
    <div class="card" style="max-width: 780px;">
        <h3 class="card-title" style="font-size: 1rem; margin-bottom: 0.5rem;">Danger Zone</h3>

        @if($usageCount > 0)
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.75rem;">
            This currency is referenced by {{ $usageCount }} financial record(s), so it cannot be deleted.
            Deactivate it instead to hide it from new record forms while keeping existing records intact.
        </p>
        <button type="button" class="btn-sm btn-secondary" disabled style="opacity: 0.5; cursor: not-allowed;">Delete Currency</button>
        @else
        <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 0.75rem;">
            This currency is not used by any financial record and can be safely deleted.
        </p>
        <form method="POST" action="{{ route('settings.currencies.destroy', $currency) }}"
              data-confirm
              data-confirm-title="Permanently delete {{ $currency->code }}?"
              data-confirm-message="Are you sure you want to permanently delete {{ $currency->code }}? This action cannot be undone."
              data-confirm-action="Delete">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-sm btn-danger-sm">Delete Currency</button>
        </form>
        @endif
    </div>
    @endunless
</x-app-layout>