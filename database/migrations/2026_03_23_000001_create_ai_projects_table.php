<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained('ai_projects')->nullOnDelete();
            $table->index(['project_id', 'created_at']);
        });

        $users = DB::table('ai_conversations')
            ->select('user_id')
            ->distinct()
            ->get();

        foreach ($users as $user) {
            $now = now();

            $projectId = DB::table('ai_projects')->insertGetId([
                'user_id' => $user->user_id,
                'name' => 'Imported Chats',
                'description' => 'Migrated from the previous AI conversation history.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('ai_conversations')
                ->where('user_id', $user->user_id)
                ->whereNull('project_id')
                ->update([
                    'project_id' => $projectId,
                    'updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::dropIfExists('ai_projects');
    }
};
