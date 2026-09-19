<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Individual messages inside an AI conversation.
 *
 * "metadata" records what was retrieved (sources, scores, currency info) so a
 * response can always be traced back to real MySQL records.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')
                ->constrained('ai_conversations')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Role is constrained to the values the pipeline understands.
            $table->string('role', 16); // user | assistant | system
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->unsignedInteger('token_estimate')->nullable();
            $table->timestamps();

            $table->index(['ai_conversation_id', 'id']);
            $table->index(['user_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
