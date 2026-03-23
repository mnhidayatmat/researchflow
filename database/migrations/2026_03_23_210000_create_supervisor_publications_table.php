<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 500);
            $table->string('journal');
            $table->string('quartile', 10)->nullable();
            $table->decimal('impact_factor', 8, 3)->nullable();
            $table->string('stage', 50)->default('draft');
            $table->date('submission_date')->nullable();
            $table->date('rejected_1_date')->nullable();
            $table->text('rejected_1_reviewer_input')->nullable();
            $table->date('rejected_2_date')->nullable();
            $table->text('rejected_2_reviewer_input')->nullable();
            $table->date('rejected_3_date')->nullable();
            $table->text('rejected_3_reviewer_input')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'stage']);
            $table->index(['user_id', 'submission_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_publications');
    }
};
