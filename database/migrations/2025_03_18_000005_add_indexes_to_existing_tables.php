<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users table - add composite indexes for common queries
        Schema::table('users', function (Blueprint $table) {
            $table->index(['role', 'status']);
            $table->index(['status', 'created_at']);
        });

        // Students table - add composite indexes
        Schema::table('students', function (Blueprint $table) {
            $table->index(['supervisor_id', 'status']);
            $table->index(['programme_id', 'status']);
            $table->index(['status', 'start_date']);
        });

        // Tasks table - add additional indexes
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['status', 'due_date']);
            $table->index(['student_id', 'milestone_id']);
            $table->index(['parent_id', 'sort_order']);
        });

        // Task dependencies - add composite index
        Schema::table('task_dependencies', function (Blueprint $table) {
            $table->index(['depends_on_id', 'task_id']);
        });

        // Research journeys - add composite indexes
        Schema::table('research_journeys', function (Blueprint $table) {
            $table->index(['student_id', 'status']);
            $table->index(['status', 'start_date']);
        });

        // Stages - add composite index
        Schema::table('stages', function (Blueprint $table) {
            $table->index(['research_journey_id', 'status']);
            $table->index(['research_journey_id', 'sort_order']);
        });

        // Milestones - add composite index
        Schema::table('milestones', function (Blueprint $table) {
            $table->index(['stage_id', 'status']);
            $table->index(['stage_id', 'sort_order']);
            $table->index(['status', 'due_date']);
        });

        // Progress reports - add additional indexes
        Schema::table('progress_reports', function (Blueprint $table) {
            $table->index(['student_id', 'type', 'status']);
            $table->index(['status', 'submitted_at']);
            $table->index(['reviewed_by', 'status']);
        });

        // Files - add additional indexes
        Schema::table('files', function (Blueprint $table) {
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['student_id', 'mime_type']);
            $table->index(['parent_file_id', 'version']);
        });

        // Folders - add additional index
        Schema::table('folders', function (Blueprint $table) {
            $table->index(['student_id', 'category']);
        });

        // Meetings - add additional indexes
        Schema::table('meetings', function (Blueprint $table) {
            $table->index(['created_by', 'status']);
            $table->index(['status', 'scheduled_at']);
        });

        // AI conversations - add additional index
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index(['student_id', 'scope']);
        });

        // AI messages - add composite index
        Schema::table('ai_messages', function (Blueprint $table) {
            $table->index(['ai_conversation_id', 'created_at']);
        });

        // Audit logs - add composite index
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'action']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['action', 'created_at']);
        });

        // Revisions - add composite index
        Schema::table('revisions', function (Blueprint $table) {
            $table->index(['revisable_type', 'revisable_id', 'status']);
            $table->index(['requested_by', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        // Meeting attendees - add composite index
        Schema::table('meeting_attendees', function (Blueprint $table) {
            $table->index('user_id');
            $table->index(['meeting_id', 'attended']);
        });

        // Meeting action items - add additional index
        Schema::table('meeting_action_items', function (Blueprint $table) {
            $table->index(['assigned_to', 'is_completed']);
            $table->index(['due_date', 'is_completed']);
        });

        // Journey templates - add additional index
        Schema::table('journey_templates', function (Blueprint $table) {
            $table->index(['programme_id', 'is_active']);
            $table->index(['is_default', 'is_active']);
        });

        // Template stages - add additional index
        Schema::table('template_stages', function (Blueprint $table) {
            $table->index(['journey_template_id', 'sort_order']);
        });

        // Template milestones - add additional index
        Schema::table('template_milestones', function (Blueprint $table) {
            $table->index(['template_stage_id', 'sort_order']);
        });

        // System settings - add composite index
        Schema::table('system_settings', function (Blueprint $table) {
            $table->index(['group', 'key']);
        });

        // Notifications - add composite index (Laravel default, enhanced)
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }

    public function down(): void
    {
        // Rollback all indexes
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['supervisor_id', 'status']);
            $table->dropIndex(['programme_id', 'status']);
            $table->dropIndex(['status', 'start_date']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
            $table->dropIndex(['student_id', 'milestone_id']);
            $table->dropIndex(['parent_id', 'sort_order']);
        });

        Schema::table('task_dependencies', function (Blueprint $table) {
            $table->dropIndex(['depends_on_id', 'task_id']);
        });

        Schema::table('research_journeys', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'status']);
            $table->dropIndex(['status', 'start_date']);
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->dropIndex(['research_journey_id', 'status']);
            $table->dropIndex(['research_journey_id', 'sort_order']);
        });

        Schema::table('milestones', function (Blueprint $table) {
            $table->dropIndex(['stage_id', 'status']);
            $table->dropIndex(['stage_id', 'sort_order']);
            $table->dropIndex(['status', 'due_date']);
        });

        Schema::table('progress_reports', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'type', 'status']);
            $table->dropIndex(['status', 'submitted_at']);
            $table->dropIndex(['reviewed_by', 'status']);
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['uploaded_by', 'created_at']);
            $table->dropIndex(['student_id', 'mime_type']);
            $table->dropIndex(['parent_file_id', 'version']);
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'category']);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropIndex(['created_by', 'status']);
            $table->dropIndex(['status', 'scheduled_at']);
        });

        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['student_id', 'scope']);
        });

        Schema::table('ai_messages', function (Blueprint $table) {
            $table->dropIndex(['ai_conversation_id', 'created_at']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'action']);
            $table->dropIndex(['auditable_type', 'auditable_id']);
            $table->dropIndex(['action', 'created_at']);
        });

        Schema::table('revisions', function (Blueprint $table) {
            $table->dropIndex(['revisable_type', 'revisable_id', 'status']);
            $table->dropIndex(['requested_by', 'status']);
            $table->dropIndex(['assigned_to', 'status']);
        });

        Schema::table('meeting_attendees', function (Blueprint $table) {
            $table->dropIndex('user_id');
            $table->dropIndex(['meeting_id', 'attended']);
        });

        Schema::table('meeting_action_items', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'is_completed']);
            $table->dropIndex(['due_date', 'is_completed']);
        });

        Schema::table('journey_templates', function (Blueprint $table) {
            $table->dropIndex(['programme_id', 'is_active']);
            $table->dropIndex(['is_default', 'is_active']);
        });

        Schema::table('template_stages', function (Blueprint $table) {
            $table->dropIndex(['journey_template_id', 'sort_order']);
        });

        Schema::table('template_milestones', function (Blueprint $table) {
            $table->dropIndex(['template_stage_id', 'sort_order']);
        });

        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropIndex(['group', 'key']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id', 'read_at']);
        });
    }
};
