<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates all finance-related settings sections:
 * Salary, Savings, Income, Expense and Recurring Finance.
 */
class FinanceSettingsRequest extends FormRequest
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
            // Salary
            'salary_currency_code' => ['required', 'string', 'max:8'],
            'salary_frequency' => ['required', Rule::in(array_keys(Setting::SALARY_FREQUENCIES))],
            'salary_payment_day' => ['nullable', 'integer', 'between:1,31'],
            'salary_reminder_enabled' => ['nullable', 'boolean'],
            'salary_reminder_date' => ['nullable', 'date'],
            'salary_display_format' => ['required', Rule::in(array_keys(Setting::DISPLAY_FORMATS))],

            // Savings
            'savings_currency_code' => ['required', 'string', 'max:8'],
            'savings_monthly_target' => ['required', 'numeric', 'min:0'],
            'savings_default_goal' => ['nullable', 'string', 'max:255'],
            'savings_reminder_enabled' => ['nullable', 'boolean'],
            'savings_reminder_date' => ['nullable', 'date'],
            'savings_progress_display' => ['required', Rule::in(array_keys(Setting::SAVINGS_PROGRESS_DISPLAY))],
            'savings_transaction_display' => ['required', Rule::in(array_keys(Setting::SAVINGS_TRANSACTION_DISPLAY))],

            // Income
            'income_currency_code' => ['required', 'string', 'max:8'],
            'income_display_format' => ['required', Rule::in(array_keys(Setting::DISPLAY_FORMATS))],
            'income_summary_preference' => ['required', Rule::in(array_keys(Setting::SUMMARY_PREFERENCES))],
            'income_monthly_overview' => ['nullable', 'boolean'],

            // Expense
            'expense_currency_code' => ['required', 'string', 'max:8'],
            'expense_display_format' => ['required', Rule::in(array_keys(Setting::DISPLAY_FORMATS))],
            'expense_monthly_limit' => ['required', 'numeric', 'min:0'],
            'expense_warning_enabled' => ['nullable', 'boolean'],

            // Recurring
            'recurring_display_preference' => ['required', Rule::in(array_keys(Setting::DISPLAY_FORMATS))],
            'recurring_frequency' => ['required', Rule::in(array_keys(Setting::SALARY_FREQUENCIES))],
            'recurring_summary_preference' => ['required', Rule::in(array_keys(Setting::RECURRING_SUMMARY_PREFERENCES))],
        ];
    }

    public function attributes(): array
    {
        return [
            'salary_payment_day' => 'salary payment day',
            'savings_monthly_target' => 'monthly savings target',
            'expense_monthly_limit' => 'monthly expense limit',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'salary_payment_day.between' => 'The salary payment day must be between 1 and 31.',
        ];
    }
}
