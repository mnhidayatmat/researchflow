<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            // Add category relationship
            $table->foreignId('category_id')
                ->nullable()
                ->after('id')
                ->constrained('programme_categories')
                ->nullOnDelete();

            // Add additional fields for SaaS scalability
            $table->json('metadata')->nullable(); // Store flexible programme-specific data
            $table->string('award_type')->nullable(); // PhD, MPhil, MSc, etc.
        });

        // Add composite indexes for common queries
        Schema::table('programmes', function (Blueprint $table) {
            $table->index('category_id');
            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('programmes', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'metadata', 'award_type']);
        });
    }
};
