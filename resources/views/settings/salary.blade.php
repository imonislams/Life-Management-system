@php
use App\Models\Setting;
@endphp
<x-app-layout>
    <x-slot name="title">Salary Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Salary Settings</h2>
        <p class="card-subtitle">Salary is tracked separately from Recurring Finance and is never counted twice.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.finance.update') }}">
            @csrf
            @method('PUT')

            @include('settings.partials.finance-hidden', ['currentSection' => 'salary'])

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="salary_currency_code" class="form-label">Default Salary Currency <span style="color: var(--danger-color);">*</span></label>
                    <input id="salary_currency_code" type="text" name="salary_currency_code" value="{{ old('salary_currency_code', $settings->salary_currency_code) }}" class="form-control" maxlength="8" required>
                    @error('salary_currency_code')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="salary_frequency" class="form-label">Payment Frequency <span style="color: var(--danger-color);">*</span></label>
                    <select id="salary_frequency" name="salary_frequency" class="form-control" required>
                        @foreach(Setting::SALARY_FREQUENCIES as $value => $label)
                        <option value="{{ $value }}" {{ old('salary_frequency', $settings->salary_frequency) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('salary_frequency')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="salary_payment_day" class="form-label">Default Payment Day (1-31)</label>
                    <input id="salary_payment_day" type="number" name="salary_payment_day" min="1" max="31" value="{{ old('salary_payment_day', $settings->salary_payment_day) }}" class="form-control">
                    @error('salary_payment_day')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="salary_display_format" class="form-label">Salary Display Format <span style="color: var(--danger-color);">*</span></label>
                    <select id="salary_display_format" name="salary_display_format" class="form-control" required>
                        @foreach(Setting::DISPLAY_FORMATS as $value => $label)
                        <option value="{{ $value }}" {{ old('salary_display_format', $settings->salary_display_format) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('salary_display_format')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="salary_reminder_enabled" value="1" {{ old('salary_reminder_enabled', $settings->salary_reminder_enabled) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Enable salary reminder preference (saved preference only — nothing is sent automatically)</span>
                </label>
            </div>

            <div class="form-group">
                <label for="salary_reminder_date" class="form-label">Salary Reminder Date</label>
                <input id="salary_reminder_date" type="date" name="salary_reminder_date" value="{{ old('salary_reminder_date', optional($settings->salary_reminder_date)->format('Y-m-d')) }}" class="form-control">
                @error('salary_reminder_date')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Salary Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>