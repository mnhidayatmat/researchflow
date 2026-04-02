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
            $table->string('supervisor_email')->nullable()->after('cosupervisor_id');
            $table->string('cosupervisor_email')->nullable()->after('supervisor_email');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['supervisor_email', 'cosupervisor_email']);
        });
    }
};
