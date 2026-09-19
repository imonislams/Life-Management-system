<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give recurring finance the full recurrence model described by the spec:
     * daily / weekly / monthly / yearly / custom interval, an optional end date,
     * and an explicit active / paused / completed status.
     *
     * The legacy is_active flag is preserved so existing rows keep working.
     */
    public function up(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('recurring_transactions', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('recurring_transactions', 'interval_days')) {
                $table->unsignedSmallInteger('interval_days')->nullable()->after('recurrence_type');
            }
            if (! Schema::hasColumn('recurring_transactions', 'status')) {
                $table->enum('status', ['active', 'paused', 'completed'])->default('active')->after('is_active');
            }
        });

        // Widen the recurrence_type enum to include every supported cadence.
        try {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE recurring_transactions MODIFY COLUMN recurrence_type ENUM('daily','weekly','monthly','yearly','custom') NOT NULL"
            );
        } catch (\Throwable $e) {
            // Non-MySQL driver or already widened - safe to continue.
        }
    }

    public function down(): void
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            foreach (['end_date', 'interval_days', 'status'] as $column) {
                if (Schema::hasColumn('recurring_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
