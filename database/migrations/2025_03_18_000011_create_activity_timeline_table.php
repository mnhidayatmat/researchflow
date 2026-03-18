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
            $table->morphs('causer')->nullable(); // Who caused this activity (defaults to user_id)
            $table->json('metadata')->nullable(); // Additional context
            $table->string('batch_id')->nullable(); // Group related activities
            $table->timestamps();

            // Indexes for efficient timeline queries
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index('batch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
