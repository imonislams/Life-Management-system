@php
use App\Models\Currency;
@endphp
<x-app-layout>
    <x-slot name="title">Add Currency - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Add Currency</h2>
                <p class="card-subtitle">Create a currency with its own symbol, precision and separators.</p>
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

    <!-- Preset picker -->
    @if($catalogSuggestions->count() > 0)
    <div class="card">
        <div class="card-header-flex" style="margin-bottom: 0.5rem;">
            <div>
                <h3 class="card-title" style="font-size: 1rem;">Start From a Common Currency</h3>
                <p class="card-subtitle">Selecting one fills the form below; you can still adjust every field.</p>
            </div>
        </div>

        <select id="catalogPicker" class="form-control">
            <option value="">— Choose a currency (optional) —</option>
            @foreach($catalogSuggestions as $suggestion)
            <option
                value="{{ $suggestion['code'] }}"
                data-name="{{ $suggestion['name'] }}"
                data-symbol="{{ $suggestion['symbol'] }}"
                data-country="{{ $suggestion['country'] }}"
                data-precision="{{ $suggestion['precision'] }}">{{ $suggestion['code'] }} — {{ $suggestion['name'] }} ({{ $suggestion['symbol'] }})</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="card" style="max-width: 780px;">
        <form method="POST" action="{{ route('settings.currencies.store') }}">
            @csrf

            @include('settings.currencies.partials.form', ['currency' => null])

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Add Currency</button>
                <a href="{{ route('settings.currencies.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const picker = document.getElementById('catalogPicker');
            if (!picker) return;

            picker.addEventListener('change', function() {
                const option = picker.options[picker.selectedIndex];
                if (!option || !option.value) return;

                document.getElementById('code').value = option.value;
                document.getElementById('name').value = option.dataset.name || '';
                document.getElementById('symbol').value = option.dataset.symbol || '';
                document.getElementById('country').value = option.dataset.country || '';
                document.getElementById('decimal_precision').value = option.dataset.precision || '2';

                // Trigger the live preview refresh.
                document.getElementById('symbol').dispatchEvent(new Event('input'));
            });
        });
    </script>
</x-app-layout>