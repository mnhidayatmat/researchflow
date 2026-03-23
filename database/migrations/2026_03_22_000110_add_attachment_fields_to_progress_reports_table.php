<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->string('attachment_original_name')->nullable()->after('reviewed_at');
            $table->string('attachment_mime_type')->nullable()->after('attachment_original_name');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime_type');
            $table->string('attachment_disk')->nullable()->after('attachment_size');
            $table->string('attachment_path')->nullable()->after('attachment_disk');
            $table->foreignId('attachment_storage_owner_id')->nullable()->after('attachment_path')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attachment_storage_owner_id');
            $table->dropColumn([
                'attachment_original_name',
                'attachment_mime_type',
                'attachment_size',
                'attachment_disk',
                'attachment_path',
            ]);
        });
    }
};
