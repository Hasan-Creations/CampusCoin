<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampusCoinAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_with_sitemap(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Campus');
        $response->assertSee('Coin');
        $response->assertSee('Application Sitemap');
        $response->assertSee('/dashboard');
        $response->assertSee('/admin/login');
    }

    public function test_student_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Student Sign In');
        $response->assertSee('CAMPUS FINANCIAL INTELLIGENCE');
        $response->assertSee('Fixed-point arithmetic');
    }

    public function test_admin_direct_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('Administrator Direct Access');
        $response->assertSee('Root Key / Password');
    }

    public function test_registration_screen_renders_with_student_fields(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Academic Standing / Cohort');
        $response->assertSee('Monthly Allowance / Inflow');
        $response->assertSee('Monthly Savings Target');
        $response->assertSee('Freshman');
        $response->assertSee('Senior');
    }

    public function test_new_students_can_register_with_valid_cohort_and_financial_baselines(): void
    {
        $response = $this->post('/register', [
            'name' => 'Jordan Lee',
            'email' => 'jordan.lee@university.edu',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'academic_year' => 'Sophomore',
            'monthly_allowance' => 950.00,
            'savings_goal' => 200.00,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'email' => 'jordan.lee@university.edu',
            'role' => 'student',
            'academic_year' => 'Sophomore',
            'monthly_allowance' => 950.00,
            'savings_goal' => 200.00,
        ]);
    }

    public function test_student_can_authenticate_and_access_dashboard(): void
    {
        $student = User::factory()->create([
            'email' => 'student.test@campus.edu',
            'password' => bcrypt('SecurePassword123!'),
            'role' => 'student',
            'status' => 'active',
            'academic_year' => 'Senior',
            'monthly_allowance' => 1500.00,
        ]);

        $response = $this->post('/login', [
            'email' => 'student.test@campus.edu',
            'password' => 'SecurePassword123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($student);

        $dashboardResponse = $this->actingAs($student)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Senior Cohort');
        $dashboardResponse->assertSee('$1,500.00');
    }

    public function test_students_are_blocked_from_admin_console(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_administrators_can_authenticate_via_direct_portal_and_access_admin_console(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'superadmin@campuscoin.edu',
            'password' => bcrypt('SuperSecretAdmin123!'),
            'status' => 'active',
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'superadmin@campuscoin.edu',
            'password' => 'SuperSecretAdmin123!',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);

        $adminDashboard = $this->actingAs($admin)->get('/admin/dashboard');
        $adminDashboard->assertStatus(200);
        $adminDashboard->assertSee('System Operations');
        $adminDashboard->assertSee('Metric Telemetry', false);
    }

    public function test_deactivated_users_cannot_log_in(): void
    {
        $disabledUser = User::factory()->disabled()->create([
            'email' => 'banned@campus.edu',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'banned@campus.edu',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
