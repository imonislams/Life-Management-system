@php
$flags = [
'notify_task_reminder' => 'Task reminders',
'notify_habit_reminder' => 'Habit reminders',
'notify_goal_deadline' => 'Goal deadline reminders',
'notify_event_reminder' => 'Event reminders',
'notify_salary_reminder' => 'Salary reminders',
'notify_savings_reminder' => 'Savings reminders',
'notify_recurring_reminder' => 'Recurring Finance reminders',
];
@endphp
<x-app-layout>
    <x-slot name="title">Notification Settings - Life Management System</x-slot>
    <x-slot name="pageTitle">Settings</x-slot>

    <div class="card">
        <h2 class="card-title">Notification Settings</h2>
        <p class="card-subtitle">Saved preferences for future use. No emails, SMS, browser notifications, queues, cron jobs or schedulers are activated.</p>
    </div>

@include('settings.partials.nav')

    <div class="card" style="max-width: 720px;">
        <form method="POST" action="{{ route('settings.notifications.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group" style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 0.375rem; padding: 0.875rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="notifications_enabled" value="1" {{ old('notifications_enabled', $settings->notifications_enabled) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0; font-weight: 600;">Enable notification preferences</span>
                </label>
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">
                    Master switch. Individual preferences below are only considered when this is enabled.
                </div>
            </div>

            <h3 style="font-size: 0.95rem; font-weight: 600; margin: 1.25rem 0 0.5rem;">Reminder Types</h3>

            @foreach($flags as $field => $label)
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="{{ $field }}" value="1" {{ old($field, $settings->$field) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    <span class="form-label" style="margin: 0;">{{ $label }}</span>
                </label>
            </div>
            @endforeach

            <h3 style="font-size: 0.95rem; font-weight: 600; margin: 1.25rem 0 0.5rem;">Delivery Channels</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="notify_email" value="1" {{ old('notify_email', $settings->notify_email) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                        <span class="form-label" style="margin: 0;">Email notification preference</span>
                    </label>
                </div>

                <div class="form-group" style="margin-bottom: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="notify_browser" value="1" {{ old('notify_browser', $settings->notify_browser) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                        <span class="form-label" style="margin: 0;">Browser notification preference</span>
                    </label>
                </div>
            </div>

            <h3 style="font-size: 0.95rem; font-weight: 600; margin: 1.25rem 0 0.5rem;">Timing</h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="notify_reminder_time" class="form-label">Reminder Time <span style="color: var(--danger-color);">*</span></label>
                    <input id="notify_reminder_time" type="time" name="notify_reminder_time" value="{{ old('notify_reminder_time', substr($settings->notify_reminder_time, 0, 5)) }}" class="form-control" required>
                    @error('notify_reminder_time')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="notify_quiet_start" class="form-label">Quiet Hours Start</label>
                    <input id="notify_quiet_start" type="time" name="notify_quiet_start" value="{{ old('notify_quiet_start', $settings->notify_quiet_start ? substr($settings->notify_quiet_start, 0, 5) : '') }}" class="form-control">
                    @error('notify_quiet_start')<div class="error-msg">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label for="notify_quiet_end" class="form-label">Quiet Hours End</label>
                    <input id="notify_quiet_end" type="time" name="notify_quiet_end" value="{{ old('notify_quiet_end', $settings->notify_quiet_end ? substr($settings->notify_quiet_end, 0, 5) : '') }}" class="form-control">
                    @error('notify_quiet_end')<div class="error-msg">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="alert-danger" style="margin-top: 0.5rem;">
                These settings are stored preferences only. No reminders are sent automatically.
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Save Notification Preferences</button>
                <a href="{{ route('settings.index') }}" class="btn-secondary btn-sm" style="padding: 0.625rem 1rem;">Back to Overview</a>
            </div>
        </form>
    </div>
</x-app-layout>