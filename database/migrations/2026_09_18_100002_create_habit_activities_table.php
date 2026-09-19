<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A habit may group several independently trackable activities, e.g.
     * Habit "Daily Prayer" -> activities Fajr, Dhuhr, Asr, Maghrib, Isha.
     */
    public function up(): void
    {
        if (! Schema::hasTable('habit_activities')) {
            Schema::create('habit_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('habit_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['habit_id', 'sort_order']);
            });
        }

        // Attach a completion to a specific activity. Nullable so legacy
        // habit-level completions (activities not used) keep working.
        Schema::table('habit_completions', function (Blueprint $table) {
            if (! Schema::hasColumn('habit_completions', 'habit_activity_id')) {
                $table->foreignId('habit_activity_id')->nullable()->after('habit_id')
                    ->constrained('habit_activities')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('habit_completions', 'notes')) {
                $table->text('notes')->nullable()->after('completed_date');
            }
        });

        // Replace the (habit_id, completed_date) unique index with one that also
        // includes the activity, so the same activity can not be logged twice on
        // the same day while a habit can have many activities per day.
        //
        // The unique index backs the habit_id foreign key, so the FK must be
        // dropped first, then a plain index re-added and the FK restored.
        try {
            Schema::table('habit_completions', function (Blueprint $table) {
                $table->dropForeign(['habit_id']);
            });
        } catch (\Throwable $e) {
            // Foreign key already removed - safe to continue.
        }

        try {
            Schema::table('habit_completions', function (Blueprint $table) {
                $table->dropUnique('habit_completions_habit_id_completed_date_unique');
            });
        } catch (\Throwable $e) {
            // Index already removed - safe to continue.
        }

        try {
            Schema::table('habit_completions', function (Blueprint $table) {
                $table->index('habit_id', 'habit_completions_habit_id_index');
            });
        } catch (\Throwable $e) {
            // Index already present - safe to continue.
        }

        try {
            Schema::table('habit_completions', function (Blueprint $table) {
                $table->foreign('habit_id')->references('id')->on('habits')->cascadeOnDelete();
            });
        } catch (\Throwable $e) {
            // Foreign key already restored - safe to continue.
        }

        try {
            Schema::table('habit_completions', function (Blueprint $table) {
                $table->unique(
                    ['habit_activity_id', 'completed_date'],
                    'habit_activity_completion_unique'
                );
            });
        } catch (\Throwable $e) {
            // Index already present - safe to continue.
        }
    }

    public function down(): void
    {
        Schema::table('habit_completions', function (Blueprint $table) {
            if (Schema::hasColumn('habit_completions', 'habit_activity_id')) {
                $table->dropConstrainedForeignId('habit_activity_id');
            }
            if (Schema::hasColumn('habit_completions', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        Schema::dropIfExists('habit_activities');
    }
};
