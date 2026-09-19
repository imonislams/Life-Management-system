@php
use App\Models\Setting;
@endphp
<x-app-layout>
    <x-slot name="title">Savings Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Savings Settings</h2>
        <p class="card-subtitle">These preferences complement your existing Savings goal.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.finance.update') }}">
            @csrf
            @method('PUT')

            @include('settings.partials.finance-hidden', ['currentSection' => 'savings'])

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="savings_currency_code" class="form-label">Default Savings Currency <span style="color: var(--danger-color);">*</span></label>
                    <input id="savings_currency_code" type="text" name="savings_currency_code" value="{{ old('savings_currency_code', $settings->savings_currency_code) }}" class="form-control" maxlength="8" required>
                    @error('savings_currency_code')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="savings_monthly_target" class="form-label">Monthly Savings Target <span style="color: var(--danger-color);">*</span></label>
                    <input id="savings_monthly_target" type="number" step="0.01" min="0" name="savings_monthly_target" value="{{ old('savings_monthly_target', (float) $settings->savings_monthly_target) }}" class="form-control" required>
                    @error('savings_monthly_target')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="savings_default_goal" class="form-label">Default Savings Goal Name</label>
                <input id="savings_default_goal" type="text" name="savings_default_goal" value="{{ old('savings_default_goal', $settings->savings_default_goal) }}" class="form-control" placeholder="e.g. Emergency fund">
                @error('savings_default_goal')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="savings_progress_display" class="form-label">Savings Progress Display <span style="color: var(--danger-color);">*</span></label>
                    <select id="savings_progress_display" name="savings_progress_display" class="form-control" required>
                        @foreach(Setting::SAVINGS_PROGRESS_DISPLAY as $value => $label)
                        <option value="{{ $value }}" {{ old('savings_progress_display', $settings->savings_progress_display) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('savings_progress_display')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="savings_transaction_display" class="form-label">Contribution / Withdrawal Display <span style="color: var(--danger-color);">*</span></label>
                    <select id="savings_transaction_display" name="savings_transaction_display" class="form-control" required>
                        @foreach(Setting::SAVINGS_TRANSACTION_DISPLAY as $value => $label)
                        <option value="{{ $value }}" {{ old('savings_transaction_display', $settings->savings_transaction_display) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('savings_transaction_display')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="savings_reminder_enabled" value="1" {{ old('savings_reminder_enabled', $settings->savings_reminder_enabled) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Enable savings reminder preference (saved preference only — nothing is sent automatically)</span>
                </label>
            </div>

            <div class="form-group">
                <label for="savings_reminder_date" class="form-label">Savings Reminder Date</label>
                <input id="savings_reminder_date" type="date" name="savings_reminder_date" value="{{ old('savings_reminder_date', optional($settings->savings_reminder_date)->format('Y-m-d')) }}" class="form-control">
                @error('savings_reminder_date')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Savings Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>