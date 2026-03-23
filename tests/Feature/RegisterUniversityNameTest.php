<?php

namespace Tests\Feature;

use App\Models\Programme;
use App\Models\User;
use App\Services\BrevoTransactionalEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class RegisterUniversityNameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $brevo = Mockery::mock(BrevoTransactionalEmailService::class);
        $brevo->shouldReceive('sendEmailVerification')->twice();

        $this->app->instance(BrevoTransactionalEmailService::class, $brevo);
    }

    public function test_student_registration_requires_and_persists_university_name(): void
    {
        $programme = Programme::create([
            'name' => 'Master of Science',
            'code' => 'MSC',
            'slug' => 'master-of-science',
            'is_active' => true,
        ]);
        $supervisor = User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor@example.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'university_name' => 'Universiti Test',
            'status' => 'active',
        ]);
        $cosupervisor = User::create([
            'name' => 'Co Supervisor User',
            'email' => 'cosupervisor@example.com',
            'password' => Hash::make('password123'),
            'role' => 'cosupervisor',
            'university_name' => 'Universiti Test',
            'status' => 'active',
        ]);

        $response = $this->post('/register', [
            'role' => 'student',
            'name' => 'Student User',
            'email' => 'student@example.com',
            'university_name' => 'Universiti Test',
            'matric_number' => 'MSC2026001',
            'programme_id' => $programme->id,
            'supervisor_email' => 'supervisor@example.com',
            'cosupervisor_email' => 'cosupervisor@example.com',
            'phone' => '0123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'student@example.com',
            'role' => 'student',
            'university_name' => 'Universiti Test',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('students', [
            'user_id' => User::where('email', 'student@example.com')->value('id'),
            'programme_id' => $programme->id,
            'supervisor_id' => $supervisor->id,
            'cosupervisor_id' => $cosupervisor->id,
        ]);
    }

    public function test_supervisor_registration_requires_and_persists_university_name(): void
    {
        $response = $this->post('/register', [
            'role' => 'supervisor',
            'name' => 'Lecturer User',
            'email' => 'lecturer@example.com',
            'university_name' => 'Universiti Test',
            'staff_id' => 'SV1001',
            'department' => 'Computer Science',
            'faculty' => 'Faculty of Computing',
            'phone' => '0123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'lecturer@example.com',
            'role' => 'supervisor',
            'university_name' => 'Universiti Test',
            'status' => 'active',
        ]);
    }

    public function test_registration_fails_without_university_name(): void
    {
        $programme = Programme::create([
            'name' => 'Doctor of Philosophy',
            'code' => 'PHD',
            'slug' => 'doctor-of-philosophy',
            'is_active' => true,
        ]);

        $response = $this->from('/register')->post('/register', [
            'role' => 'student',
            'name' => 'Student User',
            'email' => 'missing-university@example.com',
            'matric_number' => 'PHD2026001',
            'programme_id' => $programme->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('university_name');
    }

    public function test_student_registration_fails_when_supervisor_emails_do_not_match_existing_users(): void
    {
        $programme = Programme::create([
            'name' => 'Doctor of Philosophy',
            'code' => 'PHD',
            'slug' => 'doctor-of-philosophy',
            'is_active' => true,
        ]);

        $response = $this->from('/register')->post('/register', [
            'role' => 'student',
            'name' => 'Student User',
            'email' => 'no-match@example.com',
            'university_name' => 'Universiti Test',
            'matric_number' => 'PHD2026002',
            'programme_id' => $programme->id,
            'supervisor_email' => 'missing-supervisor@example.com',
            'cosupervisor_email' => 'missing-cosupervisor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['supervisor_email', 'cosupervisor_email']);
    }

    public function test_verified_pending_user_can_sign_in_without_admin_approval(): void
    {
        User::create([
            'name' => 'Pending Student',
            'email' => 'pending@example.com',
            'password' => Hash::make('password123'),
            'role' => 'student',
            'university_name' => 'Universiti Test',
            'status' => 'pending',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/student/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'pending@example.com',
            'status' => 'active',
        ]);
    }

    public function test_supervisor_login_ignores_student_intended_url(): void
    {
        User::create([
            'name' => 'Supervisor User',
            'email' => 'supervisor-login@example.com',
            'password' => Hash::make('password123'),
            'role' => 'supervisor',
            'staff_id' => 'SV2001',
            'department' => 'Computer Science',
            'faculty' => 'Faculty of Computing',
            'university_name' => 'Universiti Test',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->withSession([
            'url.intended' => 'http://localhost/student/dashboard',
        ])->post('/login', [
            'email' => 'supervisor-login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/supervisor/dashboard');
    }
}
