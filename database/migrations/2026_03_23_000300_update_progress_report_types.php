<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function isMySql(): bool
    {
        return DB::getDriverName() === 'mysql';
    }

    public function up(): void
    {
        if (!Schema::hasColumn('progress_reports', 'custom_type')) {
            Schema::table('progress_reports', function (Blueprint $table) {
                $table->string('custom_type')->nullable()->after('type');
            });
        }

        if ($this->isMySql()) {
            DB::statement("
                ALTER TABLE progress_reports
                MODIFY COLUMN type ENUM(
                    'weekly',
                    'monthly',
                    'milestone',
                    'custom',
                    'progress_report',
                    'thesis',
                    'manuscript',
                    'proposal',
                    'literature_review',
                    'presentation',
                    'other'
                ) NOT NULL DEFAULT 'progress_report'
            ");
        }

        DB::table('progress_reports')->whereIn('type', ['weekly', 'monthly', 'milestone'])->update([
            'type' => 'progress_report',
        ]);

        DB::table('progress_reports')->where('type', 'custom')->update([
            'type' => 'other',
            'custom_type' => 'Custom',
        ]);

        if ($this->isMySql()) {
            DB::statement("
                ALTER TABLE progress_reports
                MODIFY COLUMN type ENUM(
                    'progress_report',
                    'thesis',
                    'manuscript',
                    'proposal',
                    'literature_review',
                    'presentation',
                    'other'
                ) NOT NULL DEFAULT 'progress_report'
            ");
        }
    }

    public function down(): void
    {
        DB::table('progress_reports')->where('type', 'other')->update([
            'type' => 'custom',
        ]);

        DB::table('progress_reports')->whereIn('type', [
            'thesis',
            'manuscript',
            'proposal',
            'literature_review',
            'presentation',
        ])->update([
            'type' => 'custom',
        ]);

        if ($this->isMySql()) {
            DB::statement("
                ALTER TABLE progress_reports
                MODIFY COLUMN type ENUM('weekly', 'monthly', 'milestone', 'custom') NOT NULL DEFAULT 'weekly'
            ");
        }

        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropColumn('custom_type');
        });
    }
};
