<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('proposal_title');
            $table->string('grant_type', 100);
            $table->string('grant_name');
            $table->string('duration')->nullable();
            $table->enum('scope', ['international', 'national'])->default('national');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('stage', 100)->default('draft');
            $table->date('submission_date')->nullable();
            $table->date('deadline')->nullable();
            $table->date('announcement_date')->nullable();
            $table->unsignedTinyInteger('rejection_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'stage']);
            $table->index(['user_id', 'deadline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grants');
    }
};
