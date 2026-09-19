<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add measurable progress support to goals so progress can be derived from
     * real stored data (current_amount / target_amount) instead of a hand-typed
     * percentage, while qualitative goals keep the manual progress value.
     */
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            if (! Schema::hasColumn('goals', 'progress_type')) {
                $table->enum('progress_type', ['measurable', 'qualitative'])->default('qualitative')->after('status');
            }
            if (! Schema::hasColumn('goals', 'target_amount')) {
                $table->decimal('target_amount', 15, 2)->nullable()->after('progress_type');
            }
            if (! Schema::hasColumn('goals', 'current_amount')) {
                $table->decimal('current_amount', 15, 2)->nullable()->default(0)->after('target_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            foreach (['progress_type', 'target_amount', 'current_amount'] as $column) {
                if (Schema::hasColumn('goals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
