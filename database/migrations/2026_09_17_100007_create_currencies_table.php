<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per-user currency registry. Each user manages their own list of
     * currencies, so one user can never see or affect another's.
     */
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name', 64);
            $table->string('code', 8);
            $table->string('symbol', 16);
            $table->string('country', 64)->nullable();

            $table->unsignedTinyInteger('decimal_precision')->default(2);
            $table->string('thousands_separator', 4)->default(',');
            $table->string('decimal_separator', 4)->default('.');
            $table->string('symbol_position', 8)->default('before');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            // A currency code must be unique per user.
            $table->unique(['user_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
