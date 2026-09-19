<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add recurrence metadata to routine templates and create the separate
     * occurrence history table.
     *
     * A routine item is a template (e.g. "Study English"); each scheduled day is
     * an independent occurrence row with its own status, so completing or
     * skipping one day never overwrites the template.
     */
    public function up(): void
    {
        Schema::table('routine_items', function (Blueprint $table) {
            if (! Schema::hasColumn('routine_items', 'recurrence_type')) {
                $table->enum('recurrence_type', [
                    'one_time',
                    'daily',
                    'weekly',
                    'monthly',
                    'custom_days',
                    'interval',
                ])->default('daily')->after('description');
            }
            if (! Schema::hasColumn('routine_items', 'interval_days')) {
                $table->unsignedSmallInteger('interval_days')->nullable()->after('recurrence_type');
            }
            if (! Schema::hasColumn('routine_items', 'days_of_week')) {
                $table->string('days_of_week', 32)->nullable()->after('interval_days');
            }
            if (! Schema::hasColumn('routine_items', 'start_date')) {
                $table->date('start_date')->nullable()->after('days_of_week');
            }
            if (! Schema::hasColumn('routine_items', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
        });

        Schema::create('routine_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_item_id')->constrained()->cascadeOnDelete();

            $table->date('occurrence_date');
            $table->enum('status', ['pending', 'completed', 'skipped'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // One occurrence per routine per day, and no accidental duplicates.
            $table->unique(['routine_item_id', 'occurrence_date']);
            $table->index(['user_id', 'occurrence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_occurrences');

        Schema::table('routine_items', function (Blueprint $table) {
            foreach (['recurrence_type', 'interval_days', 'days_of_week', 'start_date', 'end_date'] as $column) {
                if (Schema::hasColumn('routine_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
