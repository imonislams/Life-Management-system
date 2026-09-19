<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend tasks with the full field set: category, progress tracking,
     * estimated and actual durations, and a 'cancelled' status.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('tasks', 'category')) {
                $table->string('category')->nullable()->after('description');
            }
            if (! Schema::hasColumn('tasks', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0)->after('status');
            }
            if (! Schema::hasColumn('tasks', 'estimated_minutes')) {
                $table->unsignedInteger('estimated_minutes')->nullable()->after('progress');
            }
            if (! Schema::hasColumn('tasks', 'actual_minutes')) {
                $table->unsignedInteger('actual_minutes')->nullable()->after('estimated_minutes');
            }
            if (! Schema::hasColumn('tasks', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('actual_minutes');
            }
        });

        // Widen the status enum to allow a cancelled state. MySQL requires a
        // raw statement to modify an enum in place.
        try {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE tasks MODIFY COLUMN status ENUM('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending'"
            );
        } catch (\Throwable $e) {
            // Non-MySQL driver or already widened - safe to continue.
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            foreach (['category', 'progress', 'estimated_minutes', 'actual_minutes', 'completed_at'] as $column) {
                if (Schema::hasColumn('tasks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
