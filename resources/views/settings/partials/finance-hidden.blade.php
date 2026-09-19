@php
    /**
     * Renders hidden inputs for every finance field that is NOT part of the
     * currently submitted section, so a single shared update endpoint keeps the
     * other sections' saved values intact.
     *
     * Expects: $settings (App\Models\Setting) and $currentSection (string).
     */
    $currentSection = $currentSection ?? '';

    $textFields = [
        'salary' => [
            'salary_currency_code' => $settings->salary_currency_code,
            'salary_frequency' => $settings->salary_frequency,
            'salary_payment_day' => $settings->salary_payment_day,
            'salary_reminder_date' => optional($settings->salary_reminder_date)->format('Y-m-d'),
            'salary_display_format' => $settings->salary_display_format,
        ],
        'savings' => [
            'savings_currency_code' => $settings->savings_currency_code,
            'savings_monthly_target' => (float) $settings->savings_monthly_target,
            'savings_default_goal' => $settings->savings_default_goal,
            'savings_reminder_date' => optional($settings->savings_reminder_date)->format('Y-m-d'),
            'savings_progress_display' => $settings->savings_progress_display,
            'savings_transaction_display' => $settings->savings_transaction_display,
        ],
        'income' => [
            'income_currency_code' => $settings->income_currency_code,
            'income_display_format' => $settings->income_display_format,
            'income_summary_preference' => $settings->income_summary_preference,
        ],
        'expense' => [
            'expense_currency_code' => $settings->expense_currency_code,
            'expense_display_format' => $settings->expense_display_format,
            'expense_monthly_limit' => (float) $settings->expense_monthly_limit,
        ],
        'recurring' => [
            'recurring_display_preference' => $settings->recurring_display_preference,
            'recurring_frequency' => $settings->recurring_frequency,
            'recurring_summary_preference' => $settings->recurring_summary_preference,
        ],
    ];

    $flagFields = [
        'salary' => ['salary_reminder_enabled' => $settings->salary_reminder_enabled],
        'savings' => ['savings_reminder_enabled' => $settings->savings_reminder_enabled],
        'income' => ['income_monthly_overview' => $settings->income_monthly_overview],
        'expense' => ['expense_warning_enabled' => $settings->expense_warning_enabled],
    ];
@endphp
@foreach($textFields as $section => $fields)
    @if($section !== $currentSection)
        @foreach($fields as $field => $value)
            <input type="hidden" name="{{ $field }}" value="{{ old($field, $value) }}">
        @endforeach
    @endif
@endforeach
@foreach($flagFields as $section => $fields)
    @if($section !== $currentSection)
        @foreach($fields as $flag => $on)
            @if($on)
                <input type="hidden" name="{{ $flag }}" value="1">
            @endif
        @endforeach
    @endif
@endforeach