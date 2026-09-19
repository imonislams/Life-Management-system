<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    public const LANGUAGES = [
        'en' => 'English',
        'bn' => 'Bengali',
        'ar' => 'Arabic',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'hi' => 'Hindi',
        'ur' => 'Urdu',
    ];

    public const DATE_FORMATS = [
        'M d, Y' => 'Jan 31, 2026',
        'd M Y' => '31 Jan 2026',
        'Y-m-d' => '2026-01-31',
        'd/m/Y' => '31/01/2026',
        'm/d/Y' => '01/31/2026',
        'd-m-Y' => '31-01-2026',
    ];

    public const TIME_FORMATS = [
        '12' => '12-hour (03:30 PM)',
        '24' => '24-hour (15:30)',
    ];

    public const WEEK_DAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    public const CURRENCY_POSITIONS = [
        'before' => 'Before amount (symbol 1,234.00)',
        'after' => 'After amount (1,234.00 symbol)',
    ];

    public const SALARY_FREQUENCIES = [
        'monthly' => 'Monthly',
        'weekly' => 'Weekly',
        'biweekly' => 'Bi-weekly',
    ];

    public const DISPLAY_FORMATS = [
        'full' => 'Full amount (1,234.00)',
        'short' => 'Short (1.2K)',
        'plain' => 'Plain number (1234.00)',
    ];

    public const SAVINGS_PROGRESS_DISPLAY = [
        'percentage' => 'Percentage (75%)',
        'amount' => 'Amount remaining',
        'both' => 'Both percentage and amount',
    ];

    public const SAVINGS_TRANSACTION_DISPLAY = [
        'detailed' => 'Detailed (date, note, amount)',
        'compact' => 'Compact (amount only)',
    ];

    public const SUMMARY_PREFERENCES = [
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'all_time' => 'All time',
    ];

    public const RECURRING_SUMMARY_PREFERENCES = [
        'net' => 'Net total',
        'separate' => 'Separate income and expense',
    ];

    public const THEMES = [
        'light' => 'Light',
        'dark' => 'Dark',
        'system' => 'System',
    ];

    public const DASHBOARD_LAYOUTS = [
        'full' => 'Full (all sections)',
        'compact' => 'Compact (summary cards only)',
        'focus' => 'Focus (tasks, habits, goals)',
    ];

    public const PRIMARY_COLORS = [
        '#2563eb' => 'Blue (default)',
        '#16a34a' => 'Green',
        '#9333ea' => 'Purple',
        '#dc2626' => 'Red',
        '#ea580c' => 'Orange',
        '#0891b2' => 'Teal',
        '#0f172a' => 'Slate',
    ];

    /**
     * A curated list of currencies, useful as datalist suggestions while still
     * allowing any ISO code to be entered (worldwide support).
     */
    public const COMMON_CURRENCIES = [
        ['code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => "\u{09F3}"],
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => "\u{20AC}"],
        ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => "\u{00A3}"],
        ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => "\u{20B9}"],
        ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => "\u{20A8}"],
        ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => "\u{00A5}"],
        ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => "\u{00A5}"],
        ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
        ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$'],
        ['code' => 'AED', 'name' => 'UAE Dirham', 'symbol' => "\u{062F}.\u{0625}"],
        ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => "\u{FDFC}"],
        ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
        ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => 'S$'],
        ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => "\u{20BA}"],
    ];

    protected $fillable = [
        'user_id',
        // General
        'app_name',
        'logo_path',
        'favicon_path',
        'language',
        'timezone',
        'date_format',
        'time_format',
        'week_start',
        // Currency
        'currency_name',
        'currency_code',
        'currency_symbol',
        'currency_decimals',
        'currency_thousands_separator',
        'currency_decimal_separator',
        'currency_position',
        'currency_suffix',
        'currency_active',
        // Salary
        'salary_currency_code',
        'salary_frequency',
        'salary_payment_day',
        'salary_reminder_enabled',
        'salary_reminder_date',
        'salary_display_format',
        // Savings
        'savings_currency_code',
        'savings_monthly_target',
        'savings_default_goal',
        'savings_reminder_enabled',
        'savings_reminder_date',
        'savings_progress_display',
        'savings_transaction_display',
        // Income
        'income_currency_code',
        'income_display_format',
        'income_summary_preference',
        'income_monthly_overview',
        // Expense
        'expense_currency_code',
        'expense_display_format',
        'expense_monthly_limit',
        'expense_warning_enabled',
        // Recurring
        'recurring_display_preference',
        'recurring_frequency',
        'recurring_summary_preference',
        // Notifications
        'notifications_enabled',
        'notify_task_reminder',
        'notify_habit_reminder',
        'notify_goal_deadline',
        'notify_event_reminder',
        'notify_salary_reminder',
        'notify_savings_reminder',
        'notify_recurring_reminder',
        'notify_email',
        'notify_browser',
        'notify_reminder_time',
        'notify_quiet_start',
        'notify_quiet_end',
        // Appearance
        'theme',
        'sidebar_collapsed',
        'compact_mode',
        'dashboard_layout',
        'primary_color',
    ];

    protected function casts(): array
    {
        return [
            'currency_active' => 'boolean',
            'currency_decimals' => 'integer',
            'week_start' => 'integer',
            'salary_reminder_enabled' => 'boolean',
            'salary_reminder_date' => 'date',
            'salary_payment_day' => 'integer',
            'savings_monthly_target' => 'decimal:2',
            'savings_reminder_enabled' => 'boolean',
            'savings_reminder_date' => 'date',
            'income_monthly_overview' => 'boolean',
            'expense_monthly_limit' => 'decimal:2',
            'expense_warning_enabled' => 'boolean',
            'notifications_enabled' => 'boolean',
            'notify_task_reminder' => 'boolean',
            'notify_habit_reminder' => 'boolean',
            'notify_goal_deadline' => 'boolean',
            'notify_event_reminder' => 'boolean',
            'notify_salary_reminder' => 'boolean',
            'notify_savings_reminder' => 'boolean',
            'notify_recurring_reminder' => 'boolean',
            'notify_email' => 'boolean',
            'notify_browser' => 'boolean',
            'sidebar_collapsed' => 'boolean',
            'compact_mode' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the settings row for a user, creating it with defaults if missing.
     */
    public static function forUser(int $userId): self
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }
}
