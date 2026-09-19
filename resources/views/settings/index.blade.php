@php
use App\Support\CurrencyConfig;
@endphp
<x-app-layout>
    <x-slot name="title">Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <!-- Header Panel -->
    <div class="card">
        <div class="card-header-flex">
            <div>
                <h2 class="card-title">Settings</h2>
                <p class="card-subtitle">Configure your personal workspace. These settings apply to your account only.</p>
            </div>
        </div>
    </div>

@include('settings.partials.nav')

    <!-- Overview of current configuration -->
    <div class="dashboard-grid-5" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="summary-card">
            <div class="summary-card-title">Application Name</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ $settings->app_name ?: config('app.name') }}</div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Currency</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                {{ $settings->currency_code }} — {{ $settings->currency_symbol }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                Example: {{ CurrencyConfig::format(auth()->id(), 1234.5) }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Timezone</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ $settings->timezone }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                {{ $settings->date_format }} · {{ $settings->time_format }}h
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Theme</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ ucfirst($settings->theme) }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                Layout: {{ ucfirst($settings->dashboard_layout) }}
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Notifications</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">
                {{ $settings->notifications_enabled ? 'Enabled' : 'Disabled' }}
            </div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                Saved preferences only
            </div>
        </div>
        <div class="summary-card">
            <div class="summary-card-title">Account</div>
            <div class="summary-card-value" style="font-size: 1.125rem;">{{ auth()->user()->name }}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                {{ auth()->user()->email }}
            </div>
        </div>
    </div>

    <!-- Settings sections as cards -->
    <div class="card">
        <h3 class="card-title" style="margin-bottom: 1rem;">Configuration Sections</h3>

        <div class="dashboard-tx-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            @php
            $cards = [
            ['General', 'Application name, language, timezone, date and time formats, week start day.', 'settings.general'],
            ['Currency', 'Currency name, code, symbol, precision, separators and symbol position.', 'settings.currency'],
            ['Salary', 'Default salary currency, payment frequency, payment day and display format.', 'settings.salary'],
            ['Savings', 'Savings currency, monthly target, default goal and progress display.', 'settings.savings'],
            ['Income', 'Default income currency, display format and summary preferences.', 'settings.income'],
            ['Expense', 'Default expense currency, display format and monthly limit warnings.', 'settings.expense'],
            ['Recurring', 'Recurring finance display, frequency and summary preferences.', 'settings.recurring'],
            ['Notifications', 'Saved reminder preferences for tasks, habits, goals, events and finance.', 'settings.notifications'],
            ['Appearance', 'Theme, sidebar behaviour, compact mode, layout and primary colour.', 'settings.appearance'],
            ['Profile', 'Your name, email and profile photo.', 'settings.profile'],
            ['Security', 'Change your password and review account details.', 'settings.security'],
            ['AI Status', 'Local AI health: Ollama, models and the vector database (100% free, runs on your machine).', 'settings.ai'],
            ];
            @endphp

            @foreach($cards as [$title, $description, $route])
            <a href="{{ route($route) }}" style="display: block; border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 1rem; text-decoration: none; color: inherit;">
                <div style="font-weight: 600; margin-bottom: 0.25rem;">{{ $title }}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $description }}</div>
            </a>
            @endforeach
        </div>
    </div>
</x-app-layout>