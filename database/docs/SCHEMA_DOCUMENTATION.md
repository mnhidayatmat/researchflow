# ResearchFlow Database Schema Documentation

## Overview

This document provides a comprehensive overview of the ResearchFlow database schema designed for a Student Research Supervision Management System. The schema is built on Laravel 13 and optimized for PostgreSQL/MySQL.

## Design Principles

1. **Data Integrity**: Foreign keys with appropriate cascade behaviors
2. **Performance**: Strategic indexes on frequently queried columns
3. **Scalability**: Soft deletes, composite indexes, and JSON columns for flexibility
4. **Auditability**: Activity tracking and audit logs throughout
5. **Multi-tenancy Ready**: Structure supports future SaaS expansion

## Table Groups

### 1. Identity & Access Control

#### users
**Purpose**: Core user accounts with role-based access

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | Primary key |
| name | string | NOT NULL | Full name |
| email | string | UNIQUE, NOT NULL | Login email |
| password | string | NOT NULL | Hashed |
| role | enum | NOT NULL | admin, supervisor, cosupervisor, student |
| staff_id | string | UNIQUE, NULLABLE | Staff identifier |
| matric_number | string | UNIQUE, NULLABLE | Student ID |
| phone | string | NULLABLE | Contact number |
| avatar | string | NULLABLE | Profile image path |
| department | string | NULLABLE | Academic department |
| faculty | string | NULLABLE | Academic faculty |
| status | enum | NOT NULL | active, inactive, pending |
| bio | text | NULLABLE | User biography |
| email_verified_at | timestamp | NULLABLE | Email verification |
| remember_token | string | NULLABLE | "Remember me" token |
| created_at | timestamp | | |
| updated_at | timestamp | | |
| deleted_at | timestamp | NULLABLE | Soft delete |

**Indexes**: role, status, [role, status], [status, created_at]

---

#### students
**Purpose**: Extended profile for student users

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, UNIQUE, NOT NULL | users.id |
| programme_id | bigint | FK, NOT NULL | programmes.id |
| supervisor_id | bigint | FK, NULLABLE | users.id (supervisor role) |
| cosupervisor_id | bigint | FK, NULLABLE | users.id (cosupervisor role) |
| research_title | string | NULLABLE | Thesis/dissertation title |
| research_abstract | text | NULLABLE | Research summary |
| intake | string | NULLABLE | Academic intake |
| start_date | date | NULLABLE | Programme start |
| expected_completion | date | NULLABLE | Planned end date |
| actual_completion | date | NULLABLE | Actual end date |
| status | enum | NOT NULL | pending, active, on_hold, completed, withdrawn |
| overall_progress | tinyint | NOT NULL | 0-100 percentage |

**Indexes**: status, user_id, [supervisor_id, status], [programme_id, status], [status, start_date]

---

#### supervisors
**Purpose**: Extended profile for supervisor users

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NOT NULL | users.id |
| staff_id | string | UNIQUE, NOT NULL | Staff identifier |
| specializations | text | NULLABLE | JSON: research areas |
| research_interests | text | NULLABLE | |
| max_students | int | NOT NULL | Capacity limit |
| current_count | int | NOT NULL | Current students |
| is_available | boolean | NOT NULL | Accepting students |
| qualifications | json | NULLABLE | Degrees, certs |
| designation | string | NULLABLE | Prof, Assoc Prof, etc. |

**Indexes**: user_id, staff_id, is_available, [is_available, current_count]

---

#### password_reset_tokens
**Purpose**: Password reset functionality

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| email | string | PK, NOT NULL | User email |
| token | string | NOT NULL | Reset token |
| created_at | timestamp | NULLABLE | |

---

#### sessions
**Purpose**: User session management

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | string | PK, NOT NULL | Session ID |
| user_id | bigint | FK, NULLABLE, INDEX | users.id |
| ip_address | string | NULLABLE | Client IP |
| user_agent | text | NULLABLE | Browser info |
| payload | longtext | NOT NULL | Session data |
| last_activity | int | INDEX | Timestamp |

---

#### api_tokens
**Purpose**: API authentication tokens

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NOT NULL | users.id |
| name | string | NOT NULL | Token identifier |
| token | string | UNIQUE, NOT NULL | SHA-256 hash |
| abilities | text | NULLABLE | JSON: permissions |
| last_used_at | timestamp | NULLABLE | |
| expires_at | timestamp | NULLABLE | |
| ip_restriction | string | NULLABLE | Allowed IPs |

**Indexes**: [user_id, expires_at], token

---

### 2. Programme Management

#### programme_categories
**Purpose**: Group programmes by category

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| name | string | NOT NULL | |
| slug | string | UNIQUE, NOT NULL | URL-friendly |
| description | text | NULLABLE | |
| icon | string | NULLABLE | UI icon |
| color | string | NULLABLE | UI color |
| sort_order | int | NOT NULL | Display order |
| is_active | boolean | NOT NULL | |

**Indexes**: slug, is_active, sort_order

---

#### programmes
**Purpose**: Academic programmes (PhD, MSc, etc.)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| category_id | bigint | FK, NULLABLE | programme_categories.id |
| name | string | NOT NULL | |
| code | string | UNIQUE, NOT NULL | Programme code |
| slug | string | UNIQUE, NOT NULL | |
| description | text | NULLABLE | |
| duration_months | int | NOT NULL | Default 12 |
| metadata | json | NULLABLE | Flexible data |
| award_type | string | NULLABLE | PhD, MPhil, MSc |
| is_active | boolean | NOT NULL | |
| sort_order | int | NOT NULL | |

**Indexes**: code, slug, category_id, is_active, [is_active, sort_order]

---

### 3. Research Journey Management

#### journey_templates
**Purpose**: Reusable journey templates for programmes

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| programme_id | bigint | FK, NOT NULL | programmes.id |
| name | string | NOT NULL | |
| description | text | NULLABLE | |
| is_default | boolean | NOT NULL | |
| is_active | boolean | NOT NULL | |

**Indexes**: programme_id, [programme_id, is_active], [is_default, is_active]

---

#### template_stages
**Purpose**: Stages within journey templates

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| journey_template_id | bigint | FK, NOT NULL | journey_templates.id |
| name | string | NOT NULL | |
| description | text | NULLABLE | |
| sort_order | int | NOT NULL | |
| duration_weeks | int | NULLABLE | Expected duration |

**Indexes**: [journey_template_id, sort_order]

---

#### template_milestones
**Purpose**: Milestones within template stages

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| template_stage_id | bigint | FK, NOT NULL | template_stages.id |
| name | string | NOT NULL | |
| description | text | NULLABLE | |
| sort_order | int | NOT NULL | |
| week_offset | int | NULLABLE | Weeks from stage start |

**Indexes**: [template_stage_id, sort_order]

---

#### research_journeys
**Purpose**: Student's research journey instance

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| student_id | bigint | FK, NOT NULL | students.id |
| journey_template_id | bigint | FK, NULLABLE | journey_templates.id |
| name | string | NOT NULL | |
| start_date | date | NULLABLE | |
| end_date | date | NULLABLE | |
| progress | tinyint | NOT NULL | 0-100 |
| status | enum | NOT NULL | not_started, in_progress, completed |

**Indexes**: [student_id, status], [status, start_date], journey_template_id

---

#### stages
**Purpose**: Student's journey stages

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| research_journey_id | bigint | FK, NOT NULL | research_journeys.id |
| name | string | NOT NULL | |
| description | text | NULLABLE | |
| sort_order | int | NOT NULL | |
| start_date | date | NULLABLE | |
| end_date | date | NULLABLE | |
| status | enum | NOT NULL | not_started, in_progress, completed |
| progress | tinyint | NOT NULL | 0-100 |

**Indexes**: [research_journey_id, status], [research_journey_id, sort_order]

---

#### milestones
**Purpose**: Milestones within student stages

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| stage_id | bigint | FK, NOT NULL | stages.id |
| name | string | NOT NULL | |
| description | text | NULLABLE | |
| sort_order | int | NOT NULL | |
| due_date | date | NULLABLE | |
| completed_at | date | NULLABLE | |
| status | enum | NOT NULL | not_started, in_progress, completed |
| progress | tinyint | NOT NULL | 0-100 |

**Indexes**: [stage_id, status], [stage_id, sort_order], [status, due_date]

---

### 4. Task Management

#### tasks
**Purpose**: Research tasks with hierarchical support

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| student_id | bigint | FK, NOT NULL | students.id |
| milestone_id | bigint | FK, NULLABLE | milestones.id |
| assigned_by | bigint | FK, NULLABLE | users.id |
| parent_id | bigint | FK, NULLABLE | tasks.id (self-ref) |
| title | string | NOT NULL | |
| description | text | NULLABLE | |
| status | enum | NOT NULL | backlog, planned, in_progress, waiting_review, revision, completed |
| priority | enum | NOT NULL | low, medium, high, urgent |
| start_date | date | NULLABLE | |
| due_date | date | NULLABLE | |
| completed_at | date | NULLABLE | |
| progress | tinyint | NOT NULL | 0-100 |
| sort_order | int | NOT NULL | |
| estimated_hours | int | NULLABLE | |

**Indexes**: status, priority, [student_id, status], milestone_id, parent_id, [status, due_date], [student_id, milestone_id], [parent_id, sort_order]

---

#### task_dependencies
**Purpose**: Task dependency relationships

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| task_id | bigint | FK, NOT NULL | tasks.id |
| depends_on_id | bigint | FK, NOT NULL | tasks.id |

**Indexes**: UNIQUE(task_id, depends_on_id), [depends_on_id, task_id]

---

### 5. Progress Tracking

#### progress_reports
**Purpose**: Student progress reports

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| student_id | bigint | FK, NOT NULL | students.id |
| reviewed_by | bigint | FK, NULLABLE | users.id |
| title | string | NOT NULL | |
| content | text | NOT NULL | |
| achievements | text | NULLABLE | |
| challenges | text | NULLABLE | |
| next_steps | text | NULLABLE | |
| type | enum | NOT NULL | weekly, monthly, milestone, custom |
| status | enum | NOT NULL | draft, submitted, reviewed, revision_needed, accepted |
| period_start | date | NULLABLE | |
| period_end | date | NULLABLE | |
| supervisor_feedback | text | NULLABLE | |
| submitted_at | timestamp | NULLABLE | |
| reviewed_at | timestamp | NULLABLE | |

**Indexes**: [student_id, status], type, [student_id, type, status], [status, submitted_at], [reviewed_by, status]

---

### 6. Collaboration

#### meetings
**Purpose**: Supervisory meetings

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| student_id | bigint | FK, NOT NULL | students.id |
| created_by | bigint | FK, NOT NULL | users.id |
| title | string | NOT NULL | |
| agenda | text | NULLABLE | |
| notes | text | NULLABLE | |
| type | enum | NOT NULL | supervision, progress_review, viva, other |
| mode | enum | NOT NULL | in_person, online, hybrid |
| location | string | NULLABLE | |
| meeting_link | string | NULLABLE | |
| scheduled_at | datetime | NOT NULL | |
| duration_minutes | int | NULLABLE | |
| status | enum | NOT NULL | scheduled, in_progress, completed, cancelled |

**Indexes**: [student_id, status], scheduled_at, status, [created_by, status], [status, scheduled_at]

---

#### meeting_attendees
**Purpose**: Meeting participants

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| meeting_id | bigint | FK, NOT NULL | meetings.id |
| user_id | bigint | FK, NOT NULL | users.id |
| attended | boolean | NOT NULL | |

**Indexes**: UNIQUE(meeting_id, user_id), user_id, [meeting_id, attended]

---

#### meeting_action_items
**Purpose**: Action items from meetings

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| meeting_id | bigint | FK, NOT NULL | meetings.id |
| assigned_to | bigint | FK, NULLABLE | users.id |
| description | text | NOT NULL | |
| due_date | date | NULLABLE | |
| is_completed | boolean | NOT NULL | |
| completed_at | timestamp | NULLABLE | |

**Indexes**: [assigned_to, is_completed], [due_date, is_completed]

---

#### meeting_notes
**Purpose**: Additional meeting notes

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| meeting_id | bigint | FK, NOT NULL | meetings.id |
| added_by | bigint | FK, NOT NULL | users.id |
| content | text | NOT NULL | |
| is_private | boolean | NOT NULL | |

**Indexes**: meeting_id

---

### 7. File Management

#### folders
**Purpose**: Organize files into folders

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| student_id | bigint | FK, NOT NULL | students.id |
| parent_id | bigint | FK, NULLABLE | folders.id (self-ref) |
| name | string | NOT NULL | |
| path | string | NOT NULL | Full path |
| category | enum | NULLABLE | proposal, reports, thesis, simulation, data, images, references, other |

**Indexes**: [student_id, parent_id], [student_id, category], parent_id

---

#### files
**Purpose**: File storage with versioning

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| student_id | bigint | FK, NOT NULL | students.id |
| folder_id | bigint | FK, NULLABLE | folders.id |
| uploaded_by | bigint | FK, NOT NULL | users.id |
| name | string | NOT NULL | Stored filename |
| original_name | string | NOT NULL | Original filename |
| mime_type | string | NOT NULL | |
| size | bigint | NOT NULL | Bytes |
| disk | string | NOT NULL | Storage disk |
| path | string | NOT NULL | File path |
| description | text | NULLABLE | |
| version | int | NOT NULL | Version number |
| parent_file_id | bigint | FK, NULLABLE | files.id (previous version) |
| is_latest | boolean | NOT NULL | |

**Indexes**: [student_id, folder_id], is_latest, parent_file_id, mime_type, [uploaded_by, created_at], [student_id, mime_type], [parent_file_id, version]

---

#### file_activities
**Purpose**: File access tracking

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| file_id | bigint | FK, NOT NULL | files.id |
| user_id | bigint | FK, NULLABLE | users.id |
| action | enum | NOT NULL | uploaded, downloaded, viewed, updated, deleted, restored |
| ip_address | string | NULLABLE | |
| user_agent | string | NULLABLE | |
| metadata | json | NULLABLE | |

**Indexes**: [file_id, action], [user_id, action], [action, created_at]

---

### 8. AI Integration

#### ai_providers
**Purpose**: AI service configurations

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| name | string | NOT NULL | |
| slug | string | UNIQUE, NOT NULL | |
| api_key | string | NULLABLE | Encrypted |
| model | string | NULLABLE | |
| base_url | string | NULLABLE | |
| is_active | boolean | NOT NULL | |
| is_default | boolean | NOT NULL | |
| settings | json | NULLABLE | |

**Indexes**: slug, is_active

---

#### ai_conversations
**Purpose**: AI chat conversations

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NOT NULL | users.id |
| student_id | bigint | FK, NULLABLE | students.id |
| title | string | NULLABLE | |
| context_files | json | NULLABLE | File IDs |
| scope | enum | NOT NULL | general, student, folder, file |

**Indexes**: user_id, [user_id, created_at], student_id, [student_id, scope]

---

#### ai_messages
**Purpose**: Messages within AI conversations

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| ai_conversation_id | bigint | FK, NOT NULL | ai_conversations.id |
| role | enum | NOT NULL | user, assistant, system |
| content | text | NOT NULL | |
| metadata | json | NULLABLE | Tokens, model, timing |

**Indexes**: ai_conversation_id, role, [ai_conversation_id, created_at]

---

### 9. System & Administration

#### system_settings
**Purpose**: Application configuration

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| group | string | NOT NULL | storage, ai, general, email, notifications, security |
| key | string | UNIQUE, NOT NULL | |
| value | text | NULLABLE | |
| type | enum | NOT NULL | string, boolean, integer, json |
| description | text | NULLABLE | |

**Indexes**: group, key, [group, key]

---

#### audit_logs
**Purpose**: Comprehensive audit trail

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NULLABLE | users.id |
| action | string | NOT NULL | created, updated, deleted, login, logout, etc. |
| auditable_type | string | NULLABLE | Morph type |
| auditable_id | bigint | NULLABLE | Morph ID |
| old_values | json | NULLABLE | |
| new_values | json | NULLABLE | |
| ip_address | string | NULLABLE | |
| user_agent | string | NULLABLE | |

**Indexes**: action, [user_id, action], [auditable_type, auditable_id], [action, created_at]

---

#### notifications
**Purpose**: User notifications (Laravel default)

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | uuid | PK, NOT NULL | |
| type | string | NOT NULL | Notification class |
| notifiable_type | string | NOT NULL | Morph type |
| notifiable_id | bigint | NOT NULL | Morph ID |
| data | text | NOT NULL | JSON payload |
| read_at | timestamp | NULLABLE | |

**Indexes**: [notifiable_type, notifiable_id, read_at]

---

#### announcements
**Purpose**: System announcements

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| created_by | bigint | FK, NOT NULL | users.id |
| title | string | NOT NULL | |
| content | text | NOT NULL | |
| target_audience | enum | NOT NULL | all, admins, supervisors, students, cosupervisors |
| priority | enum | NOT NULL | low, normal, high, urgent |
| is_published | boolean | NOT NULL | |
| published_at | timestamp | NULLABLE | |
| expires_at | timestamp | NULLABLE | |
| view_count | int | NOT NULL | |

**Indexes**: [is_published, published_at], target_audience, [expires_at, is_published]

---

#### announcement_views
**Purpose**: Track announcement reads

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| announcement_id | bigint | FK, NOT NULL | announcements.id |
| user_id | bigint | FK, NOT NULL | users.id |
| viewed_at | timestamp | NOT NULL | |

**Indexes**: UNIQUE(announcement_id, user_id)

---

### 10. Revision Management

#### revisions
**Purpose**: Revision requests for any entity

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| revisable_type | string | NOT NULL | Morph type |
| revisable_id | bigint | NOT NULL | Morph ID |
| requested_by | bigint | FK, NOT NULL | users.id |
| assigned_to | bigint | FK, NOT NULL | users.id |
| description | text | NOT NULL | |
| status | enum | NOT NULL | pending, in_progress, completed, verified |
| priority | enum | NOT NULL | low, medium, high |
| due_date | date | NULLABLE | |
| completed_at | timestamp | NULLABLE | |
| verified_at | timestamp | NULLABLE | |

**Indexes**: status, [revisable_type, revisable_id, status], [requested_by, status], [assigned_to, status]

---

#### revision_comments
**Purpose**: Comments on revisions

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| revision_id | bigint | FK, NOT NULL | revisions.id |
| user_id | bigint | FK, NOT NULL | users.id |
| content | text | NOT NULL | |

---

### 11. Additional Features

#### tags
**Purpose**: Flexible tagging system

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| name | string | NOT NULL | |
| slug | string | UNIQUE, NOT NULL | |
| type | string | NOT NULL | research_area, skill, topic |
| color | string | NULLABLE | UI color |
| usage_count | int | NOT NULL | |

**Indexes**: slug, type, [type, usage_count]

---

#### taggables
**Purpose**: Polymorphic tag relationships

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| tag_id | bigint | FK, NOT NULL | tags.id |
| taggable_type | string | NOT NULL | |
| taggable_id | bigint | NOT NULL | |

**Indexes**: UNIQUE(tag_id, taggable_type, taggable_id), [taggable_type, taggable_id]

---

#### comments
**Purpose**: General commenting system

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NOT NULL | users.id |
| commentable_type | string | NOT NULL | Morph type |
| commentable_id | bigint | NOT NULL | Morph ID |
| parent_id | bigint | FK, NULLABLE | comments.id (threaded) |
| content | text | NOT NULL | |
| is_internal | boolean | NOT NULL | |

**Indexes**: [commentable_type, commentable_id], [user_id, created_at], parent_id

---

#### reminders
**Purpose**: Reminder notifications

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NOT NULL | users.id |
| title | string | NOT NULL | |
| description | text | NULLABLE | |
| remindable_type | string | NULLABLE | Morph type |
| remindable_id | bigint | NULLABLE | Morph ID |
| remind_at | datetime | NOT NULL | |
| sent_at | datetime | NULLABLE | |
| status | enum | NOT NULL | pending, sent, cancelled |
| type | enum | NOT NULL | email, in_app, both |

**Indexes**: [user_id, status], [status, remind_at], [remindable_type, remindable_id]

---

#### activities
**Purpose**: Unified activity timeline

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | uuid | PK, NOT NULL | |
| user_id | bigint | FK, NULLABLE | users.id |
| type | string | NOT NULL | Event type |
| subject_type | string | NOT NULL | Morph type |
| subject_id | bigint | NOT NULL | Morph ID |
| causer_type | string | NULLABLE | Morph type |
| causer_id | bigint | NULLABLE | Morph ID |
| metadata | json | NULLABLE | |
| batch_id | string | NULLABLE | Group related |

**Indexes**: [user_id, created_at], [subject_type, subject_id, created_at], [type, created_at], batch_id

---

#### webhooks
**Purpose**: Webhook integrations

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NOT NULL | users.id |
| name | string | NOT NULL | |
| url | string | NOT NULL | |
| events | json | NOT NULL | Subscribed events |
| secret | string | NULLABLE | Signature key |
| is_active | boolean | NOT NULL | |
| failure_count | int | NOT NULL | |
| last_triggered_at | timestamp | NULLABLE | |
| last_success_at | timestamp | NULLABLE | |

**Indexes**: [user_id, is_active]

---

#### webhook_deliveries
**Purpose**: Webhook delivery logs

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| webhook_id | bigint | FK, NOT NULL | webhooks.id |
| event | string | NOT NULL | |
| payload | text | NOT NULL | |
| status | enum | NOT NULL | pending, success, failed, retrying |
| attempt_count | int | NOT NULL | |
| response_code | int | NULLABLE | |
| response_body | text | NULLABLE | |
| delivered_at | timestamp | NULLABLE | |

**Indexes**: [webhook_id, status], [status, created_at]

---

#### subscriptions
**Purpose**: SaaS subscription management

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| user_id | bigint | FK, NOT NULL | users.id |
| name | string | NOT NULL | Plan name |
| stripe_id | string | NULLABLE | Stripe sub ID |
| stripe_status | string | NULLABLE | |
| stripe_price | string | NULLABLE | |
| quantity | int | NOT NULL | |
| trial_ends_at | timestamp | NULLABLE | |
| ends_at | timestamp | NULLABLE | |

**Indexes**: [user_id, stripe_status]

---

#### subscription_items
**Purpose**: Multiple prices per subscription

| Column | Type | Constraints | Notes |
|--------|------|-------------|-------|
| id | bigint | PK, AI | |
| subscription_id | bigint | FK, NOT NULL | subscriptions.id |
| stripe_id | string | NULLABLE | |
| stripe_product | string | NULLABLE | |
| stripe_price | string | NULLABLE | |
| quantity | int | NOT NULL | |

**Indexes**: subscription_id

---

## Performance Optimization

### Composite Indexes

Composite indexes are created for frequently queried column combinations:

- `[role, status]` on users - Filter by both role and status
- `[supervisor_id, status]` on students - Get supervisor's active students
- `[status, due_date]` on tasks - Get urgent/backlogged tasks
- `[student_id, type, status]` on progress_reports - Filter reports efficiently

### Foreign Key Indexes

All foreign keys have indexes for:
- Faster JOIN operations
- Cascade delete performance
- Referential integrity checks

### Soft Deletes

Tables with soft deletes:
- users, students, programmes, programme_categories
- journey_templates, research_journeys, stages
- tasks, progress_reports, files, folders
- meetings, ai_conversations, announcements, comments

### Full-Text Search Ready

Text fields that may benefit from full-text search:
- users.name, users.bio
- students.research_title, students.research_abstract
- tasks.title, tasks.description
- progress_reports.content
- files.name, files.description

## Data Integrity

### Cascade Behaviors

- **cascadeOnDelete**: Child records deleted when parent is deleted
  - user → sessions, api_tokens, comments
  - student → research_journey, tasks, progress_reports
  - meeting → meeting_attendees, meeting_action_items, meeting_notes

- **nullOnDelete**: Foreign key set to NULL when parent is deleted
  - programmes.category_id
  - tasks.milestone_id
  - files.folder_id

### Enum Constraints

All enum fields are validated at application level. Common enums:

| Enum | Values |
|------|--------|
| user.role | admin, supervisor, cosupervisor, student |
| user.status | active, inactive, pending |
| student.status | pending, active, on_hold, completed, withdrawn |
| task.status | backlog, planned, in_progress, waiting_review, revision, completed |
| task.priority | low, medium, high, urgent |
| meeting.mode | in_person, online, hybrid |
| meeting.status | scheduled, in_progress, completed, cancelled |
| report.status | draft, submitted, reviewed, revision_needed, accepted |

## SaaS Multi-Tenancy Considerations

### Current Schema Design

The current schema is designed to be easily adapted for multi-tenancy:

1. **Single-Database Approach**: Add `tenant_id` to all tables
2. **Composite Indexes**: All indexes include `(tenant_id, ...)`
3. **Global Scopes**: Automatic tenant filtering via Eloquent global scopes
4. **Tenant Isolation**: All queries automatically scoped to tenant

### Future Migration Path

To enable multi-tenancy:

1. Add `tenants` table (provided in ERD)
2. Add `tenant_id` foreign key to all relevant tables
3. Create composite indexes: `[(tenant_id, id), (tenant_id, created_at)]`
4. Implement tenant middleware for request scoping
5. Add tenant-aware queue workers

## Migration Strategy

### Order of Execution

1. Core tables: users, programmes, programme_categories
2. Extended profiles: students, supervisors
3. Templates: journey_templates, template_stages, template_milestones
4. Student journeys: research_journeys, stages, milestones
5. Work items: tasks, task_dependencies
6. Progress: progress_reports, revisions, revision_comments
7. Collaboration: meetings, meeting_attendees, meeting_action_items
8. Files: folders, files, file_activities
9. AI: ai_providers, ai_conversations, ai_messages
10. System: system_settings, audit_logs, notifications
11. Features: tags, taggables, comments, reminders, activities
12. Integrations: api_tokens, webhooks, webhook_deliveries
13. SaaS: subscriptions, subscription_items

## Backup & Recovery

### Critical Tables for Backup

1. **Daily**: users, students, tasks, progress_reports, meetings
2. **Weekly**: ai_conversations, ai_messages, audit_logs
3. **Monthly**: activities, notifications, webhook_deliveries

### Archive Strategy

- **audit_logs**: Archive after 1 year
- **activities**: Archive after 6 months
- **notifications**: Delete after read (configurable)
- **ai_messages**: Optional archiving per privacy policy

## Security Considerations

1. **API Keys**: Stored in `ai_providers` table, should be encrypted
2. **Passwords**: Hashed using Laravel's default (bcrypt/argon2)
3. **PII Data**: Identify fields containing personal data for GDPR compliance
4. **Audit Trail**: All sensitive actions logged in `audit_logs`
5. **File Access**: Logged in `file_activities`

---

*Document Version: 1.0*
*Last Updated: 2025-03-18*
*Laravel Version: 13*
*PHP Version: 8.3*
