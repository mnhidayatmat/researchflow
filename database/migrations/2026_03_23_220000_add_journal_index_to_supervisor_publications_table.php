<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisor_publications', function (Blueprint $table) {
            $table->string('journal_index', 50)->nullable()->after('journal');
            $table->string('journal_index_other')->nullable()->after('journal_index');
        });
    }

    public function down(): void
    {
        Schema::table('supervisor_publications', function (Blueprint $table) {
            $table->dropColumn(['journal_index', 'journal_index_other']);
        });
    }
};
