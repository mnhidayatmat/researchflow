<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('staff_id')->unique(); // Unique staff identifier
            $table->text('specializations')->nullable(); // JSON array of research areas
            $table->text('research_interests')->nullable();
            $table->integer('max_students')->default(10); // Maximum students they can supervise
            $table->integer('current_count')->default(0); // Current number of students
            $table->boolean('is_available')->default(true); // Available for new students
            $table->json('qualifications')->nullable(); // Degrees, certifications
            $table->string('designation')->nullable(); // Prof, Assoc Prof, etc.
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('user_id');
            $table->index('staff_id');
            $table->index('is_available');
            $table->index(['is_available', 'current_count']); // For finding available supervisors
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisors');
    }
};
