<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persistent AI conversations. Every row is owned by exactly one user and is
 * protected by the AiConversationPolicy; MySQL remains the source of truth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            // A lightweight tag describing where the conversation started
            // (assistant, dashboard widget, goal panel, ...).
            $table->string('context_type')->nullable();
            // Optional related record (e.g. a goal the conversation is about).
            $table->string('context_ref_type')->nullable();
            $table->unsignedBigInteger('context_ref_id')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
