<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 50);
            $table->string('category_other')->nullable();
            $table->string('institution_name');
            $table->string('department')->nullable();
            $table->string('faculty')->nullable();
            $table->string('position_title')->nullable();
            $table->string('expertise_area')->nullable();
            $table->string('research_field')->nullable();
            $table->string('working_email');
            $table->string('phone_number')->nullable();
            $table->string('country', 120)->nullable();
            $table->boolean('suitable_for_grant')->default(true);
            $table->boolean('suitable_for_publication')->default(true);
            $table->boolean('suggested_reviewer')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'country']);
            $table->index(['user_id', 'suggested_reviewer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborators');
    }
};
