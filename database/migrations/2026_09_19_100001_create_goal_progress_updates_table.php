<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Goal progress updates: a dated history of what the user did toward a goal.
     *
     * Every update is preserved as history (never overwritten). A goal's current
     * progress is derived from these records, never fabricated.
     */
    public function up(): void
    {
        Schema::create('goal_progress_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();

            $table->date('date');
            $table->text('description');

            // Numeric progress toward a measurable target (e.g. "+10 pages").
            $table->decimal('progress_value', 15, 2)->nullable();
            $table->unsignedInteger('time_spent_minutes')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['goal_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_progress_updates');
    }
};
