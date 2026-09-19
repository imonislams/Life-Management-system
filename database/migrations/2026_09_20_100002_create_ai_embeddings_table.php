<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vector / semantic retrieval index.
 *
 * This table is a DERIVED index of the MySQL source of truth. It can be fully
 * rebuilt from the underlying records at any time (php artisan ai:reindex) and
 * losing it must never lose user data.
 *
 * Every row is scoped to a single user_id and semantic search is ALWAYS
 * filtered by that column, so one user can never retrieve another's vectors.
 *
 * The embedding itself is stored as a portable JSON vector by the default
 * "database" vector store. A different store (e.g. Milvus) keeps its own copy
 * under its own scheme; the vector_key column links both representations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Which MySQL record this vector was derived from.
            $table->string('source_type', 40);          // daily_activity | goal | ...
            $table->unsignedBigInteger('source_id');

            // Stable external key used by non-SQL vector stores.
            $table->string('vector_key', 191);

            // The embedded text (kept for re-embedding / debugging) and the raw
            // vector. Text is truncated deliberately: only retrievable context.
            $table->text('content');
            $table->json('embedding')->nullable();

            // Provider bookkeeping so a model change can be detected.
            $table->string('embedding_model', 100)->nullable();
            $table->unsignedInteger('embedding_dimensions')->nullable();

            // Snapshot of the source row's updated_at, so stale vectors can be
            // detected without touching MySQL records.
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamps();

            // One vector per source record.
            $table->unique(['user_id', 'source_type', 'source_id'], 'ai_embeddings_source_unique');
            $table->unique(['vector_key'], 'ai_embeddings_key_unique');
            $table->index(['user_id', 'source_type'], 'ai_embeddings_user_source_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_embeddings');
    }
};
