<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Records the currency a financial record was originally created in.
     *
     * Historical preservation: these columns are only ever written when a
     * record is created. Changing the default currency later never rewrites
     * them, and no exchange-rate conversion is performed anywhere.
     */
    public function up(): void
    {
        $tables = [
            'income_records',
            'expense_records',
            'salaries',
            'savings_goals',
            'recurring_transactions',
        ];

        foreach ($tables as $table) {
            if (! Schema::hasTable($table) || Schema::hasColumn($table, 'currency_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('currency_id')->nullable()->after('user_id')
                    ->constrained('currencies')->nullOnDelete();

                // Snapshot of the currency code at creation time, so the original
                // currency survives even if the currency row is deleted later.
                $blueprint->string('currency_code', 8)->nullable()->after('currency_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['income_records', 'expense_records', 'salaries', 'savings_goals', 'recurring_transactions'] as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropConstrainedForeignId('currency_id');
                $blueprint->dropColumn('currency_code');
            });
        }
    }
};
