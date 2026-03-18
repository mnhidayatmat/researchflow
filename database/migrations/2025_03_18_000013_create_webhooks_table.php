<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('url');
            $table->json('events'); // Array of events to subscribe to
            $table->string('secret')->nullable(); // For signature verification
            $table->boolean('is_active')->default(true);
            $table->integer('failure_count')->default(0); // Track consecutive failures
            $table->datetime('last_triggered_at')->nullable();
            $table->datetime('last_success_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        // Webhook delivery logs
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('event');
            $table->text('payload');
            $table->enum('status', ['pending', 'success', 'failed', 'retrying'])->default('pending');
            $table->integer('attempt_count')->default(0);
            $table->integer('response_code')->nullable();
            $table->text('response_body')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['webhook_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
    }
};
