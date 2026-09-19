<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend goals with the full target/tracking field set: start date,
     * target title/value, notes and a 'cancelled' status.
     */
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            if (! Schema::hasColumn('goals', 'notes')) {
                $table->text('notes')->nullable()->after('progress');
            }
            if (! Schema::hasColumn('goals', 'start_date')) {
                $table->date('start_date')->nullable()->after('description');
            }
            if (! Schema::hasColumn('goals', 'target_value')) {
                $table->string('target_value')->nullable()->after('start_date');
            }
        });

        try {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE goals MODIFY COLUMN status ENUM('not_started','in_progress','completed','paused','cancelled') NOT NULL DEFAULT 'not_started'"
            );
        } catch (\Throwable $e) {
            // Non-MySQL driver or already widened - safe to continue.
        }
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            foreach (['notes', 'start_date', 'target_value'] as $column) {
                if (Schema::hasColumn('goals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
