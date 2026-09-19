@php
use App\Models\Setting;
@endphp
<x-app-layout>
    <x-slot name="title">Recurring Finance Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Recurring Finance Settings</h2>
        <p class="card-subtitle">Recurring Finance never generates transactions automatically — these are display preferences only.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.finance.update') }}">
            @csrf
            @method('PUT')

            @include('settings.partials.finance-hidden', ['currentSection' => 'recurring'])

            <div class="form-group">
                <label for="recurring_display_preference" class="form-label">Recurring Finance Display Preference <span style="color: var(--danger-color);">*</span></label>
                <select id="recurring_display_preference" name="recurring_display_preference" class="form-control" required>
                    @foreach(Setting::DISPLAY_FORMATS as $value => $label)
                    <option value="{{ $value }}" {{ old('recurring_display_preference', $settings->recurring_display_preference) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('recurring_display_preference')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="recurring_frequency" class="form-label">Recurring Payment Frequency <span style="color: var(--danger-color);">*</span></label>
                <select id="recurring_frequency" name="recurring_frequency" class="form-control" required>
                    @foreach(Setting::SALARY_FREQUENCIES as $value => $label)
                    <option value="{{ $value }}" {{ old('recurring_frequency', $settings->recurring_frequency) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('recurring_frequency')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="recurring_summary_preference" class="form-label">Income / Expense Summary Preference <span style="color: var(--danger-color);">*</span></label>
                <select id="recurring_summary_preference" name="recurring_summary_preference" class="form-control" required>
                    @foreach(Setting::RECURRING_SUMMARY_PREFERENCES as $value => $label)
                    <option value="{{ $value }}" {{ old('recurring_summary_preference', $settings->recurring_summary_preference) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('recurring_summary_preference')<div class="error-msg">{{ $message }}</div>@enderror
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Recurring Settings</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>