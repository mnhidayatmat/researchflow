# ResearchFlow Database Migration Index

## Migration Files

This document lists all migration files for the ResearchFlow database schema in execution order.

---

## Existing Migrations (Baseline)

| File | Description | Dependencies |
|------|-------------|--------------|
| `0001_01_01_000000_create_users_table.php` | Users, sessions, password reset | - |
| `0001_01_01_000001_create_cache_table.php` | Cache table | - |
| `0001_01_01_000002_create_jobs_table.php` | Queue jobs | - |
| `2025_01_01_000010_create_programmes_table.php` | Academic programmes | - |
| `2025_01_01_000020_create_students_table.php` | Student profiles | users, programmes |
| `2025_01_01_000030_create_research_journeys_table.php` | Journey templates and instances | students, programmes |
| `2025_01_01_000040_create_tasks_table.php` | Tasks and dependencies | students, milestones |
| `2025_01_01_000050_create_progress_reports_table.php` | Progress reporting | students |
| `2025_01_01_000060_create_revisions_table.php` | Revision management | users |
| `2025_01_01_000070_create_files_table.php` | File management | students, folders |
| `2025_01_01_000080_create_meetings_table.php` | Meeting management | students |
| `2025_01_01_000090_create_notifications_table.php` | Notifications (Laravel) | - |
| `2025_01_01_000100_create_ai_tables.php` | AI integration | users |
| `2025_01_01_000110_create_audit_logs_table.php` | Audit trail | users |
| `2025_01_01_000120_create_system_settings_table.php` | Settings | - |

---

## New Migrations (Enhanced Schema)

| File | Description | Dependencies |
|------|-------------|--------------|
| `2025_03_18_000001_create_programme_categories_table.php` | Programme categorization | - |
| `2025_03_18_000002_update_programmes_table.php` | Add category, metadata to programmes | programme_categories |
| `2025_03_18_000003_create_supervisors_table.php` | Supervisor profiles | users |
| `2025_03_18_000004_create_meeting_notes_table.php` | Meeting notes | meetings |
| `2025_03_18_000005_add_indexes_to_existing_tables.php` | Performance indexes | All base tables |
| `2025_03_18_000006_add_file_activities_table.php` | File access tracking | files |
| `2025_03_18_000007_create_tags_table.php` | Tagging system | - |
| `2025_03_18_000008_create_reminders_table.php` | Reminder system | users |
| `2025_03_18_000009_create_comments_table.php` | General comments | - |
| `2025_03_18_000010_create_announcements_table.php` | Announcements | users |
| `2025_03_18_000011_create_activity_timeline_table.php` | Activity timeline | users |
| `2025_03_18_000012_create_api_tokens_table.php` | API authentication | users |
| `2025_03_18_000013_create_webhooks_table.php` | Webhook integrations | users |
| `2025_03_18_000014_create_subscriptions_table.php` | SaaS subscriptions | users |

---

## Execution Order

To migrate the database from scratch, run migrations in this order:

```bash
# Laravel default migrations
php artisan migrate

# New schema migrations
php artisan migrate --path=database/migrations/2025_03_18_000001_create_programme_categories_table.php
php artisan migrate --path=database/migrations/2025_03_18_000002_update_programmes_table.php
php artisan migrate --path=database/migrations/2025_03_18_000003_create_supervisors_table.php
php artisan migrate --path=database/migrations/2025_03_18_000004_create_meeting_notes_table.php
php artisan migrate --path=database/migrations/2025_03_18_000005_add_indexes_to_existing_tables.php
php artisan migrate --path=database/migrations/2025_03_18_000006_add_file_activities_table.php
php artisan migrate --path=database/migrations/2025_03_18_000007_create_tags_table.php
php artisan migrate --path=database/migrations/2025_03_18_000008_create_reminders_table.php
php artisan migrate --path=database/migrations/2025_03_18_000009_create_comments_table.php
php artisan migrate --path=database/migrations/2025_03_18_000010_create_announcements_table.php
php artisan migrate --path=database/migrations/2025_03_18_000011_create_activity_timeline_table.php
php artisan migrate --path=database/migrations/2025_03_18_000012_create_api_tokens_table.php
php artisan migrate --path=database/migrations/2025_03_18_000013_create_webhooks_table.php
php artisan migrate --path=database/migrations/2025_03_18_000014_create_subscriptions_table.php
```

Or simply run:
```bash
php artisan migrate:fresh --seed
```

---

## Rollback Commands

To rollback specific migrations:

```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback all new migrations
php artisan migrate:rollback --step=14
```

---

## Table Creation Summary

| Category | Tables | Count |
|----------|--------|-------|
| Identity & Access | users, students, supervisors, sessions, password_reset_tokens, api_tokens | 6 |
| Programme Management | programme_categories, programmes | 2 |
| Research Journey | journey_templates, template_stages, template_milestones, research_journeys, stages, milestones | 6 |
| Task Management | tasks, task_dependencies | 2 |
| Progress Tracking | progress_reports, revisions, revision_comments | 3 |
| Collaboration | meetings, meeting_attendees, meeting_action_items, meeting_notes, comments | 5 |
| File Management | folders, files, file_activities | 3 |
| AI Integration | ai_providers, ai_conversations, ai_messages | 3 |
| System & Admin | system_settings, audit_logs, notifications, announcements, announcement_views | 5 |
| Features | tags, taggables, reminders, activities | 4 |
| Integrations | api_tokens, webhooks, webhook_deliveries | 3 |
| SaaS | subscriptions, subscription_items | 2 |
| **Total** | | **44 tables** |

---

## Model Relationships Reference

### User Relationships
- **Student**: One-to-One (user_id → students.user_id)
- **Supervisor**: One-to-One (user_id → supervisors.user_id)
- **Tasks**: One-to-Many (as assigned_by)
- **Meetings**: One-to-Many (as created_by)
- **Files**: One-to-Many (as uploaded_by)
- **Progress Reports**: One-to-Many (as reviewed_by)
- **Comments**: One-to-Many
- **Activities**: One-to-Many
- **API Tokens**: One-to-Many
- **Webhooks**: One-to-Many

### Student Relationships
- **User**: One-to-One
- **Programme**: Many-to-One
- **Supervisor**: Many-to-One
- **Co-supervisor**: Many-to-One
- **Research Journey**: One-to-Many
- **Tasks**: One-to-Many
- **Progress Reports**: One-to-Many
- **Meetings**: One-to-Many
- **Files**: One-to-Many
- **Folders**: One-to-Many

### Task Relationships
- **Student**: Many-to-One
- **Milestone**: Many-to-One (optional)
- **Parent Task**: Self-reference (hierarchical)
- **Subtasks**: One-to-Many (self-reference)
- **Dependencies**: Many-to-Many (via task_dependencies)
- **Revisions**: One-to-Many (polymorphic)

### File Relationships
- **Student**: Many-to-One
- **Folder**: Many-to-One (optional)
- **Uploaded By**: Many-to-One (user)
- **Parent File**: Self-reference (versioning)
- **File Versions**: One-to-Many (self-reference)
- **Activities**: One-to-Many

---

## Seeding Recommendations

### Required Seeders
1. **SystemSettingsSeeder** - Default configuration
2. **ProgrammeCategorySeeder** - Base categories
3. **ProgrammeSeeder** - Base programmes
4. **JourneyTemplateSeeder** - Default templates
5. **AiProviderSeeder** - AI service configurations
6. **AdminUserSeeder** - Default admin account

### Optional Seeders
1. **DemoDataSeeder** - Sample data for development
2. **TestUserSeeder** - Test accounts for QA

---

## Migration Checklist

- [x] Users and authentication
- [x] Role-based access control
- [x] Programme categories
- [x] Programme management
- [x] Student profiles
- [x] Supervisor profiles
- [x] Journey templates
- [x] Research journeys
- [x] Stages and milestones
- [x] Tasks and dependencies
- [x] Progress reports
- [x] Revision tracking
- [x] File management with versioning
- [x] Meeting management
- [x] AI integration
- [x] Audit logging
- [x] Notifications
- [x] Tags and taggables
- [x] Comments
- [x] Reminders
- [x] Activity timeline
- [x] API tokens
- [x] Webhooks
- [x] Announcements
- [x] Subscriptions (SaaS)
- [x] File activities tracking
- [x] Proper indexing
- [x] Foreign keys with cascade rules
- [x] Soft deletes where appropriate

---

*Last Updated: 2025-03-18*
