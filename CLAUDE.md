# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**ResearchFlow** is a Student Research Supervision Management System built with Laravel 13 and PHP 8.3. It manages the complete research supervision lifecycle for postgraduate students, including task management, progress reporting, meetings, file management, and AI-powered assistance.

## Development Commands

```bash
# Initial setup (installs dependencies, generates .env, runs migrations, builds assets)
composer run setup

# Full development environment (server, queue, logs, vite in parallel)
composer run dev

# Individual services
php artisan serve              # Laravel server on port 8000
php artisan queue:listen       # Queue worker
php artisan pail               # Log monitoring
npm run dev                    # Vite dev server

# Build production assets
npm run build

# Run tests
composer test
php artisan test

# Laravel Pint (code formatting)
./vendor/bin/pint

# Database
php artisan migrate:fresh --seed
```

## Architecture

### Multi-Role System

Users have a single `role` field with four possible values:
- **admin** - Full system access
- **supervisor** - Primary academic supervisor
- **cosupervisor** - Secondary academic supervisor
- **student** - Research student

Routes are organized by role with middleware protection:
- `admin/*` → `middleware(['auth', 'role:admin'])`
- `supervisor/*` → `middleware(['auth', 'role:supervisor,cosupervisor'])`
- `student/*` → `middleware(['auth', 'role:student'])`

### Authorization Patterns

1. **Role-based routing** - Uses `RoleMiddleware` for role checking
2. **Policy-based access** - Laravel policies for resource-level authorization (Student, Task, ProgressReport)
3. **Audit logging** - `AuditActivity` middleware tracks user actions

Key authorization rules:
- Admins can access everything
- Supervisors can view/manage their assigned students
- Students can only view their own data
- Cosupervisors share supervisor privileges for their assigned students

### Core Models and Relationships

```
User (has role field)
├── Student (links user to programme, has supervisor_id and cosupervisor_id)
│   └── ResearchJourney (student's research timeline)
│       ├── Stage (journey phases)
│       │   └── Milestone (stage milestones)
│       │       └── Task (hierarchical, belongs to milestone)
│       │           ├── TaskDependency (depends_on relationship)
│       │           └── Revision (task revision history)
├── Supervisor (extended supervisor profile with capacity tracking)
├── ProgrammeCategory (programme categorization)
│   └── Programme (academic programmes)
├── JourneyTemplate (reusable journey templates per programme)
│   ├── TemplateStage (template phases)
│   │   └── TemplateMilestone (template milestones)
│   └── ResearchJourney (instantiated templates)
├── ProgressReport (periodic progress submissions)
│   └── Revision (report revision history)
├── Meeting (supervisory meetings)
│   ├── MeetingAttendee (meeting participants)
│   ├── MeetingActionItem (action items from meetings)
│   └── MeetingNote (additional notes)
├── File/Folder (document management with versioning)
│   └── FileActivity (access tracking)
├── AiConversation/AiMessage (AI chat integration)
├── Tag (flexible tagging system)
│   └── Taggable (polymorphic tag relationships)
├── Comment (general commenting with threading)
├── Reminder (reminder notifications)
├── Announcement (system announcements)
│   └── AnnouncementView (read tracking)
├── Activity (unified activity timeline)
├── Webhook (webhook integrations)
│   └── WebhookDelivery (delivery logs)
├── ApiToken (API authentication)
└── Subscription (SaaS subscriptions)
    └── SubscriptionItem (multiple prices per subscription)
```

### Database Schema

The complete database schema is documented in `database/docs/`:
- **ERD.md** - Full Entity Relationship Diagram
- **SCHEMA_DOCUMENTATION.md** - Detailed table documentation with columns, indexes, and relationships
- **MIGRATION_INDEX.md** - Migration file reference and execution order

**Total Tables**: 45 across 12 functional domains

#### Key Tables by Category

| Category | Tables |
|----------|--------|
| Identity & Access | users, students, supervisors, sessions, password_reset_tokens, api_tokens |
| Programme Management | programme_categories, programmes |
| Research Journey | journey_templates, template_stages, template_milestones, research_journeys, stages, milestones |
| Task Management | tasks, task_dependencies |
| Progress Tracking | progress_reports, revisions, revision_comments |
| Collaboration | meetings, meeting_attendees, meeting_action_items, meeting_notes, comments |
| File Management | folders, files, file_activities |
| AI Integration | ai_providers, ai_conversations, ai_messages, ai_embeddings |
| System & Admin | system_settings, audit_logs, notifications, announcements |
| Features | tags, taggables, reminders, activities |
| Integrations | webhooks, webhook_deliveries |
| SaaS | subscriptions, subscription_items |

#### Enum Reference

| Field | Values |
|-------|--------|
| user.role | admin, supervisor, cosupervisor, student |
| user.status | active, inactive, pending |
| student.status | pending, active, on_hold, completed, withdrawn |
| task.status | backlog, planned, in_progress, waiting_review, revision, completed |
| task.priority | low, medium, high, urgent |
| meeting.mode | in_person, online, hybrid |
| meeting.status | scheduled, in_progress, completed, cancelled |
| report.status | draft, submitted, reviewed, revision_needed, accepted |
| revision.status | pending, in_progress, completed, verified |

#### Performance Indexes

The schema includes 30+ composite indexes for optimal query performance:
- `[role, status]` on users
- `[supervisor_id, status]` on students
- `[student_id, status]` on tasks
- `[status, due_date]` on tasks
- `[student_id, type, status]` on progress_reports

See `database/docs/SCHEMA_DOCUMENTATION.md` for complete index reference.

### Task Status Workflow

Tasks follow a strict status progression:
```
backlog → planned → in_progress → waiting_review → revision → completed
```

The `TaskPolicy` defines who can review tasks (only supervisors/admins).

### Route Organization

**routes/web.php** is organized into four sections:

1. **Guest routes** - Login/register (lines 15-21)
2. **Admin routes** - `/admin/*` prefix (lines 37-54)
3. **Supervisor routes** - `/supervisor/*` prefix (lines 56-61)
4. **Student routes** - `/student/*` prefix (lines 63-66)
5. **Shared resource routes** - Policy-based access for tasks, reports, meetings, files (lines 68-109)

**routes/api.php** - API endpoints for frontend (Kanban, Gantt, AI Chat, Notifications)

### Blade Component Structure

```
resources/views/
├── layouts/
│   ├── app.blade.php          # Main authenticated layout
│   ├── sidebar.blade.php      # Navigation sidebar
│   ├── topbar.blade.php       # Top navigation bar
│   └── guest.blade.php        # Non-authenticated layout
├── components/
│   ├── layouts/               # Layout wrappers
│   ├── accordion.blade.php    # Collapsible content sections
│   ├── alert.blade.php        # Alert/toast notifications
│   ├── avatar.blade.php       # User avatar display
│   ├── badge.blade.php        # Status/information badges
│   ├── button.blade.php       # Button component
│   ├── card.blade.php         # Generic card container
│   ├── command-menu.blade.php # Command palette (Cmd+K)
│   ├── dropdown.blade.php     # Dropdown menu
│   ├── empty-state.blade.php  # Empty state placeholder
│   ├── input.blade.php        # Form input field
│   ├── input-group.blade.php  # Grouped form inputs
│   ├── modal.blade.php        # Modal dialog
│   ├── nav-item.blade.php     # Navigation item
│   ├── progress.blade.php     # Progress bar
│   ├── select.blade.php       # Dropdown select
│   ├── stat-card.blade.php    # Dashboard stat cards
│   ├── status-badge.blade.php # Status indicator
│   ├── table.blade.php        # Data table
│   ├── tabs.blade.php         # Tab navigation
│   ├── textarea.blade.php     # Multi-line text input
│   └── toggle.blade.php       # Toggle switch
└── {role}/                    # Role-specific views
    ├── dashboard.blade.php
    ├── admin/
    │   └── settings.blade.php  # Admin settings page
    ├── student/
    │   └── workspace.blade.php # Student workspace
    ├── supervisor/
    │   └── panel.blade.php     # Supervisor panel
    └── tasks/
        ├── kanban.blade.php    # Kanban board view
        ├── gantt.blade.php     # Gantt chart view
        └── timeline.blade.php  # Timeline view
```

### Blade UI System

See the comprehensive Blade UI documentation below for component usage examples and design principles.

### Design System & Color Palette

The app uses Tailwind CSS via CDN with a custom color palette defined in `layouts/app.blade.php`. The design is inspired by Claude/Notion with a clean, minimal aesthetic.

#### Core Colors (Claude-inspired)
```
Surface & Background:
- surface: #FAFAF9 - Very light gray background
- card: #FFFFFF - White card backgrounds
- border: #E5E5E4 - Subtle borders
- border-light: #F5F5F4 - Lighter borders for dividers

Text Colors:
- primary: #1C1917 - Main text (near black)
- secondary: #78716C - Muted text (gray)
- tertiary: #A8A29E - Placeholder text (light gray)

Accent & Actions:
- accent: #D97706 - Amber/Orange for CTAs (similar to Claude's accent)
- accent-light: #FEF3C7 - Light amber for backgrounds
- accent/20: 20% opacity accent for gradients
- accent/10: 10% opacity accent for subtle backgrounds

Status Colors:
- success: #059669 - Green for success states
- success-light: #D1FAE5 - Light green for backgrounds
- success/10: 10% opacity success for subtle backgrounds
- success/20: 20% opacity success for gradients
- warning: #F59E0B - Amber for warnings
- warning-light: #FEF3C7 - Light amber for backgrounds
- warning/10: 10% opacity warning for subtle backgrounds
- warning/20: 20% opacity warning for gradients
- danger: #DC2626 - Red for errors
- danger-light: #FEE2E2 - Light red for backgrounds
- info: #2563EB - Blue for information
- info-light: #DBEAFE - Light blue for backgrounds
- info/10: 10% opacity info for subtle backgrounds
- info/20: 20% opacity info for gradients
```

#### Border Colors (Hover States)
```
- border-accent/30: 30% opacity accent for hover borders
- border-success/30: 30% opacity success for hover borders
- border-warning/30: 30% opacity warning for hover borders
- border-info/30: 30% opacity info for hover borders
```

#### Design Principles (Premium Dashboard Style)
- **Minimal borders**: Use `border border-border` for cards, `border-light` for subtle dividers
- **Soft shadows**: Use `shadow-soft` for hover effects, avoid heavy shadows
- **Hover states**: Use `hover:bg-surface` for interactive elements, add color transitions
- **Rounded corners**: `rounded-2xl` for cards, `rounded-xl` for buttons/containers, `rounded-lg` for small elements
- **Spacing**: Use `gap-4` for tight spacing, `gap-6` for comfortable spacing, `p-6` for card padding
- **Typography**: Clear hierarchy with font weights (regular, medium, semibold, bold)
- **Animations**: Subtle scale transforms, color transitions, duration-300 for smooth effects

#### Premium Dashboard Patterns

**KPI/Stat Cards (with hover effects):**
```blade
<div class="group relative bg-card rounded-2xl p-6 border border-border
            hover:border-accent/30 hover:shadow-soft transition-all duration-300">
    <div class="flex items-start justify-between">
        <!-- Icon with gradient background -->
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10
                    flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="..."/>
            </svg>
        </div>
        <!-- Optional trend indicator -->
        <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-success/10 text-success text-xs font-semibold">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            <span>+12%</span>
        </div>
    </div>
    <div class="mt-4">
        <div class="flex items-baseline gap-2">
            <p class="text-lg font-bold text-primary">{{ $value }}</p>
            <p class="text-xs text-secondary">label</p>
        </div>
        <p class="text-xs text-secondary mt-1">Description</p>
    </div>
</div>
```

**Card with Header Section:**
```blade
<div class="bg-card rounded-2xl border border-border overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-5 border-b border-border flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-primary">Title</h2>
            <p class="text-sm text-secondary mt-0.5">Subtitle</p>
        </div>
        <!-- Optional action link -->
        <a href="#" class="text-sm text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
            View all
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
    <!-- Content -->
    <div class="p-6">
        <!-- Content here -->
    </div>
</div>
```

**List Items with Hover (Students, Tasks):**
```blade
<div class="divide-y divide-border">
    @foreach($items as $item)
    <a href="#" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
        <!-- Avatar with status indicator -->
        <div class="relative">
            <x-avatar :name="$item->name" size="md" />
            <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-card
                         {{ $item->status === 'active' ? 'bg-success' : 'bg-tertiary' }}"></span>
        </div>
        <!-- Content -->
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-primary group-hover:text-accent transition-colors truncate">
                {{ $item->title }}
            </p>
            <p class="text-sm text-secondary">{{ $item->subtitle }}</p>
        </div>
        <!-- Right side actions/stats -->
        <div class="flex items-center gap-4">
            <!-- Progress bar -->
            <div class="w-20 h-2 bg-border-light rounded-full overflow-hidden">
                <div class="h-full bg-accent rounded-full" style="width: {{ $progress }}%"></div>
            </div>
            <!-- Arrow icon -->
            <svg class="w-5 h-5 text-tertiary group-hover:text-accent transition-colors"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </a>
    @endforeach
</div>
```

**Activity Feed Items:**
```blade
<div class="space-y-4">
    @foreach($activities as $activity)
    <div class="flex items-start gap-3 group">
        <!-- Icon with gradient background -->
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent/20 to-accent/10
                    flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="..."/>
            </svg>
        </div>
        <!-- Content -->
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-primary truncate">{{ $activity->title }}</p>
            <p class="text-xs text-secondary">{{ $activity->subtitle }}</p>
            <p class="text-[10px] text-tertiary mt-1">{{ $activity->time }}</p>
        </div>
    </div>
    @endforeach
</div>
```

**Quick Action Buttons (with icon boxes):**
```blade
<a href="#" class="flex items-center gap-3 p-3 rounded-xl text-secondary
             hover:bg-surface hover:text-primary transition-all group">
    <!-- Icon box with hover effect -->
    <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center
                group-hover:bg-accent/20 transition-colors">
        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="..."/>
        </svg>
    </div>
    <!-- Text content -->
    <div class="flex-1">
        <p class="text-sm font-medium">Action Title</p>
        <p class="text-xs text-tertiary">Brief description</p>
    </div>
    <!-- Arrow icon -->
    <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
```

**Empty States (Premium Style):**
```blade
<div class="flex flex-col items-center justify-center text-center py-12 px-6">
    <!-- Large icon with gradient -->
    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-accent/15 to-accent/5
                flex items-center justify-center mb-6">
        <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="..."/>
        </svg>
    </div>
    <!-- Title -->
    <h3 class="text-xl font-semibold text-primary mb-2">Empty State Title</h3>
    <!-- Description -->
    <p class="text-secondary max-w-sm mb-8">Detailed description of the empty state and what to do next.</p>
    <!-- Actions -->
    <div class="flex items-center gap-3">
        <a href="#" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold
                      bg-accent text-white hover:bg-amber-700 transition-all shadow-sm hover:shadow">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Primary Action
        </a>
        <a href="#" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium
                      text-secondary hover:text-primary border border-border hover:bg-surface transition-all">
            Secondary Action
        </a>
    </div>
</div>
```

**Alert/Status Badge (Pill style):**
```blade
<div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-warning/10 text-warning text-sm font-medium">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="..."/>
    </svg>
    <span>{{ $count }} items</span>
</div>
```

#### Animation & Transitions
- **Scale transforms**: `group-hover:scale-110` for icons
- **Color transitions**: `transition-colors duration-300` for text/icons
- **Background transitions**: `transition-all duration-300` for cards
- **Pulse animation**: `animate-pulse` for notification dots
- **Hover lifts**: `hover:-translate-y-0.5` for button lift effect

#### Responsive Grid Patterns
```blade
<!-- 4-column KPI grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

<!-- Main content grid (2:1 split) -->
<div class="grid lg:grid-cols-3 gap-6">
    <!-- 2 columns for main content -->
    <div class="lg:col-span-2">
        <!-- Main content -->
    </div>
    <!-- 1 column for sidebar -->
    <div>
        <!-- Sidebar content -->
    </div>
</div>
```

#### Icon Sizing Reference
- **Large icons (feature cards)**: `w-7 h-7` (28px) in 14x14 container
- **Medium icons (list items)**: `w-5 h-5` (20px) in 10x10 container
- **Small icons (buttons)**: `w-4 h-4` (16px) in small containers
- **Stroke width**: `1.5` for premium feel (vs 2 for standard)

#### Spacing Standards
- **Card padding**: `p-6` (24px all around)
- **Section gaps**: `gap-4` (16px) for tight, `gap-6` (24px) for comfortable
- **Page spacing**: `space-y-8` for major sections
- **Header padding**: `py-5` (20px) for card headers
- **List item padding**: `p-5` (20px) for clickable rows

#### Common Patterns

**KPI Cards:**
```blade
<div class="bg-card rounded-lg p-5 border border-border hover:shadow-soft transition-all">
    <div class="w-10 h-10 rounded-lg bg-accent-light flex items-center justify-center">
        <!-- Icon with text-accent -->
    </div>
    <div class="mt-3">
        <p class="text-lg font-semibold text-primary">{{ $value }}</p>
        <p class="text-xs text-secondary">{{ $label }}</p>
    </div>
</div>
```

**Empty States:**
```blade
<div class="flex flex-col items-center justify-center py-16 px-6 text-center">
    <div class="w-16 h-16 rounded-xl bg-accent-light flex items-center justify-center mb-6">
        <!-- Icon -->
    </div>
    <h3 class="text-lg font-semibold text-primary mb-2">Title</h3>
    <p class="text-secondary max-w-sm mb-6">Description</p>
    <!-- Actions -->
</div>
```

**Activity Feed Items:**
```blade
<div class="flex items-start gap-3 p-3 rounded-lg bg-surface">
    <div class="w-8 h-8 rounded-lg bg-accent-light flex items-center justify-center">
        <!-- Icon -->
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-primary truncate">Title</p>
        <p class="text-xs text-secondary">Subtitle</p>
        <p class="text-[10px] text-tertiary mt-1">Time</p>
    </div>
</div>
```

### Blade UI System

This application uses a custom Blade UI system with a clean, minimal design similar to Notion/Claude. All components are mobile-responsive and built with Tailwind CSS.

## Layout Usage

```blade
<x-layouts.app title="Page Title" :header="'Page Name'">
    {{-- Your content here --}}
</x-layouts.app>
```

## Available Components

| Component | File | Purpose |
|-----------|------|---------|
| Card | `card.blade.php` | Container with variants (bordered, elevated, surface) |
| Stat Card | `stat-card.blade.php` | Dashboard stats with change indicators |
| Table | `table.blade.php` | Clean tables with empty states |
| Modal | `modal.blade.php` | Dialog (xs/sm/md/lg/xl/full) |
| Badge | `badge.blade.php` | Labels with variants (with dot option) |
| Status Badge | `status-badge.blade.php` | Pre-configured status badges |
| Alert | `alert.blade.php` | Dismissible alerts (info/success/warning/danger) |
| Progress | `progress.blade.php` | Progress bars (xs/sm/md/lg/xl) |
| Avatar | `avatar.blade.php` | User initials with status indicator |
| Tabs | `tabs.blade.php` | Navigation (pills/underline variants) |
| Input Group | `input-group.blade.php` | Labeled inputs with error/hint support |
| Select | `select.blade.php` | Enhanced select dropdown |
| Toggle | `toggle.blade.php` | Switch-style checkbox |
| Empty State | `empty-state.blade.php` | Consistent empty data display |
| Dropdown | `dropdown.blade.php` | Position-aware dropdown menu |
| Accordion | `accordion.blade.php` | Collapsible content sections |
| Command Menu | `command-menu.blade.php` | Keyboard-driven search (⌘K) |

## Component Examples

### Card
```blade
<x-card title="Title" :subtitle="'Optional'" :padding="'loose'">
    Content
</x-card>
```

### Stat Card
```blade
<x-stat-card
    title="Total Students"
    :value="150"
    :change="'+12%'"
    icon="M12 4.354a4 4 0 110 5.292..."
    variant="accent"
    :href="route('admin.students.index')"
/>
```

### Status Badge
```blade
<x-status-badge :status="$task->status" size="sm" />
<!-- Supports: backlog, planned, in_progress, waiting_review, revision, completed, pending, active, inactive, draft, submitted, etc. -->
```

### Modal
```blade
<x-modal title="Dialog Title" size="md">
    Content
    <x-slot:footer>
        <button>Cancel</button>
        <button>Confirm</button>
    </x-slot:footer>
</x-modal>
```

### Table
```blade
<x-table :headers="['Name', 'Email', 'Status', ['label' => 'Actions', 'align' => 'right']]">
    @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><x-status-badge :status="$user->status" /></td>
            <td class="text-right"><a href="#">View</a></td>
        </tr>
    @endforeach
</x-table>
```

### Progress
```blade
<x-progress :value="75" size="md" variant="success" :show-label="true" />
```

### Avatar
```blade
<x-avatar :name="$user->name" size="md" status="online" />
<!-- Sizes: xs, sm, md, lg, xl, 2xl -->
<!-- Status: online, offline, away, busy, none -->
```

### Form Components
```blade
<x-input-group label="Name" name="name" :value="$name" :error="$errors->first('name')" hint="Enter name" />

<x-select label="Role" name="role" :options="['admin' => 'Admin']" :value="$role" />

<x-toggle name="active" :label="'Active'" :checked="true" description="Enable account" />
```

### Tabs
```blade
<x-tabs :tabs="['overview' => 'Overview', 'tasks' => 'Tasks']" :active="'overview'" variant="pills" />
```

### Alert
```blade
<x-alert variant="info" :dismissible="true">
    <strong>Info:</strong> Your message here.
</x-alert>
```

### Empty State
```blade
<x-empty-state title="No data" description="Get started by creating new item.">
    <x-slot:action>
        <x-button href="#">Create New</x-button>
    </x-slot:action>
</x-empty-state>
```

## Dashboard Templates

- **Admin** (`admin/dashboard.blade.php`) - Stats, pending approvals, task chart, student table
- **Supervisor** (`supervisor/dashboard.blade.php`) - Students list, pending reviews
- **Student** (`student/dashboard.blade.php`) - Progress, tasks, journey timeline, meetings

## Workspace Templates

- **Student Workspace** (`student/workspace.blade.php`) - Tabbed interface (Overview, Tasks, Timeline, Reports, Meetings, Vault)
- **Supervisor Panel** (`supervisor/panel.blade.php`) - Student management with pending reviews
- **Admin Settings** (`admin/settings.blade.php`) - Tabbed settings (General, Storage, AI, Users)

## Design Principles

1. Use existing components before creating new ones
2. Maintain consistent spacing, colors, and typography
3. **Typography Reference**: `resources/views/admin/students/index.blade.php` is the standard for admin page typography
4. Mobile-first - all components are responsive
5. Minimal design - avoid excessive borders/shadows
6. Use Alpine's `x-transition` for smooth animations
7. Include proper labels, focus states, and ARIA attributes
8. Use modest, compact font sizes for admin interfaces (not text-2xl/text-3xl)

## Common Patterns

### Page Header
```blade
<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-base font-semibold text-primary">Page Title</h2>
        <p class="text-xs text-secondary mt-0.5">Subtitle or description</p>
    </div>
    <div class="flex items-center gap-2">
        <x-button>Primary</x-button>
        <x-button variant="secondary">Secondary</x-button>
    </div>
</div>
```

### Content Grid
```blade
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">{{-- Main --}}</div>
    <div class="space-y-6">{{-- Sidebar --}}</div>
</div>
```

### Breadcrumb
```blade
<div class="flex items-center gap-2 text-xs text-secondary mb-6">
    <a href="{{ route('dashboard') }}" class="hover:text-primary">Dashboard</a>
    <svg class="w-3 h-3">...</svg>
    <span class="text-primary">Current</span>
</div>
```

### Frontend Module Architecture

The application uses modular JavaScript with dynamic imports for task management interfaces.

#### Module Structure (`resources/js/modules/`)

| Module | Purpose | Library |
|--------|---------|---------|
| `api.js` | API client for all backend communication | Axios |
| `store.js` | Centralized state management with pub/sub pattern | Custom |
| `kanban.js` | Drag-drop Kanban board interface | SortableJS 1.15.3 |
| `gantt.js` | Timeline Gantt chart with date editing | Frappe Gantt 0.6.1 |
| `timeline.js` | Interactive timeline with zoom/pan | vis-timeline 7.7.3 |

#### Usage in Blade Views

```blade
<!-- Kanban Board -->
<div x-data="initTaskFlowKanban({ studentId: {{ $student->id }} })" x-init="init()">

<!-- Gantt Chart -->
<div x-data="ganttChartApp({{ $student->id }})">

<!-- Timeline View -->
<div x-data="initTaskFlowTimeline({ studentId: {{ $student->id }} })" x-init="init()">
```

#### API Client (`api.js`)

Provides typed methods for all task operations:
- `taskApi.getTasks(studentId)` - Fetch all tasks
- `taskApi.getGanttData(studentId)` - Fetch tasks with dates for timeline
- `taskApi.updateStatus(taskId, status)` - Update task status
- `taskApi.updateOrder(tasks)` - Bulk update sort order
- `taskApi.updateDates(taskId, start, end)` - Update task dates
- `taskApi.updateProgress(taskId, progress)` - Update progress percentage

#### Task Store (`store.js`)

State management with reactive updates:
- `taskStore.fetchTasks(studentId)` - Load and cache tasks
- `taskStore.getTasksByStatus()` - Get grouped tasks for Kanban
- `taskStore.getTasksWithDates()` - Get dated tasks for Gantt/Timeline
- `taskStore.subscribe(listener)` - Subscribe to state changes

#### Third-Party Libraries

Libraries load dynamically from CDN:
```javascript
// Loaded on demand by respective modules
https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js
https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js
https://cdn.jsdelivr.net/npm/vis-timeline@7.7.3/vis-timeline-graph2d.min.js
```

#### Build Output

```bash
npm run build
# Outputs to public/build/assets/
# - app-*.js (main entry)
# - kanban-*.js (SortableJS module)
# - gantt-*.js (Frappe Gantt module)
# - timeline-*.js (vis-timeline module)
# - api-*.js (API client)
```

### Key Controllers by Role

| Role | Controllers | Location |
|------|-------------|----------|
| Admin | DashboardController, StudentManagementController, ProgrammeController, JourneyTemplateController, SettingsController | `app/Http/Controllers/Admin/` |
| Supervisor | DashboardController, StudentViewController | `app/Http/Controllers/Supervisor/` |
| Student | DashboardController | `app/Http/Controllers/Student/` |
| Shared | TaskController, ProgressReportController, MeetingController, FileController, AiChatPageController, AiChatController, AiFeatureController | `app/Http/Controllers/` |

### Settings Architecture

Admin settings are stored in the database via `SystemSetting` model:
- `storage_disk` - Active storage driver (local, do_spaces, google_drive)
- `storage.*` - Storage provider credentials (DO Spaces keys, Google Drive OAuth)
- `ai.*` - AI service configuration (provider, API key, model)
- Settings are cached for 1 hour (TTL) and accessible via `SystemSetting::get($key)`
- Admin panel at `/admin/settings/storage` for configuration

### Storage Architecture

The application supports multiple storage backends through Laravel's filesystem abstraction. The admin storage settings page at `/admin/settings/storage` (`resources/views/admin/settings/storage.blade.php`) has been redesigned to match the compact typography standards found in `resources/views/admin/students/index.blade.php`.

**Available Storage Disks:**
- `local` - Server's local filesystem at `storage/app/private`
- `do_spaces` - DigitalOcean Spaces (S3-compatible object storage)
- `google-drive` - Google Drive API integration with OAuth 2.0

**Storage Services:**

| Service | Location | Purpose |
|---------|----------|---------|
| `StorageManager` | `app/Services/Storage/StorageManager.php` | Manages storage configs, builds category-based paths, handles cloud operations |
| `StorageTestService` | `app/Services/Storage/StorageTestService.php` | Connection testing and storage statistics |
| `GoogleDriveAdapter` | `app/Services/Storage/GoogleDriveAdapter.php` | Custom Flysystem adapter for Google Drive |
| `StorageServiceProvider` | `app/Providers/StorageServiceProvider.php` | Dynamic disk registration |

**Storage Configuration:**
- Disks registered dynamically via `StorageServiceProvider` from database settings
- Active disk selected via admin UI at `/admin/settings/storage`
- Connection testing available for cloud providers
- Storage statistics (file count, total size) displayed in admin

**Category-Based Folders:**
Files are organized by category for each student:
- `proposal` - Research proposals
- `reports` - Progress reports
- `thesis` - Thesis documents
- `simulation` - Simulation files
- `data` - Research data
- `images` - Images and figures
- `references` - Reference materials

Path format: `{programme_slug}/{student_id}/{category}`

**Storage Features:**
- Auto folder creation on student registration
- File versioning with `parent_file_id` and `is_latest` flag
- Cloud storage statistics (file count, total size, usage/limit)
- CDN endpoint support for DO Spaces
- OAuth 2.0 with refresh token for Google Drive
- Automatic folder hierarchy creation for Google Drive

**DigitalOcean Spaces Configuration:**
- Access Key ID & Secret
- Bucket/Space name
- Region selection (NYC3, SFO3, AMS3, SGP1, FRA1, BLR1, SYD1)
- Optional CDN endpoint

**Google Drive Configuration:**
- OAuth Client ID & Secret
- Refresh Token (obtained via OAuth Playground)
- Optional root folder ID for uploads
- Requires Drive API scope: `https://www.googleapis.com/auth/drive`

### File Versioning

Files support versioning through the `files` table. When uploading a new version:
1. Original file is preserved (parent_file_id references previous version)
2. New version stored with incremented `version` number
3. `is_latest` flag indicates the current version
4. `FileActivity` table tracks all file access and modifications

### Database Migrations

All migrations are organized in `database/migrations/`:
- **Baseline migrations** (`0001_01_01_*`) - Laravel default and core tables
- **Core migrations** (`2025_01_01_*)` - Initial ResearchFlow schema
- **Enhanced migrations** (`2025_03_18_*`) - New features and performance indexes

Run migrations:
```bash
php artisan migrate:fresh --seed    # Fresh database with seeding
php artisan migrate:rollback        # Rollback last batch
php artisan migrate:refresh         # Rollback and re-migrate
```

See `database/docs/MIGRATION_INDEX.md` for complete migration reference.

### SaaS Multi-Tenancy

The schema is designed for future multi-tenancy support:
- All tables can be extended with `tenant_id` column
- Composite indexes ready for tenant-scoped queries
- Subscription and billing tables included
- Webhook support for external integrations

### Audit Trail

All user actions are tracked through:
- `audit_logs` table - Complete audit trail with old/new values
- `activities` table - Unified activity timeline for dashboard feeds
- `file_activities` table - File access tracking

## AI Layer Architecture

### Overview

The AI layer provides a provider-agnostic interface for AI-powered features including chat, document analysis, task suggestions, and deadline risk detection. It supports multiple AI providers (OpenAI, Google Gemini, Anthropic) with RAG (Retrieval-Augmented Generation) capabilities.

### Service Architecture (`app/Services/Ai/`)

```
Ai/
├── AiProviderInterface.php    # Contract for all AI providers
├── BaseAiProvider.php          # Abstract base with common functionality
├── OpenAiProvider.php          # OpenAI/GPT-compatible implementation
├── GeminiProvider.php          # Google Gemini implementation
├── AnthropicProvider.php       # Claude/Anthropic implementation
├── AiServiceFactory.php        # Provider instantiation and caching
├── AiChatService.php           # Chat operations with context
├── AiRagService.php            # Document retrieval with embeddings
└── Features/
    ├── AiFeature.php           # Base feature class
    ├── ReportSummarizer.php    # Summarize progress reports
    ├── DeadlineRiskDetector.php # Analyze task deadline risks
    ├── TaskSuggester.php        # Suggest next research tasks
    └── DocumentComparator.php   # Compare document versions
```

### Provider Configuration

AI providers are stored in the `ai_providers` table with:
- `slug` - Provider identifier (openai, gemini, anthropic, custom)
- `api_key` - Encrypted API key
- `model` - Model name (e.g., gpt-4o-mini, gemini-pro, claude-3-5-sonnet)
- `base_url` - Custom API endpoint (for self-hosted/custom providers)
- `is_active` / `is_default` - Provider selection flags
- `settings` - JSON configuration (features, embedding_model, etc.)

### AI Features API

| Feature | Endpoint | Description |
|---------|----------|-------------|
| Report Summary | `POST /api/ai/reports/{id}/summarize` | Summarizes a progress report |
| Multi-Report Summary | `POST /api/ai/students/{id}/reports/summarize` | Summarizes multiple reports |
| Deadline Analysis | `GET /api/ai/students/{id}/deadline-risks` | Analyzes at-risk and overdue tasks |
| Task Suggestions | `POST /api/ai/students/{id}/suggest-tasks` | Suggests next tasks based on progress |
| Subtask Suggestions | `POST /api/ai/students/{id}/tasks/{task}/suggest-subtasks` | Breaks down task into subtasks |
| Document Comparison | `POST /api/ai/documents/compare` | Compares two document versions |
| Version Comparison | `GET /api/ai/students/{id}/files/{file}/compare-versions` | Compares all file versions |
| Changes Summary | `GET /api/ai/students/{id}/files/{file}/changes-summary` | Summarizes version changes |

### Chat API

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/ai/conversations` | GET | List user's conversations |
| `/api/ai/conversations` | POST | Create new conversation |
| `/api/ai/conversations/{id}/messages` | GET | Get conversation messages |
| `/api/ai/conversations/{id}/messages` | POST | Send message (supports `use_rag` flag) |
| `/api/ai/conversations/{id}` | PATCH | Update conversation (title, scope, context_files) |
| `/api/ai/conversations/{id}` | DELETE | Delete conversation |

### RAG (Retrieval-Augmented Generation)

The `AiRagService` provides document-aware chat:

1. **File Indexing** - Documents are chunked and embedded
   - PDF text extraction via `smalot/pdfparser`
   - Text file support (plain text, markdown)
   - Chunk size: 500 tokens with 50-token overlap

2. **Vector Storage** - `ai_embeddings` table stores:
   - `file_id` - Source file reference
   - `chunk_index` - Position in document
   - `content` - Text chunk
   - `vector` - Embedding as JSON array
   - `metadata` - Character positions, token estimates

3. **Similarity Search** - Cosine similarity for relevant content retrieval

4. **Context Injection** - Retrieved chunks prepended to user queries

### System Prompts by Scope

| Scope | Purpose |
|-------|---------|
| `general` | Default research assistance |
| `student` | Student-specific guidance with progress context |
| `planning` | Research planning and methodology advice |
| `proposal` | Thesis proposal structure and content |
| `analysis` | Data analysis and statistical guidance |
| `writing` | Academic writing style and structure |

### Usage Example

```php
use App\Services\Ai\AiServiceFactory;
use App\Services\Ai\Features\DeadlineRiskDetector;

// Get configured provider
$provider = AiServiceFactory::getProvider();

// Chat with context
$chatService = new \App\Services\Ai\AiChatService($provider);
$response = $chatService->chat($conversation, "Help me plan my next milestone");

// Analyze deadline risks
$detector = new DeadlineRiskDetector($provider);
$analysis = $detector->execute($student);
// Returns: analysis, at_risk_tasks, overdue_tasks, summary

// Summarize report
$summarizer = new \App\Services\Ai\Features\ReportSummarizer($provider);
$summary = $summarizer->execute($report);
```

### Adding a New AI Feature

1. Create feature class extending `AiFeature`:
```php
namespace App\Services\Ai\Features;

class MyFeature extends AiFeature
{
    public function execute(...$args): string|array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->buildSystemPrompt()],
            ['role' => 'user', 'content' => $this->buildUserMessage(...$args)],
        ];
        return $this->call($messages);
    }

    protected function buildSystemPrompt(): string { /* ... */ }
    protected function buildUserMessage(...$args): string { /* ... */ }
}
```

2. Add controller method in `AiFeatureController`

3. Add route in `routes/api.php`

### Extending Provider Support

To add a new AI provider:

1. Create provider class extending `BaseAiProvider`
2. Implement required abstract methods:
   - `getChatEndpoint()` - Chat API endpoint
   - `getEmbeddingEndpoint()` - Embeddings API endpoint
   - `formatMessages()` - Convert to provider's message format
   - `extractContent()` - Parse response
   - `extractStreamChunk()` - Parse SSE chunks
   - `embed()` - Generate embeddings

3. Register in `AiServiceFactory::$providers`:
```php
protected static array $providers = [
    'openai' => OpenAiProvider::class,
    'myprovider' => MyProvider::class,
];

---

# Admin Dashboard Design System

The admin dashboard at `resources/views/admin/dashboard.blade.php` implements a premium, Claude-inspired design system. Follow these patterns when creating or modifying admin interfaces.

## Dashboard Layout Structure

```
Welcome Header (flex row with date + notifications)
├── KPI Stats Grid (4 columns, responsive)
├── Main Content Grid (3 columns: 2 + 1 split)
│   ├── Left: Student Overview (2 cols)
│   └── Right: Quick Stats, Activity, Actions (1 col)
└── Tasks Due Soon (conditional, full width)
```

## Key Design Patterns

### 1. Welcome Header
```blade
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold text-primary">Welcome back, {{ $name }}</h1>
        <p class="text-sm text-secondary mt-1">Subtitle text</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-xs text-tertiary">{{ $date }}</span>
        <button class="relative p-2.5 text-secondary hover:text-primary hover:bg-surface rounded-xl transition-all">
            <!-- Notification icon with badge if needed -->
        </button>
    </div>
</div>
```

**Note**: `text-2xl` is only appropriate for dashboard welcome headers. For standard page headers, use `text-base`.

### 2. Premium KPI Cards
- **Container**: `bg-card rounded-2xl p-6 border border-border`
- **Hover**: `hover:border-accent/30 hover:shadow-soft transition-all duration-300`
- **Icon**: `w-12 h-12 rounded-2xl bg-gradient-to-br from-accent/20 to-accent/10`
- **Icon hover**: `group-hover:scale-110 transition-transform duration-300`
- **Value**: `text-lg font-bold text-primary`
- **Label**: `text-xs text-secondary`

### 3. Card with Header Section
```blade
<div class="bg-card rounded-2xl border border-border overflow-hidden">
    <!-- Header -->
    <div class="px-8 py-5 border-b border-border flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-primary">Title</h3>
            <p class="text-xs text-secondary mt-0.5">Subtitle</p>
        </div>
        <a href="#" class="text-xs text-accent hover:text-amber-700 font-medium inline-flex items-center gap-1">
            View all
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
        </a>
    </div>
    <!-- Content -->
    <div class="p-8">
        <!-- Card content -->
    </div>
</div>
```

### 4. List Items with Avatars (Student/Task Rows)
```blade
<div class="divide-y divide-border">
    <a href="#" class="flex items-center gap-4 p-5 hover:bg-surface transition-colors group">
        <!-- Avatar with status indicator -->
        <div class="relative">
            <x-avatar :name="$name" size="md" />
            <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full border-2 border-card
                         {{ $active ? 'bg-success' : 'bg-tertiary' }}"></span>
        </div>
        <!-- Content -->
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-primary group-hover:text-accent transition-colors truncate">
                {{ $title }}
            </p>
            <p class="text-xs text-secondary">{{ $subtitle }}</p>
        </div>
        <!-- Right side -->
        <div class="flex items-center gap-4">
            <!-- Progress bar or stats -->
            <svg class="w-5 h-5 text-tertiary group-hover:text-accent transition-colors">
                <!-- Arrow icon -->
            </svg>
        </div>
    </a>
</div>
```

### 5. Activity Feed Items
```blade
<div class="space-y-4">
    <div class="flex items-start gap-3 group">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-accent/20 to-accent/10
                    flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="..."/>
            </svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-primary truncate">{{ $title }}</p>
            <p class="text-xs text-secondary">{{ $subtitle }}</p>
            <p class="text-[10px] text-tertiary mt-1">{{ $time }}</p>
        </div>
    </div>
</div>
```

### 6. Quick Action Buttons (with Descriptions)
```blade
<a href="#" class="flex items-center gap-3 p-3 rounded-xl text-secondary
             hover:bg-surface hover:text-primary transition-all group">
    <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center
                group-hover:bg-accent/20 transition-colors">
        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="..."/>
        </svg>
    </div>
    <div class="flex-1">
        <p class="text-sm font-medium">Action Title</p>
        <p class="text-xs text-tertiary">Brief description</p>
    </div>
    <svg class="w-5 h-5 text-tertiary group-hover:text-primary transition-colors"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
</a>
```

### 7. Premium Empty States
```blade
<div class="flex flex-col items-center justify-center text-center py-12 px-6">
    <!-- Large gradient icon -->
    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-accent/15 to-accent/5
                flex items-center justify-center mb-6">
        <svg class="w-10 h-10 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="..."/>
        </svg>
    </div>
    <h3 class="text-xl font-semibold text-primary mb-2">Title</h3>
    <p class="text-secondary max-w-sm mb-8">Description</p>
    <!-- Actions -->
    <div class="flex items-center gap-3">
        <!-- Primary action button -->
        <!-- Secondary action button -->
    </div>
</div>
```

### 8. Status/Alert Pills
```blade
<!-- Warning pill with icon -->
<div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-warning/10 text-warning text-sm font-medium">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="..."/>
    </svg>
    <span>{{ $count }} items</span>
</div>

<!-- Pulsing indicator for active states -->
<div class="w-3 h-3 rounded-full bg-warning animate-pulse"></div>
```

## Typography Hierarchy

**Reference Standard**: Use `resources/views/admin/students/index.blade.php` as the typography reference for all admin pages.

| Element | Classes | Usage |
|---------|---------|------|
| Page Header | `text-base font-semibold text-primary` | Main page headings |
| Page Description | `text-xs text-secondary mt-0.5` | Subtitles below page headers |
| Section Header | `text-base font-semibold text-primary` | Section titles |
| Card Title | `text-base font-semibold text-primary` | Card headers |
| Form Labels | `text-xs font-medium text-secondary` | Input field labels |
| Form Inputs | `text-sm text-primary` | Input text content |
| Body Text | `text-sm text-secondary` | General content, descriptions |
| Labels/Metadata | `text-xs text-secondary` | Secondary info, hints |
| KPI Values | `text-lg font-bold text-primary` | Stat card values |
| KPI Labels | `text-xs text-secondary` | Stat card descriptions |
| Table Headers | `text-xs font-medium text-secondary uppercase tracking-wider` | Column headers |
| Table Data | `text-sm text-primary` | Table cell content |
| Button Text | `text-sm font-medium` | Button labels |

**Important**: Admin pages should use modest, compact font sizes similar to the students index page. Avoid large headings (text-2xl, text-3xl) except in rare cases like dashboard welcome headers.

## Spacing Standards

| Context | Spacing | Example |
|---------|---------|---------|
| Page sections | `mb-4`, `mb-6` | Section spacing (compact) |
| Card padding | `p-6` | Standard card interior |
| Card header | `px-8 py-5` | Card header section |
| Form card padding | `p-8` | Configuration forms |
| List items | `p-5` | Clickable rows |
| Grid gaps | `gap-4` (tight), `gap-5` (comfortable) | Between cards |
| Content gaps | `gap-3` | Between related items |
| Text spacing | `mt-0.5`, `mt-1`, `mt-1.5` | Between text elements |
| Label spacing | `mb-1.5` | Below form labels |
| Button padding | `px-5 py-3` | Standard buttons |

**Reference**: Follow `resources/views/admin/students/index.blade.php` for spacing patterns.

## Border & Radius Standards

| Element | Border | Radius |
|---------|--------|--------|
| Cards | `border border-border` | `rounded-2xl` |
| Buttons | `border border-border` | `rounded-xl` |
| Icons | N/A | `rounded-2xl` (large), `rounded-xl` (medium), `rounded-lg` (small) |
| Pills/Badges | N/A | `rounded-full` |
| Inputs | `border border-border` | `rounded-lg` |

## Animation Guidelines

1. **Hover Scale**: Use `group-hover:scale-110 transition-transform duration-300` for icons
2. **Color Transitions**: Use `transition-colors duration-300` for text/icons
3. **All Transitions**: Use `transition-all duration-300` for multi-property changes
4. **Background Hover**: Use `hover:bg-surface` for interactive elements
5. **Border Hover**: Use `hover:border-accent/30` for card borders
6. **Pulse**: Use `animate-pulse` for notification dots

## Responsive Patterns

```blade
<!-- 4-column KPI (responsive) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

<!-- Main grid with 2:1 split -->
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <!-- Main content (2 columns) -->
    </div>
    <div>
        <!-- Sidebar (1 column) -->
    </div>
</div>

<!-- Header responsive -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
```

## Color Usage Guidelines

1. **Primary Actions**: Use `bg-accent text-white` with `hover:bg-amber-700`
2. **Secondary Actions**: Use `text-secondary hover:text-primary border border-border hover:bg-surface`
3. **Icon Backgrounds**: Use `bg-{color}/10` or `bg-gradient-to-br from-{color}/20 to-{color}/10`
4. **Status Indicators**: Use `bg-{color}` with appropriate color
5. **Subtle Backgrounds**: Use `bg-surface` for alternating content
6. **Text Hierarchy**: `primary` → `secondary` → `tertiary`

## Common Icon Patterns

```blade
<!-- Arrow right (navigation) -->
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
</svg>

<!-- Arrow up (trend) -->
<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
</svg>

<!-- Notification bell -->
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
</svg>
```

## File Reference

- **Admin Dashboard**: `resources/views/admin/dashboard.blade.php`
- **Admin Controller**: `app/Http/Controllers/Admin/DashboardController.php`
- **Layout Component**: `resources/views/layouts/app.blade.php`
```
