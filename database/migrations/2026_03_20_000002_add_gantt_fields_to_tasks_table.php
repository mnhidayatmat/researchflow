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
        Schema::table('tasks', function (Blueprint $table) {
            // Add duration_days if not exists
            if (!Schema::hasColumn('tasks', 'duration_days')) {
                $table->unsignedInteger('duration_days')->nullable()->after('due_date');
            }

            // Add is_milestone if not exists
            if (!Schema::hasColumn('tasks', 'is_milestone')) {
                $table->boolean('is_milestone')->default(false)->after('duration_days');
            }

            // Add index for milestone queries
            $table->index('is_milestone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['is_milestone']);
            $table->dropColumn(['duration_days', 'is_milestone']);
        });
    }
};
