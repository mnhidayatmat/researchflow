<?php

namespace Database\Seeders;

use App\Models\AiProvider;
use App\Models\Meeting;
use App\Models\Milestone;
use App\Models\Programme;
use App\Models\ProgressReport;
use App\Models\Stage;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\User;
use App\Services\FileService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@researchflow.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'staff_id' => 'ADM001',
            'status' => 'active',
            'department' => 'Academic Affairs',
        ]);

        // ── Supervisors ──
        $sv1 = User::create([
            'name' => 'Dr. Sarah Chen',
            'email' => 'sarah@researchflow.test',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'staff_id' => 'SV001',
            'status' => 'active',
            'department' => 'Computer Science',
            'faculty' => 'Faculty of Computing',
        ]);

        $sv2 = User::create([
            'name' => 'Prof. Ahmad Razak',
            'email' => 'ahmad@researchflow.test',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'staff_id' => 'SV002',
            'status' => 'active',
            'department' => 'Software Engineering',
            'faculty' => 'Faculty of Computing',
        ]);

        $cosv = User::create([
            'name' => 'Dr. Lim Wei',
            'email' => 'lim@researchflow.test',
            'password' => Hash::make('password'),
            'role' => 'cosupervisor',
            'staff_id' => 'CSV001',
            'status' => 'active',
            'department' => 'Data Science',
            'faculty' => 'Faculty of Computing',
        ]);

        // ── Programmes ──
        $fyp = Programme::create(['name' => 'Final Year Project', 'code' => 'FYP', 'slug' => 'fyp', 'duration_months' => 8, 'sort_order' => 1]);
        $msc = Programme::create(['name' => 'Master of Science', 'code' => 'MSC', 'slug' => 'msc', 'duration_months' => 24, 'sort_order' => 2]);
        $phd = Programme::create(['name' => 'Doctor of Philosophy', 'code' => 'PHD', 'slug' => 'phd', 'duration_months' => 48, 'sort_order' => 3]);

        // ── Students ──
        $st1User = User::create([
            'name' => 'Ali bin Hassan',
            'email' => 'ali@researchflow.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'matric_number' => 'MSC2024001',
            'status' => 'active',
        ]);

        $student1 = Student::create([
            'user_id' => $st1User->id,
            'programme_id' => $msc->id,
            'supervisor_id' => $sv1->id,
            'cosupervisor_id' => $cosv->id,
            'research_title' => 'Machine Learning Approach for Early Detection of Network Intrusion',
            'intake' => '2024/2025-1',
            'start_date' => now()->subMonths(6),
            'expected_completion' => now()->addMonths(18),
            'status' => 'active',
            'overall_progress' => 25,
        ]);

        $st2User = User::create([
            'name' => 'Nurul Aisyah',
            'email' => 'nurul@researchflow.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'matric_number' => 'FYP2025001',
            'status' => 'active',
        ]);

        $student2 = Student::create([
            'user_id' => $st2User->id,
            'programme_id' => $fyp->id,
            'supervisor_id' => $sv2->id,
            'cosupervisor_id' => $cosv->id,
            'research_title' => 'Smart Campus Navigation System Using AR Technology',
            'research_abstract' => 'Developing an augmented reality-based mobile application to help students and visitors navigate the university campus with real-time location tracking and indoor positioning.',
            'intake' => '2024/2025-2',
            'start_date' => now()->subMonths(2),
            'expected_completion' => now()->addMonths(6),
            'status' => 'active',
            'overall_progress' => 35,
        ]);

        // Pending student
        $st3User = User::create([
            'name' => 'John Smith',
            'email' => 'john@researchflow.test',
            'password' => Hash::make('password'),
            'role' => 'student',
            'matric_number' => 'PHD2025001',
            'status' => 'pending',
        ]);

        Student::create([
            'user_id' => $st3User->id,
            'programme_id' => $phd->id,
            'status' => 'pending',
        ]);

        // ── Create dummy milestones for tasks ──
        $m1 = Milestone::create(['name' => 'Proposal Approved', 'sort_order' => 0, 'status' => 'completed', 'progress' => 100, 'due_date' => now()->subMonths(4)]);
        $m2 = Milestone::create(['name' => 'Literature Review', 'sort_order' => 1, 'status' => 'in_progress', 'progress' => 60, 'due_date' => now()->addWeeks(2)]);

        // ── Tasks for student 1 ──
        Task::create(['student_id' => $student1->id, 'milestone_id' => $m1->id, 'assigned_by' => $sv1->id, 'title' => 'Write research proposal', 'status' => 'completed', 'priority' => 'high', 'progress' => 100, 'start_date' => now()->subMonths(5), 'due_date' => now()->subMonths(4), 'completed_at' => now()->subMonths(4), 'sort_order' => 0]);
        Task::create(['student_id' => $student1->id, 'milestone_id' => $m1->id, 'assigned_by' => $sv1->id, 'title' => 'Prepare proposal presentation', 'status' => 'completed', 'priority' => 'high', 'progress' => 100, 'start_date' => now()->subMonths(4)->subWeeks(1), 'due_date' => now()->subMonths(4), 'completed_at' => now()->subMonths(4), 'sort_order' => 1]);
        Task::create(['student_id' => $student1->id, 'milestone_id' => $m2->id, 'assigned_by' => $sv1->id, 'title' => 'Review 30 relevant papers', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 70, 'start_date' => now()->subMonths(3), 'due_date' => now()->addWeeks(1), 'sort_order' => 2]);
        Task::create(['student_id' => $student1->id, 'milestone_id' => $m2->id, 'assigned_by' => $sv1->id, 'title' => 'Draft Chapter 2 - Literature Review', 'status' => 'in_progress', 'priority' => 'medium', 'progress' => 40, 'start_date' => now()->subWeeks(3), 'due_date' => now()->addWeeks(2), 'sort_order' => 3]);
        Task::create(['student_id' => $student1->id, 'assigned_by' => $sv1->id, 'title' => 'Set up experiment environment', 'status' => 'planned', 'priority' => 'medium', 'start_date' => now()->addWeeks(2), 'due_date' => now()->addMonths(1), 'sort_order' => 4]);
        Task::create(['student_id' => $student1->id, 'assigned_by' => $sv1->id, 'title' => 'Collect and preprocess dataset', 'status' => 'backlog', 'priority' => 'medium', 'start_date' => now()->addMonths(1), 'due_date' => now()->addMonths(2), 'sort_order' => 5]);
        Task::create(['student_id' => $student1->id, 'milestone_id' => $m2->id, 'assigned_by' => $sv1->id, 'title' => 'Submit literature review for review', 'status' => 'waiting_review', 'priority' => 'high', 'progress' => 90, 'start_date' => now()->subWeeks(1), 'due_date' => now(), 'sort_order' => 6]);

        // ── Milestones for student 2 (Nurul Aisyah) ──
        $m3 = Milestone::create(['name' => 'Requirements Gathering', 'sort_order' => 0, 'status' => 'completed', 'progress' => 100, 'due_date' => now()->subMonths(1)]);
        $m4 = Milestone::create(['name' => 'AR Prototype Development', 'sort_order' => 1, 'status' => 'in_progress', 'progress' => 45, 'due_date' => now()->addMonths(2)]);
        $m5 = Milestone::create(['name' => 'User Testing', 'sort_order' => 2, 'status' => 'planned', 'progress' => 0, 'due_date' => now()->addMonths(4)]);

        // ── Tasks for student 2 (Nurul Aisyah) ──
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m3->id, 'assigned_by' => $sv2->id, 'title' => 'Conduct user requirements survey', 'status' => 'completed', 'priority' => 'high', 'progress' => 100, 'start_date' => now()->subMonths(2), 'due_date' => now()->subMonths(1)->addWeeks(1), 'completed_at' => now()->subMonths(1)->addWeeks(1), 'sort_order' => 0]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m3->id, 'assigned_by' => $sv2->id, 'title' => 'Analyze competitor applications', 'status' => 'completed', 'priority' => 'medium', 'progress' => 100, 'start_date' => now()->subMonths(2), 'due_date' => now()->subMonths(1), 'completed_at' => now()->subMonths(1), 'sort_order' => 1]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m3->id, 'assigned_by' => $sv2->id, 'title' => 'Define system requirements', 'status' => 'completed', 'priority' => 'high', 'progress' => 100, 'start_date' => now()->subMonths(1)->addWeeks(2), 'due_date' => now()->subMonths(1)->addWeeks(3), 'completed_at' => now()->subMonths(1)->addWeeks(3), 'sort_order' => 2]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m4->id, 'assigned_by' => $sv2->id, 'title' => 'Set up Unity development environment', 'status' => 'completed', 'priority' => 'high', 'progress' => 100, 'start_date' => now()->subMonths(1), 'due_date' => now()->subWeeks(3), 'completed_at' => now()->subWeeks(3), 'sort_order' => 3]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m4->id, 'assigned_by' => $sv2->id, 'title' => 'Create basic AR scene with markers', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 70, 'start_date' => now()->subWeeks(2), 'due_date' => now()->addWeeks(1), 'sort_order' => 4]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m4->id, 'assigned_by' => $sv2->id, 'title' => 'Implement indoor positioning module', 'status' => 'in_progress', 'priority' => 'high', 'progress' => 30, 'start_date' => now()->subWeeks(1), 'due_date' => now()->addWeeks(2), 'sort_order' => 5]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m4->id, 'assigned_by' => $sv2->id, 'title' => 'Design UI/UX for navigation', 'status' => 'planned', 'priority' => 'medium', 'start_date' => now()->addWeeks(1), 'due_date' => now()->addWeeks(3), 'sort_order' => 6]);
        Task::create(['student_id' => $student2->id, 'assigned_by' => $sv2->id, 'title' => 'Research AR frameworks comparison', 'status' => 'waiting_review', 'priority' => 'medium', 'progress' => 100, 'start_date' => now()->subWeeks(3), 'due_date' => now()->subWeeks(1), 'sort_order' => 7]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m5->id, 'assigned_by' => $sv2->id, 'title' => 'Recall test participants', 'status' => 'backlog', 'priority' => 'low', 'start_date' => now()->addMonths(2), 'due_date' => now()->addMonths(3), 'sort_order' => 8]);
        Task::create(['student_id' => $student2->id, 'milestone_id' => $m5->id, 'assigned_by' => $sv2->id, 'title' => 'Prepare test scenarios', 'status' => 'backlog', 'priority' => 'medium', 'start_date' => now()->addMonths(3), 'due_date' => now()->addMonths(4), 'sort_order' => 9]);

        // ── Progress Reports for student 2 (Nurul Aisyah) ──
        ProgressReport::create([
            'student_id' => $student1->id,
            'title' => 'Month 5 Progress Report',
            'content' => "Completed review of 20 papers on network intrusion detection.\nStarted drafting Chapter 2.\nIdentified 3 key ML approaches for comparison.",
            'achievements' => 'Completed 20/30 paper reviews. Identified key research gap.',
            'challenges' => 'Access to some IEEE papers required inter-library loan.',
            'next_steps' => 'Complete remaining 10 paper reviews. Finish Chapter 2 draft.',
            'type' => 'progress_report',
            'status' => 'submitted',
            'period_start' => now()->subMonths(1),
            'period_end' => now(),
            'submitted_at' => now(),
        ]);

        ProgressReport::create([
            'student_id' => $student1->id,
            'reviewed_by' => $sv1->id,
            'title' => 'Month 4 Progress Report',
            'content' => "Proposal approved. Started literature review phase.\nSet up Mendeley library with 15 initial papers.",
            'achievements' => 'Research proposal approved by committee.',
            'type' => 'progress_report',
            'status' => 'accepted',
            'supervisor_feedback' => 'Good progress. Focus on recent publications (2020+) for your literature review.',
            'period_start' => now()->subMonths(2),
            'period_end' => now()->subMonths(1),
            'submitted_at' => now()->subMonths(1),
            'reviewed_at' => now()->subMonths(1)->addDays(2),
        ]);

        // Progress Reports for student 2 (Nurul Aisyah)
        ProgressReport::create([
            'student_id' => $student2->id,
            'title' => 'Month 2 Progress Report - AR Development',
            'content' => "Successfully set up Unity development environment with Vuforia AR SDK.\nCreated basic AR scene with image recognition markers.\nStarted implementing indoor positioning using Wi-Fi triangulation approach.",
            'achievements' => 'Completed environment setup. Created first working AR prototype with marker detection.',
            'challenges' => 'Indoor positioning accuracy is lower than expected (avg 5m error). Need to explore Bluetooth beacon alternatives.',
            'next_steps' => 'Improve positioning accuracy. Complete UI design for navigation interface.',
            'type' => 'progress_report',
            'status' => 'submitted',
            'period_start' => now()->subMonth(),
            'period_end' => now(),
            'submitted_at' => now()->subHours(3),
        ]);

        ProgressReport::create([
            'student_id' => $student2->id,
            'reviewed_by' => $sv2->id,
            'title' => 'Month 1 Progress Report - Requirements',
            'content' => "Completed user survey with 50 respondents.\nAnalyzed 5 competitor AR navigation apps.\nFinalized system requirements document.",
            'achievements' => 'Requirements gathering completed. All stakeholders approved the requirements.',
            'challenges' => 'Some users were unfamiliar with AR technology - had to explain concepts during survey.',
            'next_steps' => 'Begin AR prototype development using Unity and Vuforia SDK.',
            'type' => 'progress_report',
            'status' => 'accepted',
            'supervisor_feedback' => 'Excellent requirements analysis. Consider adding accessibility features for visually impaired users in your design.',
            'period_start' => now()->subMonths(2),
            'period_end' => now()->subMonth(),
            'submitted_at' => now()->subMonth()->addDays(2),
            'reviewed_at' => now()->subMonth()->addDays(4),
        ]);

        // ── Meetings ──
        $meeting1 = Meeting::create([
            'student_id' => $student1->id,
            'created_by' => $sv1->id,
            'title' => 'Weekly Supervision #12',
            'agenda' => "1. Review literature review progress\n2. Discuss methodology options\n3. Set next month targets",
            'type' => 'supervision',
            'mode' => 'online',
            'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            'scheduled_at' => now()->addDays(2)->setHour(10),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);
        $meeting1->attendees()->attach([$st1User->id, $sv1->id, $cosv->id]);

        Meeting::create([
            'student_id' => $student1->id,
            'created_by' => $sv1->id,
            'title' => 'Weekly Supervision #11',
            'agenda' => "Review paper summaries",
            'notes' => "Discussed 5 key papers. Student needs to focus on comparison of ML vs DL approaches for IDS.",
            'type' => 'supervision',
            'mode' => 'in_person',
            'location' => 'Room 3.12, Faculty of Computing',
            'scheduled_at' => now()->subWeeks(1),
            'duration_minutes' => 45,
            'status' => 'completed',
        ]);

        // Meetings for student 2 (Nurul Aisyah)
        $meeting2 = Meeting::create([
            'student_id' => $student2->id,
            'created_by' => $sv2->id,
            'title' => 'FYP Supervision #4 - AR Prototype Demo',
            'agenda' => "1. Demo current AR prototype\n2. Discuss positioning accuracy issues\n3. Plan UI development phase",
            'type' => 'supervision',
            'mode' => 'hybrid',
            'location' => 'Room 2.05, Faculty of Computing',
            'meeting_link' => 'https://meet.google.com/xyz-1234-abc',
            'scheduled_at' => now()->addDays(3)->setHour(14),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);
        $meeting2->attendees()->attach([$st2User->id, $sv2->id]);

        Meeting::create([
            'student_id' => $student2->id,
            'created_by' => $sv2->id,
            'title' => 'FYP Supervision #3',
            'agenda' => "Review AR framework comparison document.\nDiscuss technical approach for indoor positioning.",
            'notes' => "Approved AR framework comparison document. Suggested exploring Bluetooth beacons for better positioning accuracy. Agreed on Wi-Fi triangulation as initial approach with beacon fallback.",
            'type' => 'supervision',
            'mode' => 'online',
            'meeting_link' => 'https://meet.google.com/pqr-stuv-wxy',
            'scheduled_at' => now()->subWeeks(1),
            'duration_minutes' => 45,
            'status' => 'completed',
        ]);

        Meeting::create([
            'student_id' => $student2->id,
            'created_by' => $sv2->id,
            'title' => 'FYP Supervision #2 - Requirements Review',
            'agenda' => "Review survey results\nDiscuss competitor analysis\nApprove requirements document",
            'notes' => "Requirements document approved with minor revisions. Student to add accessibility features consideration.",
            'type' => 'supervision',
            'mode' => 'in_person',
            'location' => 'Supervisor Office - Block A, Room 204',
            'scheduled_at' => now()->subMonth(),
            'duration_minutes' => 30,
            'status' => 'completed',
        ]);

        // ── Create default folders ──
        app(FileService::class)->createDefaultFolders($student1);
        app(FileService::class)->createDefaultFolders($student2);

        // ── AI Providers ──
        AiProvider::create(['name' => 'OpenAI', 'slug' => 'openai', 'model' => 'gpt-4o-mini', 'is_active' => false]);
        AiProvider::create(['name' => 'Google Gemini', 'slug' => 'gemini', 'model' => 'gemini-2.5-flash', 'is_active' => false]);
        AiProvider::create(['name' => 'Custom (OpenAI Compatible)', 'slug' => 'custom', 'is_active' => false]);

        // ── System Settings ──
        SystemSetting::set('storage_disk', 'local', 'storage');
        SystemSetting::set('report_frequency', 'weekly', 'general');
        SystemSetting::set('app_name', 'ResearchFlow', 'general');

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Login credentials (password: password):');
        $this->command->info('  Admin:       admin@researchflow.test');
        $this->command->info('  Supervisors: sarah@researchflow.test, ahmad@researchflow.test');
        $this->command->info('  Students:    ali@researchflow.test (MSC), nurul@researchflow.test (FYP)');
    }
}
