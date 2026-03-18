<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // API tokens for external integrations and mobile apps
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Token name for identification
            $table->string('token', 64)->unique(); // SHA-256 hash
            $table->text('abilities')->nullable(); // JSON array of permissions
            $table->datetime('last_used_at')->nullable();
            $table->datetime('expires_at')->nullable();
            $table->string('ip_restriction')->nullable(); // Comma-separated allowed IPs
            $table->timestamps();

            $table->index(['user_id', 'expires_at']);
            $table->index('token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
