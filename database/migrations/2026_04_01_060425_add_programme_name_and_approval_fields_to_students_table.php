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
        Schema::table('students', function (Blueprint $table) {
            $table->string('programme_name')->nullable()->after('programme_id');
            $table->timestamp('supervisor_approved_at')->nullable()->after('cosupervisor_id');
            $table->timestamp('cosupervisor_approved_at')->nullable()->after('supervisor_approved_at');
            // Make programme_id nullable so students can register without a pre-existing programme
            $table->foreignId('programme_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['programme_name', 'supervisor_approved_at', 'cosupervisor_approved_at']);
            $table->foreignId('programme_id')->nullable(false)->change();
        });
    }
};
