<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user AI preferences.
 *
 * IMPORTANT: API keys are deliberately NOT stored here. Provider credentials
 * live only in the server .env because this table is user-editable and would
 * otherwise become an insecure secret store.
 *
 * "ai_training_samples" is fine-tuning scaffolding. Rows are captured only when
 * explicitly enabled and are never sent to a provider automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Master switch for this user (provider availability still applies).
            $table->boolean('ai_enabled')->default(true);

            // Optional per-user overrides of the provider/model (the API key is
            // never stored here).
            $table->string('provider', 50)->nullable();
            $table->string('model', 100)->nullable();

            // Privacy: allow the user to disable semantic indexing entirely.
            $table->boolean('semantic_indexing_enabled')->default(true);

            $table->timestamps();
        });

        Schema::create('ai_training_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('user_input');
            $table->text('expected_output')->nullable();
            $table->string('context_type', 60)->nullable();
            // pending | approved | rejected — nothing is trained on by default.
            $table->string('quality_status', 20)->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'quality_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_training_samples');
        Schema::dropIfExists('ai_settings');
    }
};
