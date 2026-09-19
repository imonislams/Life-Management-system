<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per-user settings. One row per user, created lazily on first access.
     * Every section (general, currency, salary, savings, income, expense,
     * recurring, notifications, appearance) is stored here so no second
     * settings table is ever needed.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // General
            $table->string('app_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('language', 10)->default('en');
            $table->string('timezone', 64)->default('UTC');
            $table->string('date_format', 32)->default('M d, Y');
            $table->string('time_format', 8)->default('12');
            $table->unsignedTinyInteger('week_start')->default(0);

            // Currency
            $table->string('currency_name', 64)->default('Bangladeshi Taka');
            $table->string('currency_code', 8)->default('BDT');
            $table->string('currency_symbol', 16)->default("\u{09F3}");
            $table->unsignedTinyInteger('currency_decimals')->default(2);
            $table->string('currency_thousands_separator', 4)->default(',');
            $table->string('currency_decimal_separator', 4)->default('.');
            $table->string('currency_position', 8)->default('before');
            $table->string('currency_suffix', 8)->default('TK');
            $table->boolean('currency_active')->default(true);

            // Salary
            $table->string('salary_currency_code', 8)->default('BDT');
            $table->string('salary_frequency', 16)->default('monthly');
            $table->unsignedTinyInteger('salary_payment_day')->nullable();
            $table->boolean('salary_reminder_enabled')->default(false);
            $table->date('salary_reminder_date')->nullable();
            $table->string('salary_display_format', 16)->default('full');

            // Savings
            $table->string('savings_currency_code', 8)->default('BDT');
            $table->decimal('savings_monthly_target', 15, 2)->default(0);
            $table->string('savings_default_goal')->nullable();
            $table->boolean('savings_reminder_enabled')->default(false);
            $table->date('savings_reminder_date')->nullable();
            $table->string('savings_progress_display', 16)->default('percentage');
            $table->string('savings_transaction_display', 16)->default('detailed');

            // Income
            $table->string('income_currency_code', 8)->default('BDT');
            $table->string('income_display_format', 16)->default('full');
            $table->string('income_summary_preference', 16)->default('monthly');
            $table->boolean('income_monthly_overview')->default(true);

            // Expense
            $table->string('expense_currency_code', 8)->default('BDT');
            $table->string('expense_display_format', 16)->default('full');
            $table->decimal('expense_monthly_limit', 15, 2)->default(0);
            $table->boolean('expense_warning_enabled')->default(false);

            // Recurring finance
            $table->string('recurring_display_preference', 16)->default('detailed');
            $table->string('recurring_frequency', 16)->default('monthly');
            $table->string('recurring_summary_preference', 16)->default('net');

            // Notifications (saved preferences only - nothing is dispatched)
            $table->boolean('notifications_enabled')->default(false);
            $table->boolean('notify_task_reminder')->default(false);
            $table->boolean('notify_habit_reminder')->default(false);
            $table->boolean('notify_goal_deadline')->default(false);
            $table->boolean('notify_event_reminder')->default(false);
            $table->boolean('notify_salary_reminder')->default(false);
            $table->boolean('notify_savings_reminder')->default(false);
            $table->boolean('notify_recurring_reminder')->default(false);
            $table->boolean('notify_email')->default(false);
            $table->boolean('notify_browser')->default(false);
            $table->time('notify_reminder_time')->default('09:00');
            $table->time('notify_quiet_start')->nullable();
            $table->time('notify_quiet_end')->nullable();

            // Appearance
            $table->string('theme', 16)->default('light');
            $table->boolean('sidebar_collapsed')->default(false);
            $table->boolean('compact_mode')->default(false);
            $table->string('dashboard_layout', 16)->default('full');
            $table->string('primary_color', 16)->default('#2563eb');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
