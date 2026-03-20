<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Unified activity timeline for all entities
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // task_created, task_completed, file_uploaded, meeting_scheduled, etc.
            $table->morphs('subject'); // The entity this activity is about
            $table->nullableMorphs('causer'); // Who caused this activity (defaults to user_id)
            $table->json('metadata')->nullable(); // Additional context
            $table->string('batch_id')->nullable(); // Group related activities
            $table->timestamps();

            // Indexes for efficient timeline queries
            $table->index(['user_id', 'created_at']);
            // Note: subject_type/subject_id and causer_type/causer_id indexes already exist from morphs()
            // Add created_at to subject index for timeline queries
            $table->index(['subject_type', 'subject_id', 'created_at'], 'activities_subject_created_at_index');
            $table->index(['type', 'created_at']);
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
