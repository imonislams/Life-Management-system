<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend savings goals so a user can own many savings accounts/goals
     * (name, target, start/target dates, status, description) instead of a
     * single row.
     *
     * The previous unique(user_id) index is dropped so multiple goals per user
     * are allowed; the existing rows are preserved.
     */
    public function up(): void
    {
        if (! Schema::hasTable('savings_goals')) {
            return;
        }

        // The unique(user_id) index also backs the user_id foreign key, so the
        // FK has to be dropped before the index can be removed, then restored
        // against a plain (non-unique) index.
        try {
            Schema::table('savings_goals', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Throwable $e) {
            // Foreign key already removed - safe to continue.
        }

        try {
            Schema::table('savings_goals', function (Blueprint $table) {
                $table->dropUnique('savings_goals_user_id_unique');
            });
        } catch (\Throwable $e) {
            // Already removed (or never created) - safe to continue.
        }

        Schema::table('savings_goals', function (Blueprint $table) {
            if (! Schema::hasColumn('savings_goals', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('savings_goals', 'start_date')) {
                $table->date('start_date')->nullable()->after('current_amount');
            }
            if (! Schema::hasColumn('savings_goals', 'target_date')) {
                $table->date('target_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('savings_goals', 'status')) {
                $table->enum('status', ['active', 'completed', 'paused'])->default('active')->after('target_date');
            }
        });

        // Plain (non-unique) index to keep per-user goal lookups fast, then
        // restore the foreign key the earlier drop removed.
        try {
            Schema::table('savings_goals', function (Blueprint $table) {
                $table->index('user_id', 'savings_goals_user_id_index');
            });
        } catch (\Throwable $e) {
            // Index already exists - safe to continue.
        }

        try {
            Schema::table('savings_goals', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // Foreign key already restored - safe to continue.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('savings_goals')) {
            return;
        }

        Schema::table('savings_goals', function (Blueprint $table) {
            foreach (['description', 'start_date', 'target_date', 'status'] as $column) {
                if (Schema::hasColumn('savings_goals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
