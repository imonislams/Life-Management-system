<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the remaining descriptive fields described by the Personal Workspace
     * specification so records can carry a source / category / employer / notes
     * alongside their existing amount, date and description.
     */
    public function up(): void
    {
        Schema::table('income_records', function (Blueprint $table) {
            if (! Schema::hasColumn('income_records', 'source')) {
                $table->string('source')->nullable()->after('description');
            }
            if (! Schema::hasColumn('income_records', 'category')) {
                $table->string('category')->nullable()->after('source');
            }
            if (! Schema::hasColumn('income_records', 'notes')) {
                $table->text('notes')->nullable()->after('category');
            }
        });

        Schema::table('expense_records', function (Blueprint $table) {
            if (! Schema::hasColumn('expense_records', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });

        Schema::table('salaries', function (Blueprint $table) {
            if (! Schema::hasColumn('salaries', 'employer')) {
                $table->string('employer')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('salaries', 'salary_date')) {
                $table->date('salary_date')->nullable()->after('employer');
            }
            if (! Schema::hasColumn('salaries', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'notes')) {
                $table->text('notes')->nullable()->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('income_records', function (Blueprint $table) {
            foreach (['source', 'category', 'notes'] as $column) {
                if (Schema::hasColumn('income_records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('expense_records', function (Blueprint $table) {
            if (Schema::hasColumn('expense_records', 'notes')) {
                $table->dropColumn('notes');
            }
        });

        Schema::table('salaries', function (Blueprint $table) {
            foreach (['employer', 'salary_date', 'notes'] as $column) {
                if (Schema::hasColumn('salaries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
