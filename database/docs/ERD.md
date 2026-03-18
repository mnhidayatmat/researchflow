# ResearchFlow Database Schema - Entity Relationship Diagram

```
╔══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╗
║                                                      RESEARCHFLOW DATABASE ERD                                                   ║
║                                                  Student Research Supervision System                                              ║
╚══════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════╝

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  LEGEND                                                                                                                          │
│════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════│
│  ╔══════╗       ──────────────   One-to-Many Relationship                                                                       │
│  ║ PK  ║  ①    ╔═══════════╗    ╔═════════╗    ╔═════╗  Optional (0..*)                                                      │
│  ║ FK  ║  ①..¹ ║   TABLE   ║    ║ INDEX  ║    ║ *  ║  Many (Many)                                                          │
│  ║ UK  ║  ①..* ╠═══════════╣    ╚═════════╝    ╚═════╝  One (One)                                                             │
│  ║     ║       ║ (entity)  ║    {soft}        #  Composite                                                                   │
│  ╚══════╝       ╚═══════════╝    Soft Delete    †  Nullable                                                                  │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  IDENTITY & ACCESS CONTROL                                                                                                       │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

                    ┌──────────────────────────────────────────────────────────────────┐
                    │                        USERS (1)                                  │
                    │══════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                         │
                    │ ▲ name                                                            │
                    │ ▲ email (UK)                                                      │
                    │ ▲ password                                                        │
                    │ ▲ role [enum: admin|supervisor|cosupervisor|student]             │
                    │ † staff_id (UK)                                                   │
                    │ † matric_number (UK)                                              │
                    │ † phone                                                           │
                    │ † avatar                                                          │
                    │ † department                                                      │
                    │ † faculty                                                         │
                    │ ▲ status [enum: active|inactive|pending]                          │
                    │ † bio                                                             │
                    │ ▲ email_verified_at (†)                                           │
                    │ ▲ remember_token                                                   │
                    │ ▲ created_at, updated_at                                          │
                    │ ▲ deleted_at {soft}                                               │
                    │                                                                    │
                    │ INDEXES: role, status, email, staff_id, matric_number              │
                    └──────────────────────────────────────────────────────────────────┘
                                              │
         ┌────────────────────────────────────┼────────────────────────────────────┐
         │                                    │                                    │
         ▼                                    ▼                                    ▼
┌─────────────────────────┐    ┌─────────────────────────┐    ┌─────────────────────────┐
│   STUDENTS (1..*)       │    │  PASSWORD_RESET_TOKENS  │    │      SESSIONS (1..*)    │
│═════════════════════════│    │═════════════════════════│    │═════════════════════════│
│ ▲ id (PK)               │    │ ▲ email (PK)            │    │ ▲ id (PK)               │
│ ▲ user_id (FK→users) UK │    │ ▲ token                 │    │ ▲ user_id (FK→users) †  │
│ ▲ programme_id (FK)     │    │ ▲ created_at (†)        │    │ † ip_address            │
│ † supervisor_id (FK)    │    └─────────────────────────┘    │ † user_agent            │
│ † cosupervisor_id (FK)  │                                  │ ▲ payload               │
│ † research_title        │                                  │ ▲ last_activity         │
│ † research_abstract     │                                  │                         │
│ † intake                │                                  │ INDEXES: user_id,        │
│ † start_date            │                                  │         last_activity    │
│ † expected_completion   │                                  └─────────────────────────┘
│ † actual_completion     │
│ ▲ status [enum]         │
│ ▲ overall_progress      │
│ ▲ created_at, updated_at│
│ ▲ deleted_at {soft}     │
│                         │
│ INDEXES: status,         │
│          user_id         │
└─────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  PROGRAMME MANAGEMENT                                                                                                            │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────┐      ┌─────────────────────────────────────────┐
│    PROGRAMME_CATEGORIES (1..*)           │      │         PROGRAMMES (1..*)               │
│══════════════════════════════════════════│      │══════════════════════════════════════════│
│ ▲ id (PK)                               │      │ ▲ id (PK)                               │
│ ▲ name                                  │  ┌───│ ▲ category_id (FK→programme_categories) │
│ ▲ slug (UK)                             │  │   │ ▲ name                                  │
│ ▲ description (†)                       │  │   │ ▲ code (UK)                             │
│ ▲ sort_order                            │  │   │ ▲ slug (UK)                             │
│ ▲ is_active                             │  │   │ ▲ description (†)                       │
│ ▲ created_at, updated_at                │  │   │ ▲ duration_months                       │
│ ▲ deleted_at {soft}                     │  │   │ ▲ is_active                             │
│                                         │  │   │ ▲ sort_order                            │
│ INDEXES: slug, is_active                │  │   │ ▲ created_at, updated_at                │
└─────────────────────────────────────────┘  │   │ ▲ deleted_at {soft}                     │
                                              │   │                                         │
                                              │   │ INDEXES: code, slug, is_active,         │
                                              │   │         category_id                     │
                                              │   └─────────────────────────────────────────┘
                                              │
                                              └───────┬──────────────────────────────────────────┐
                                                      │                                          │
                                                      ▼                                          ▼
                                      ┌─────────────────────────────────┐    ┌─────────────────────────────────────────┐
                                      │      JOURNEY_TEMPLATES (1..*)   │    │            STUDENTS (see above)          │
                                      │═════════════════════════════════│    │─────────────────────────────────────────│
                                      │ ▲ id (PK)                       │    │ ▲ programme_id (FK→programmes)         │
                                      │ ▲ programme_id (FK→programmes)  │    └─────────────────────────────────────────┘
                                      │ ▲ name                          │
                                      │ † description                   │
                                      │ ▲ is_default                    │
                                      │ ▲ is_active                     │
                                      │ ▲ created_at, updated_at        │
                                      │ ▲ deleted_at {soft}             │
                                      │                                 │
                                      │ INDEXES: programme_id           │
                                      └─────────────────────────────────┘
                                                  │
                      ┌───────────────────────────┼───────────────────────────┐
                      │                           │                           │
                      ▼                           ▼                           ▼
        ┌─────────────────────────┐   ┌─────────────────────────┐   ┌─────────────────────────┐
        │   TEMPLATE_STAGES (1..*)│   │ TEMPLATE_MILESTONES (1..*)│  │   RESEARCH_JOURNEYS (1..*)│
        │═════════════════════════│   │═════════════════════════│   │═════════════════════════│
        │ ▲ id (PK)               │   │ ▲ id (PK)               │   │ ▲ id (PK)               │
        │ ▲ journey_template_id FK│   │ ▲ template_stage_id FK  │   │ ▲ student_id (FK→students)│
        │ ▲ name                  │   │ ▲ name                  │   │ † journey_template_id FK│
        │ † description           │   │ † description           │   │ ▲ name                  │
        │ ▲ sort_order            │   │ ▲ sort_order            │   │ † start_date            │
        │ † duration_weeks        │   │ † week_offset           │   │ † end_date              │
        │ ▲ created_at, updated_at│   │ ▲ created_at, updated_at│   │ ▲ progress              │
        └─────────────────────────┘   └─────────────────────────┘   │ ▲ status [enum]         │
                                                                       │ ▲ created_at, updated_at│
                                                                       │                         │
                                                                       │ INDEXES: student_id,    │
                                                                       │          journey_template│
                                                                       └─────────────────────────┘
                                                                                │
                                            ┌───────────────────────────────────┼───────────────────────────────┐
                                            │                                   │                               │
                                            ▼                                   ▼                               ▼
                            ┌─────────────────────────┐       ┌─────────────────────────┐    ┌─────────────────────────┐
                            │       STAGES (1..*)      │       │    MILESTONES (1..*)     │    │        TASKS (1..*)     │
                            │═════════════════════════│       │═════════════════════════│    │═════════════════════════│
                            │ ▲ id (PK)               │       │ ▲ id (PK)               │    │ ▲ id (PK)               │
                            │ ▲ research_journey_id FK│       │ ▲ stage_id (FK→stages)  │    │ ▲ student_id (FK→students)│
                            │ ▲ name                  │       │ ▲ name                  │    │ † milestone_id (FK→milestones)│
                            │ † description           │       │ † description           │    │ † assigned_by (FK→users) │
                            │ ▲ sort_order            │       │ ▲ sort_order            │    │ † parent_id (FK→tasks †) │
                            │ † start_date            │       │ † due_date              │    │ ▲ title                 │
                            │ † end_date              │       │ † completed_at          │    │ † description           │
                            │ ▲ status [enum]         │       │ ▲ status [enum]         │    │ ▲ status [enum]         │
                            │ ▲ progress              │       │ ▲ progress              │    │ ▲ priority [enum]       │
                            │ ▲ created_at, updated_at│       │ ▲ created_at, updated_at│    │ † start_date            │
                            └─────────────────────────┘       └─────────────────────────┘    │ † due_date              │
                            │                                                         │ † completed_at          │
                            │                                                         │ ▲ progress              │
                            │                                                         │ ▲ sort_order            │
                            │                                                         │ ▲ created_at, updated_at│
                            │                                                         │ ▲ deleted_at {soft}     │
                            │                                                         │                         │
                            │                                                         │ INDEXES: student_id,    │
                            │                                                         │   milestone_id, status, │
                            │                                                         │   parent_id             │
                            │                                                         └─────────────────────────┘
                            │                                                                     │
                            │                 ┌─────────────────────────────────────────────────────┤
                            │                 │                                                     │
                            │                 ▼                                                     ▼
                            │    ┌─────────────────────────────────┐                ┌─────────────────────────────────┐
                            │    │      TASK_DEPENDENCIES (1..*)   │                │        SUBTASKS (1..*)           │
                            │    │═════════════════════════════════│                │═══════════════════════════════════│
                            │    │ ▲ id (PK)                       │                │ (self-reference via parent_id in │
                            │    │ ▲ task_id (FK→tasks)            │                │  tasks table)                    │
                            │    │ ▲ depends_on_id (FK→tasks)      │                │                                 │
                            │    │ ▲ created_at                    │                │ - task.parent_id = task.id      │
                            │    │                                 │                │ - Supports infinite nesting     │
                            │    │ INDEXES: task_id, depends_on_id │                │ - Hierarchical queries via CTE  │
                            │    │ UNIQUE(task_id, depends_on_id)  │                └─────────────────────────────────┘
                            │    └─────────────────────────────────┘
                            │
                            ▼
                ┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
                │                                                              REVISIONS                                        │
                │═══════════════════════════════════════════════════════════════════════════════════════════════════════════════│
                │ ▲ id (PK)                                                                                                   │
                │ ▲ revisable_type (morph: Task|ProgressReport|File|Meeting)                                                  │
                │ ▲ revisable_id (morph)                                                                                      │
                │ ▲ requested_by (FK→users)                                                                                   │
                │ ▲ assigned_to (FK→users)                                                                                    │
                │ ▲ description                                                                                               │
                │ ▲ status [enum: pending|in_progress|completed|verified]                                                     │
                │ ▲ priority [enum: low|medium|high]                                                                         │
                │ † due_date                                                                                                  │
                │ † completed_at                                                                                              │
                │ † verified_at                                                                                               │
                │ ▲ created_at, updated_at                                                                                    │
                │                                                                                                             │
                │ INDEXES: status, revisable_type, revisable_id                                                               │
                │                                                                                                             │
                │                                    ┌──────────────────────────────────────┐                               │
                │                                    │      REVISION_COMMENTS (1..*)        │                               │
                │                                    │══════════════════════════════════════│                               │
                │                                    │ ▲ id (PK)                           │                               │
                │                                    │ ▲ revision_id (FK→revisions)        │                               │
                │                                    │ ▲ user_id (FK→users)                │                               │
                │                                    │ ▲ content                           │                               │
                │                                    │ ▲ created_at, updated_at            │                               │
                │                                    └──────────────────────────────────────┘                               │
                └─────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  ACADEMIC PROGRESS                                                                                                               │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                          PROGRESS_REPORTS                                                       │
│════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════│
│ ▲ id (PK)                                                                                                                        │
│ ▲ student_id (FK→students)                                                                                                       │
│ † reviewed_by (FK→users)                                                                                                         │
│ ▲ title                                                                                                                          │
│ ▲ content                                                                                                                        │
│ † achievements                                                                                                                   │
│ † challenges                                                                                                                     │
│ † next_steps                                                                                                                     │
│ ▲ type [enum: weekly|monthly|milestone|custom]                                                                                   │
│ ▲ status [enum: draft|submitted|reviewed|revision_needed|accepted]                                                               │
│ † period_start                                                                                                                   │
│ † period_end                                                                                                                     │
│ † supervisor_feedback                                                                                                            │
│ † submitted_at                                                                                                                   │
│ † reviewed_at                                                                                                                    │
│ ▲ created_at, updated_at                                                                                                         │
│ ▲ deleted_at {soft}                                                                                                              │
│                                                                                                                                  │
│ INDEXES: student_id, status, type, #student_status, period_start                                                                 │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  COLLABORATION                                                                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────────────────────────────────┐
                    │                           MEETINGS                                    │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ student_id (FK→students)                                           │
                    │ ▲ created_by (FK→users)                                              │
                    │ ▲ title                                                               │
                    │ † agenda                                                              │
                    │ † notes                                                               │
                    │ ▲ type [enum: supervision|progress_review|viva|other]               │
                    │ ▲ mode [enum: in_person|online|hybrid]                               │
                    │ † location                                                            │
                    │ † meeting_link                                                        │
                    │ ▲ scheduled_at                                                        │
                    │ † duration_minutes                                                    │
                    │ ▲ status [enum: scheduled|in_progress|completed|cancelled]            │
                    │ ▲ created_at, updated_at                                              │
                    │ ▲ deleted_at {soft}                                                   │
                    │                                                                        │
                    │ INDEXES: #student_status, scheduled_at, status                         │
                    └────────────────────────────────────────────────────────────────────┘
                                                  │
                      ┌───────────────────────────┼───────────────────────────┐
                      │                           │                           │
                      ▼                           ▼                           ▼
        ┌─────────────────────────┐   ┌─────────────────────────┐   ┌─────────────────────────┐
        │   MEETING_ATTENDEES (1..*)│  │  MEETING_ACTION_ITEMS   │  │    MEETING_NOTES (1..*) │
        │═════════════════════════│   │═════════════════════════│   │═════════════════════════│
        │ ▲ id (PK)               │   │ ▲ id (PK)               │   │ ▲ id (PK)               │
        │ ▲ meeting_id (FK)       │   │ ▲ meeting_id (FK)       │   │ ▲ meeting_id (FK)       │
        │ ▲ user_id (FK→users)    │   │ † assigned_to (FK→users)│   │ ▲ added_by (FK→users)   │
        │ ▲ attended (boolean)    │   │ ▲ description           │   │ ▲ content               │
        │ ▲ created_at            │   │ † due_date              │   │ ▲ created_at            │
        │                         │   │ ▲ is_completed          │   └─────────────────────────┘
        │ UNIQUE(meeting_id, user)│   │ † completed_at          │
        └─────────────────────────┘   │ ▲ created_at            │
                                      └─────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  FILE MANAGEMENT                                                                                                                │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────────────────────────────────┐
                    │                            FOLDERS                                   │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ student_id (FK→students)                                           │
                    │ † parent_id (FK→folders self-reference)                              │
                    │ ▲ name                                                                │
                    │ ▲ path                                                                │
                    │ † category [enum: proposal|reports|thesis|simulation|data|images|    │
                    │               references|other]                                       │
                    │ ▲ created_at, updated_at                                              │
                    │                                                                        │
                    │ INDEXES: #student_parent, parent_id                                   │
                    └────────────────────────────────────────────────────────────────────┘
                                                  │
                                                  ▼
                    ┌────────────────────────────────────────────────────────────────────┐
                    │                             FILES                                    │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ student_id (FK→students)                                           │
                    │ † folder_id (FK→folders)                                             │
                    │ ▲ uploaded_by (FK→users)                                             │
                    │ ▲ name                                                                │
                    │ ▲ original_name                                                       │
                    │ ▲ mime_type                                                           │
                    │ ▲ size                                                                │
                    │ ▲ disk                                                                │
                    │ ▲ path                                                                │
                    │ † description                                                         │
                    │ ▲ version                                                             │
                    │ † parent_file_id (FK→files self-reference for versioning)            │
                    │ ▲ is_latest                                                           │
                    │ ▲ created_at, updated_at                                              │
                    │ ▲ deleted_at {soft}                                                   │
                    │                                                                        │
                    │ INDEXES: #student_folder, is_latest, parent_file_id, mime_type        │
                    └────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  AI INTEGRATION                                                                                                                 │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────────────────────────────────┐
                    │                          AI_PROVIDERS                                │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ name                                                                │
                    │ ▲ slug (UK)                                                           │
                    │ † api_key                                                             │
                    │ † model                                                               │
                    │ † base_url                                                            │
                    │ ▲ is_active                                                           │
                    │ ▲ is_default                                                          │
                    │ † settings (JSON)                                                     │
                    │ ▲ created_at, updated_at                                              │
                    │                                                                        │
                    │ INDEXES: slug, is_active                                              │
                    └────────────────────────────────────────────────────────────────────┘
                                                  │
                                                  ▼
                    ┌────────────────────────────────────────────────────────────────────┐
                    │                       AI_CONVERSATIONS                              │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ user_id (FK→users)                                                 │
                    │ † student_id (FK→students)                                           │
                    │ † title                                                               │
                    │ † context_files (JSON: array of file IDs)                            │
                    │ ▲ scope [enum: general|student|folder|file]                          │
                    │ ▲ created_at, updated_at                                              │
                    │ ▲ deleted_at {soft}                                                   │
                    │                                                                        │
                    │ INDEXES: user_id, #user_student, scope                                │
                    └────────────────────────────────────────────────────────────────────┘
                                                  │
                                                  ▼
                    ┌────────────────────────────────────────────────────────────────────┐
                    │                          AI_MESSAGES                                │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ ai_conversation_id (FK→ai_conversations)                          │
                    │ ▲ role [enum: user|assistant|system]                                 │
                    │ ▲ content                                                             │
                    │ † metadata (JSON: tokens, model, timing, etc.)                       │
                    │ ▲ created_at, updated_at                                              │
                    │                                                                        │
                    │ INDEXES: ai_conversation_id, role                                     │
                    └────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  SYSTEM & ADMINISTRATION                                                                                                        │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────────────────────────────────┐
                    │                         SYSTEM_SETTINGS                             │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ group [storage|ai|general|email|notifications|security]           │
                    │ ▲ key (UK)                                                            │
                    │ † value                                                               │
                    │ ▲ type [string|boolean|integer|json]                                 │
                    │ † description                                                         │
                    │ ▲ created_at, updated_at                                              │
                    │                                                                        │
                    │ INDEXES: group, key, #group_key                                       │
                    └────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────────────────────────────────┐
                    │                           AUDIT_LOGS                                 │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ † user_id (FK→users)                                                 │
                    │ ▲ action [created|updated|deleted|login|logout|download|upload|     │
                    │           viewed|export|approved|rejected]                           │
                    │ † auditable_type (morph)                                             │
                    │ † auditable_id (morph)                                               │
                    │ † old_values (JSON)                                                  │
                    │ † new_values (JSON)                                                  │
                    │ † ip_address                                                          │
                    │ † user_agent                                                          │
                    │ ▲ created_at, updated_at                                              │
                    │                                                                        │
                    │ INDEXES: #action_user, action, auditable_type_id, created_at          │
                    └────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────────────────────────────────┐
                    │                         NOTIFICATIONS                               │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK - UUID)                                                     │
                    │ ▲ type                                                                │
                    │ ▲ notifiable_type (morph)                                            │
                    │ ▲ notifiable_id (morph)                                              │
                    │ ▲ data (JSON)                                                         │
                    │ † read_at                                                             │
                    │ ▲ created_at, updated_at                                              │
                    │                                                                        │
                    │ INDEXES: notifiable_type_id, read_at                                  │
                    └────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│  SAAS MULTI-TENANCY (FUTURE ENHANCEMENT)                                                                                      │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                           TENANTS                               │
│════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════│
│ ▲ id (PK) - UUID                                                                                                                │
│ ▲ name                                                                                                                           │
│ ▲ slug (UK)                                                                                                                      │
│ ▲ plan [enum: free|trial|basic|professional|enterprise]                                                                         │
│ † max_users                                                                                                                      │
│ † max_storage_mb                                                                                                                 │
│ ▲ is_active                                                                                                                      │
│ ▲ trial_ends_at                                                                                                                  │
│ ▲ created_at, updated_at                                                                                                        │
│ ▲ deleted_at {soft}                                                                                                              │
│                                                                                                                                  │
│ INDEXES: slug, plan, is_active                                                                                                  │
└─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┘
                                        │
                                        ├─── All tables would add: tenant_id (FK→tenants)
                                        │   - With composite indexes: (tenant_id, id)
                                        │   - Global queries scoped by tenant_id
                                        │
                                        ▼
                    ┌────────────────────────────────────────────────────────────────────┐
                    │                        TENANT_SETTINGS                              │
                    │══════════════════════════════════════════════════════════════════════│
                    │ ▲ id (PK)                                                            │
                    │ ▲ tenant_id (FK→tenants)                                             │
                    │ ▲ settings (JSON)                                                     │
                    │ ▲ created_at, updated_at                                              │
                    └────────────────────────────────────────────────────────────────────┘

═════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
                                                    RELATIONSHIP SUMMARY
═════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ TABLE                    │ RELATIONSHIPS (One → Many)                                                                         │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ users                    │ → students, sessions, password_reset_tokens, audit_logs, ai_conversations,   │
│                         │   progress_reports(reviewed_by), tasks(assigned_by), meetings(created_by),    │
│                         │   meeting_attendees, meeting_action_items(assigned_to), revisions,            │
│                         │   revision_comments, files(uploaded_by), meeting_notes                         │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ students                 │ → research_journeys, tasks, progress_reports, files, folders, meetings,        │
│                         │   ai_conversations(student_id)                                              │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ programmes               │ → students, journey_templates                                              │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ programme_categories    │ → programmes                                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ journey_templates        │ → template_stages, research_journeys                                       │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ template_stages          │ → template_milestones                                                      │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ research_journeys        │ → stages                                                                    │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ stages                   │ → milestones                                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ milestones               │ → tasks                                                                     │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ tasks                    │ → task_dependencies (task_id, depends_on_id), subtasks (self-ref via parent)│
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ meetings                 │ → meeting_attendees, meeting_action_items, meeting_notes                    │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ folders                  │ → subfolders (self-ref via parent_id), files                                │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ files                    │ → file_versions (self-ref via parent_file_id)                               │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ revisions                │ → revision_comments                                                         │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ ai_conversations         │ → ai_messages                                                               │
├─────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Many-to-Many            │ students ↔ users (supervisor, cosupervisor via separate FKs)                 │
│                          │ tasks ↔ tasks (dependencies via task_dependencies)                          │
│                          │ users ↔ meetings (attendees via meeting_attendees)                          │
═════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════════
