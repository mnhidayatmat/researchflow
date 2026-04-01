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
        Schema::create('literature_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('author')->nullable();
            $table->smallInteger('year')->nullable();
            $table->string('title', 500);
            $table->string('journal', 255)->nullable();
            $table->string('doi_url', 500)->nullable();
            $table->text('research_objective')->nullable();
            $table->text('methodology')->nullable();
            $table->text('dataset')->nullable();
            $table->text('findings')->nullable();
            $table->text('limitations')->nullable();
            $table->text('relevance')->nullable();
            $table->string('keywords', 500)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['student_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('literature_entries');
    }
};
