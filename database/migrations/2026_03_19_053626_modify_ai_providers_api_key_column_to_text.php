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
        Schema::table('ai_providers', function (Blueprint $table) {
            // Change api_key from string to text to accommodate encrypted data
            $table->text('api_key')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            // Revert back to string
            $table->string('api_key')->nullable()->change();
        });
    }
};
