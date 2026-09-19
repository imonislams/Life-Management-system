@php
use App\Models\Setting;
@endphp
<x-app-layout>
    <x-slot name="title">Expense Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Expense Settings</h2>
        <p class="card-subtitle">Display and limit preferences for your expense records.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.finance.update') }}">
            @csrf
            @method('PUT')

            @include('settings.partials.finance-hidden', ['currentSection' => 'expense'])

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="expense_currency_code" class="form-label">Default Expense Currency <span style="color: var(--danger-color);">*</span></label>
                    <input id="expense_currency_code" type="text" name="expense_currency_code" value="{{ old('expense_currency_code', $settings->expense_currency_code) }}" class="form-control" maxlength="8" required>
                    @error('expense_currency_code')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="expense_display_format" class="form-label">Expense Display Format <span style="color: var(--danger-color);">*</span></label>
                    <select id="expense_display_format" name="expense_display_format" class="form-control" required>
                        @foreach(Setting::DISPLAY_FORMATS as $value => $label)
                        <option value="{{ $value }}" {{ old('expense_display_format', $settings->expense_display_format) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('expense_display_format')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="expense_monthly_limit" class="form-label">Monthly Expense Limit <span style="color: var(--danger-color);">*</span></label>
                <input id="expense_monthly_limit" type="number" step="0.01" min="0" name="expense_monthly_limit" value="{{ old('expense_monthly_limit', (float) $settings->expense_monthly_limit) }}" class="form-control" required>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Set to 0 to disable the monthly limit.</div>
                @error('expense_monthly_limit')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="expense_warning_enabled" value="1" {{ old('expense_warning_enabled', $settings->expense_warning_enabled) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Show a warning when monthly expenses approach the limit</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Expense Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>