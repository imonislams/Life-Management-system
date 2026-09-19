<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates saved notification preferences.
 *
 * These are stored preferences only - no email, SMS, browser, queue, cron or
 * scheduler behaviour is attached to them.
 */
class NotificationSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'notifications_enabled' => ['nullable', 'boolean'],
            'notify_task_reminder' => ['nullable', 'boolean'],
            'notify_habit_reminder' => ['nullable', 'boolean'],
            'notify_goal_deadline' => ['nullable', 'boolean'],
            'notify_event_reminder' => ['nullable', 'boolean'],
            'notify_salary_reminder' => ['nullable', 'boolean'],
            'notify_savings_reminder' => ['nullable', 'boolean'],
            'notify_recurring_reminder' => ['nullable', 'boolean'],
            'notify_email' => ['nullable', 'boolean'],
            'notify_browser' => ['nullable', 'boolean'],
            'notify_reminder_time' => ['required', 'date_format:H:i'],
            'notify_quiet_start' => ['nullable', 'date_format:H:i'],
            'notify_quiet_end' => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notify_reminder_time.date_format' => 'The reminder time must be a valid time (HH:MM).',
            'notify_quiet_start.date_format' => 'Quiet hours start must be a valid time (HH:MM).',
            'notify_quiet_end.date_format' => 'Quiet hours end must be a valid time (HH:MM).',
        ];
    }
}
