<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_providers', 'temperature')) {
                $table->float('temperature')->default(0.7)->after('settings');
            }
            if (!Schema::hasColumn('ai_providers', 'max_tokens')) {
                $table->unsignedInteger('max_tokens')->default(4096)->after('temperature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_providers', function (Blueprint $table) {
            $table->dropColumn(['temperature', 'max_tokens']);
        });
    }
};
