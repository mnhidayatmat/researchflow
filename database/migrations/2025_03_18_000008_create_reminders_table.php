<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->morphs('remindable'); // Task, Meeting, ProgressReport, Milestone
            $table->datetime('remind_at');
            $table->datetime('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'cancelled'])->default('pending');
            $table->enum('type', ['email', 'in_app', 'both'])->default('in_app');
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'remind_at']);
            $table->index(['remindable_type', 'remindable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
