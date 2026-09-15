<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expense_records', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'expense_category_id']);
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn('expense_category_id');
        });

        Schema::dropIfExists('expense_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::table('expense_records', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->constrained()->restrictOnDelete();
            $table->index(['user_id', 'expense_category_id']);
        });
    }
};
