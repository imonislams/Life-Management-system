<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daily Activities: a personal daily journal of what the user actually did.
     *
     * This is intentionally NOT a task-management table. Each row is a dated
     * record of an activity or note, optionally linked to a goal.
     */
    public function up(): void
    {
        Schema::create('daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Optional link to a goal the activity contributes to.
            $table->foreignId('goal_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->date('activity_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->string('category')->nullable();
            $table->string('status', 32)->default('completed');

            $table->timestamps();

            $table->index(['user_id', 'activity_date']);
            $table->index(['user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_activities');
    }
};
