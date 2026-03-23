// Tinker script to create seed data for Nurul Aisyah
// Run: php artisan tinker
// Then paste: include 'database/seeders/tinker_nurul_aisyah.php';

// Find or create the user
$user = \App\Models\User::firstOrCreate(
    ['email' => 'nurul@researchflow.test'],
    [
        'name' => 'Nurul Aisyah',
        'password' => Hash::make('password'),
        'role' => 'student',
        'matric_number' => 'FYP2025001',
        'status' => 'active',
    ]
);

// Get programme
$fyp = \App\Models\Programme::where('code', 'FYP')->first();
if (!$fyp) {
    $fyp = \App\Models\Programme::create([
        'name' => 'Final Year Project',
        'code' => 'FYP',
        'slug' => 'fyp',
        'duration_months' => 8,
        'sort_order' => 1
    ]);
}

// Get supervisors
$sv2 = \App\Models\User::where('email', 'ahmad@researchflow.test')->first();
if (!$sv2) {
    $sv2 = \App\Models\User::create([
        'name' => 'Prof. Ahmad Razak',
        'email' => 'ahmad@researchflow.test',
        'password' => Hash::make('password'),
        'role' => 'supervisor',
        'staff_id' => 'SV002',
        'status' => 'active',
        'department' => 'Software Engineering',
        'faculty' => 'Faculty of Computing',
    ]);
}

$cosv = \App\Models\User::where('email', 'lim@researchflow.test')->first();
if (!$cosv) {
    $cosv = \App\Models\User::create([
        'name' => 'Dr. Lim Wei',
        'email' => 'lim@researchflow.test',
        'password' => Hash::make('password'),
        'role' => 'cosupervisor',
        'staff_id' => 'CSV001',
        'status' => 'active',
        'department' => 'Data Science',
        'faculty' => 'Faculty of Computing',
    ]);
}

// Create or update student record
$student = \App\Models\Student::updateOrCreate(
    ['user_id' => $user->id],
    [
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
    ]
);

// Create or find milestones (global, not per-student)
$m3 = \App\Models\Milestone::firstOrCreate(
    ['name' => 'Requirements Gathering'],
    [
        'sort_order' => 3,
        'status' => 'completed',
        'progress' => 100,
        'due_date' => now()->subMonths(1),
    ]
);

$m4 = \App\Models\Milestone::firstOrCreate(
    ['name' => 'AR Prototype Development'],
    [
        'sort_order' => 4,
        'status' => 'in_progress',
        'progress' => 45,
        'due_date' => now()->addMonths(2),
    ]
);

$m5 = \App\Models\Milestone::firstOrCreate(
    ['name' => 'User Testing'],
    [
        'sort_order' => 5,
        'status' => 'planned',
        'progress' => 0,
        'due_date' => now()->addMonths(4),
    ]
);

// Create tasks
$tasks = [
    [
        'milestone_id' => $m3->id,
        'title' => 'Conduct user requirements survey',
        'status' => 'completed',
        'priority' => 'high',
        'progress' => 100,
        'start_date' => now()->subMonths(2),
        'due_date' => now()->subMonths(1)->addWeeks(1),
        'completed_at' => now()->subMonths(1)->addWeeks(1),
        'sort_order' => 10,
    ],
    [
        'milestone_id' => $m3->id,
        'title' => 'Analyze competitor applications',
        'status' => 'completed',
        'priority' => 'medium',
        'progress' => 100,
        'start_date' => now()->subMonths(2),
        'due_date' => now()->subMonths(1),
        'completed_at' => now()->subMonths(1),
        'sort_order' => 11,
    ],
    [
        'milestone_id' => $m3->id,
        'title' => 'Define system requirements',
        'status' => 'completed',
        'priority' => 'high',
        'progress' => 100,
        'start_date' => now()->subMonths(1)->addWeeks(2),
        'due_date' => now()->subMonths(1)->addWeeks(3),
        'completed_at' => now()->subMonths(1)->addWeeks(3),
        'sort_order' => 12,
    ],
    [
        'milestone_id' => $m4->id,
        'title' => 'Set up Unity development environment',
        'status' => 'completed',
        'priority' => 'high',
        'progress' => 100,
        'start_date' => now()->subMonths(1),
        'due_date' => now()->subWeeks(3),
        'completed_at' => now()->subWeeks(3),
        'sort_order' => 13,
    ],
    [
        'milestone_id' => $m4->id,
        'title' => 'Create basic AR scene with markers',
        'status' => 'in_progress',
        'priority' => 'high',
        'progress' => 70,
        'start_date' => now()->subWeeks(2),
        'due_date' => now()->addWeeks(1),
        'sort_order' => 14,
    ],
    [
        'milestone_id' => $m4->id,
        'title' => 'Implement indoor positioning module',
        'status' => 'in_progress',
        'priority' => 'high',
        'progress' => 30,
        'start_date' => now()->subWeeks(1),
        'due_date' => now()->addWeeks(2),
        'sort_order' => 15,
    ],
    [
        'milestone_id' => $m4->id,
        'title' => 'Design UI/UX for navigation',
        'status' => 'planned',
        'priority' => 'medium',
        'start_date' => now()->addWeeks(1),
        'due_date' => now()->addWeeks(3),
        'sort_order' => 16,
    ],
    [
        'milestone_id' => null,
        'title' => 'Research AR frameworks comparison',
        'status' => 'waiting_review',
        'priority' => 'medium',
        'progress' => 100,
        'start_date' => now()->subWeeks(3),
        'due_date' => now()->subWeeks(1),
        'sort_order' => 17,
    ],
    [
        'milestone_id' => $m5->id,
        'title' => 'Recall test participants',
        'status' => 'backlog',
        'priority' => 'low',
        'start_date' => now()->addMonths(2),
        'due_date' => now()->addMonths(3),
        'sort_order' => 18,
    ],
    [
        'milestone_id' => $m5->id,
        'title' => 'Prepare test scenarios',
        'status' => 'backlog',
        'priority' => 'medium',
        'start_date' => now()->addMonths(3),
        'due_date' => now()->addMonths(4),
        'sort_order' => 19,
    ],
];

foreach ($tasks as $task) {
    \App\Models\Task::firstOrCreate(
        [
            'student_id' => $student->id,
            'title' => $task['title'],
        ],
        array_merge($task, [
            'student_id' => $student->id,
            'assigned_by' => $sv2->id,
        ])
    );
}

// Create progress reports
\App\Models\ProgressReport::firstOrCreate(
    [
        'student_id' => $student->id,
        'title' => 'Month 2 Progress Report - AR Development',
    ],
    [
        'content' => "Successfully set up Unity development environment with Vuforia AR SDK.\nCreated basic AR scene with image recognition markers.\nStarted implementing indoor positioning using Wi-Fi triangulation approach.",
        'achievements' => 'Completed environment setup. Created first working AR prototype with marker detection.',
        'challenges' => 'Indoor positioning accuracy is lower than expected (avg 5m error). Need to explore Bluetooth beacon alternatives.',
        'next_steps' => 'Improve positioning accuracy. Complete UI design for navigation interface.',
        'type' => 'progress_report',
        'status' => 'submitted',
        'period_start' => now()->subMonth(),
        'period_end' => now(),
        'submitted_at' => now()->subHours(3),
    ]
);

\App\Models\ProgressReport::firstOrCreate(
    [
        'student_id' => $student->id,
        'title' => 'Month 1 Progress Report - Requirements',
    ],
    [
        'reviewed_by' => $sv2->id,
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
    ]
);

// Create meetings
$meeting2 = \App\Models\Meeting::firstOrCreate(
    [
        'student_id' => $student->id,
        'title' => 'FYP Supervision #4 - AR Prototype Demo',
        'scheduled_at' => now()->addDays(3)->setHour(14),
    ],
    [
        'created_by' => $sv2->id,
        'agenda' => "1. Demo current AR prototype\n2. Discuss positioning accuracy issues\n3. Plan UI development phase",
        'type' => 'supervision',
        'mode' => 'hybrid',
        'location' => 'Room 2.05, Faculty of Computing',
        'meeting_link' => 'https://meet.google.com/xyz-1234-abc',
        'duration_minutes' => 60,
        'status' => 'scheduled',
    ]
);
if ($meeting2->wasRecentlyCreated) {
    $meeting2->attendees()->attach([$user->id, $sv2->id]);
}

\App\Models\Meeting::firstOrCreate(
    [
        'student_id' => $student->id,
        'title' => 'FYP Supervision #3',
        'scheduled_at' => now()->subWeeks(1),
    ],
    [
        'created_by' => $sv2->id,
        'agenda' => "Review AR framework comparison document.\nDiscuss technical approach for indoor positioning.",
        'notes' => "Approved AR framework comparison document. Suggested exploring Bluetooth beacons for better positioning accuracy. Agreed on Wi-Fi triangulation as initial approach with beacon fallback.",
        'type' => 'supervision',
        'mode' => 'online',
        'meeting_link' => 'https://meet.google.com/pqr-stuv-wxy',
        'duration_minutes' => 45,
        'status' => 'completed',
    ]
);

\App\Models\Meeting::firstOrCreate(
    [
        'student_id' => $student->id,
        'title' => 'FYP Supervision #2 - Requirements Review',
        'scheduled_at' => now()->subMonth(),
    ],
    [
        'created_by' => $sv2->id,
        'agenda' => "Review survey results\nDiscuss competitor analysis\nApprove requirements document",
        'notes' => "Requirements document approved with minor revisions. Student to add accessibility features consideration.",
        'type' => 'supervision',
        'mode' => 'in_person',
        'location' => 'Supervisor Office - Block A, Room 204',
        'duration_minutes' => 30,
        'status' => 'completed',
    ]
);

// Create default folders
if (\App\Models\Folder::where('student_id', $student->id)->count() === 0) {
    app(\App\Services\StorageService::class)->createDefaultFolders($student);
}

echo "Nurul Aisyah seed data created successfully!\n";
echo "Login: nurul@researchflow.test / password\n";
echo "Student ID: {$student->id}\n";
echo "Tasks: " . \App\Models\Task::where('student_id', $student->id)->count() . "\n";
echo "Reports: " . \App\Models\ProgressReport::where('student_id', $student->id)->count() . "\n";
echo "Meetings: " . \App\Models\Meeting::where('student_id', $student->id)->count() . "\n";
