<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tags for flexible categorization
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('general'); // research_area, skill, topic, etc.
            $table->string('color')->nullable(); // For UI display
            $table->integer('usage_count')->default(0); // Track popularity
            $table->timestamps();

            $table->index('slug');
            $table->index('type');
            $table->index(['type', 'usage_count']);
        });

        // Polymorphic tag relationships
        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable'); // Can be User, Task, File, etc.
            $table->timestamps();

            $table->unique(['tag_id', 'taggable_type', 'taggable_id']);
            // Note: taggable_type/taggable_id index already exists from morphs()
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }
};
