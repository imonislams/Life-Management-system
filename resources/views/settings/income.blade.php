@php
use App\Models\Setting;
@endphp
<x-app-layout>
    <x-slot name="title">Income Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Income Settings</h2>
        <p class="card-subtitle">Display and summary preferences for your income records.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.finance.update') }}">
            @csrf
            @method('PUT')

            @include('settings.partials.finance-hidden', ['currentSection' => 'income'])

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="income_currency_code" class="form-label">Default Income Currency <span style="color: var(--danger-color);">*</span></label>
                    <input id="income_currency_code" type="text" name="income_currency_code" value="{{ old('income_currency_code', $settings->income_currency_code) }}" class="form-control" maxlength="8" required>
                    @error('income_currency_code')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="income_display_format" class="form-label">Income Display Format <span style="color: var(--danger-color);">*</span></label>
                    <select id="income_display_format" name="income_display_format" class="form-control" required>
                        @foreach(Setting::DISPLAY_FORMATS as $value => $label)
                        <option value="{{ $value }}" {{ old('income_display_format', $settings->income_display_format) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('income_display_format')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label for="income_summary_preference" class="form-label">Income Summary Preference <span style="color: var(--danger-color);">*</span></label>
                <select id="income_summary_preference" name="income_summary_preference" class="form-control" required>
                    @foreach(Setting::SUMMARY_PREFERENCES as $value => $label)
                    <option value="{{ $value }}" {{ old('income_summary_preference', $settings->income_summary_preference) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('income_summary_preference')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="income_monthly_overview" value="1" {{ old('income_monthly_overview', $settings->income_monthly_overview) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">Show monthly income overview on analytics pages</span>
                </label>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Income Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>