<?php

namespace Tests\Feature;

use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\UserManager;
use App\Livewire\Student\BudgetManager;
use App\Livewire\Student\TransactionList;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AdminMetricsService;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AdminManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    // -------------------------------------------------------------
    // 1. ADMIN AUTHORIZATION & ROUTE ISOLATION
    // -------------------------------------------------------------

    public function test_unauthenticated_guests_are_redirected_to_login_from_admin_routes(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/admin/categories')->assertRedirect('/login');
        $this->get('/admin/users')->assertRedirect('/login');
    }

    public function test_students_receive_forbidden_status_on_admin_routes(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
        ]);

        $this->actingAs($student)->get('/admin/dashboard')->assertStatus(403);
        $this->actingAs($student)->get('/admin/categories')->assertStatus(403);
        $this->actingAs($student)->get('/admin/users')->assertStatus(403);
    }

    public function test_students_cannot_execute_admin_livewire_mutations(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
        ]);

        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->assertStatus(403);
    }

    public function test_administrators_can_access_all_admin_screens(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/categories')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/users')->assertStatus(200);
    }

    // -------------------------------------------------------------
    // 2. CATEGORY ADMINISTRATION
    // -------------------------------------------------------------

    public function test_admin_can_view_global_and_personal_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();

        Category::factory()->create([
            'user_id' => $student->id,
            'name' => 'Custom Skateboarding',
            'type' => 'expense',
            'is_default' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->assertSee('Food')
            ->assertSee('Allowance')
            ->assertSee('Global Defaults')
            ->set('filterScope', 'personal')
            ->assertSee('Custom Skateboarding');
    }

    public function test_admin_can_create_global_default_category(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->set('name', 'Campus Printing')
            ->set('type', 'expense')
            ->set('icon', 'tag')
            ->set('color', '#6366F1')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'name' => 'Campus Printing',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#6366F1',
            'is_default' => true,
            'is_active' => true,
            'user_id' => null,
        ]);
    }

    public function test_admin_cannot_create_duplicate_global_category_of_same_type(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->set('name', 'Food') // Already exists as default expense
            ->set('type', 'expense')
            ->set('icon', 'pie-chart')
            ->set('color', '#E11D48')
            ->call('saveCategory')
            ->assertHasErrors(['name']);
    }

    public function test_admin_can_edit_global_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::where('name', 'Food')->first();

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->call('openEditModal', $category->id)
            ->set('name', 'Food & Dining')
            ->set('color', '#059669')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Food & Dining',
            'color' => '#059669',
        ]);
    }

    public function test_admin_can_toggle_category_activation_status(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::where('name', 'Food')->first();

        $this->assertTrue($category->is_active);

        // Deactivate
        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->call('toggleCategoryStatus', $category->id);

        $category->refresh();
        $this->assertFalse($category->is_active);

        // Reactivate
        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->call('toggleCategoryStatus', $category->id);

        $category->refresh();
        $this->assertTrue($category->is_active);
    }

    public function test_deactivated_category_is_hidden_from_student_entry_dropdowns(): void
    {
        $student = User::factory()->create();
        $category = Category::where('name', 'Food')->first();
        $category->update(['is_active' => false]);

        // In TransactionList, deactivated category should not be in formCategories
        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->assertViewHas('formCategories', function ($cats) {
                return ! $cats->contains('name', 'Food');
            });

        // In BudgetManager, deactivated category should not be in eligibleCategories
        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->assertViewHas('eligibleCategories', function ($cats) {
                return ! $cats->contains('name', 'Food');
            });
    }

    public function test_historical_transactions_referencing_deactivated_category_remain_interpretable(): void
    {
        $student = User::factory()->create();
        $category = Category::where('name', 'Food')->first();

        $transaction = Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'amount' => 35.50,
            'merchant' => 'Campus Diner',
        ]);

        // Deactivate category
        $category->update(['is_active' => false]);

        // Verify transaction still references category and renders cleanly
        $this->assertEquals('Food', $transaction->fresh()->category->name);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->assertSee('Campus Diner')
            ->assertSee('Food');
    }

    public function test_safe_deletion_blocks_deleting_category_with_referencing_records(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create();
        $category = Category::where('name', 'Food')->first();

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'amount' => 20.00,
        ]);

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->call('deleteCategory', $category->id)
            ->assertSee('Cannot hard delete')
            ->assertSee('transaction(s)');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_safe_deletion_permits_deleting_unreferenced_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create([
            'user_id' => null,
            'name' => 'Temporary Test Category',
            'is_default' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->call('deleteCategory', $category->id)
            ->assertSee('safely and permanently deleted');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    // -------------------------------------------------------------
    // 3. STUDENT ACCOUNT STATUS GOVERNANCE & SESSIONS
    // -------------------------------------------------------------

    public function test_admin_can_view_student_accounts_and_filters(): void
    {
        $admin = User::factory()->admin()->create();
        $student1 = User::factory()->create(['name' => 'Alice Walker', 'academic_year' => 'Junior']);
        $student2 = User::factory()->create(['name' => 'Bob Builder', 'academic_year' => 'Senior']);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->assertSee('Alice Walker')
            ->assertSee('Bob Builder')
            ->set('filterCohort', 'Junior')
            ->assertSee('Alice Walker')
            ->assertDontSee('Bob Builder');
    }

    public function test_admin_can_inspect_student_account_details(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create([
            'name' => 'Inspectable Student',
            'monthly_allowance' => 1200.00,
            'savings_goal' => 300.00,
        ]);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('inspectUser', $student->id)
            ->assertSet('inspectingUserId', $student->id)
            ->assertSee('Inspectable Student')
            ->assertSee('$1,200.00')
            ->assertSee('$300.00')
            ->assertDontSee($student->password); // No secret exposure
    }

    public function test_admin_can_deactivate_and_reactivate_student_account(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['status' => 'active']);

        // Deactivate student
        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('toggleStatus', $student->id)
            ->assertSee('deactivated');

        $student->refresh();
        $this->assertEquals('disabled', $student->status);
        $this->assertFalse($student->isActive());

        // Reactivate student
        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('toggleStatus', $student->id)
            ->assertSee('reactivated');

        $student->refresh();
        $this->assertEquals('active', $student->status);
        $this->assertTrue($student->isActive());
    }

    public function test_deactivating_student_terminates_active_sessions(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['status' => 'active']);

        // Insert mock session for this student
        DB::table('sessions')->insert([
            'id' => 'mock_session_id_123',
            'user_id' => $student->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test Agent',
            'payload' => 'mock_payload',
            'last_activity' => time(),
        ]);

        $this->assertDatabaseHas('sessions', ['user_id' => $student->id]);

        $student->deactivate();

        $this->assertDatabaseMissing('sessions', ['user_id' => $student->id]);
    }

    public function test_disabled_student_is_blocked_by_middleware_and_logged_out(): void
    {
        $disabledStudent = User::factory()->disabled()->create();

        $response = $this->actingAs($disabledStudent)->get('/dashboard');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('toggleStatus', $admin->id)
            ->assertSee('Administrators cannot deactivate their own root account');

        $admin->refresh();
        $this->assertTrue($admin->isActive());
    }

    public function test_admin_can_reset_student_financial_baselines(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create([
            'monthly_allowance' => 1500.00,
            'savings_goal' => 500.00,
        ]);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->call('resetStudentFinancialBaselines', $student->id)
            ->assertSee('reset to zero');

        $student->refresh();
        $this->assertEquals('0.00', $student->monthly_allowance);
        $this->assertEquals('0.00', $student->savings_goal);
    }

    // -------------------------------------------------------------
    // 4. OPERATIONAL PLATFORM METRICS
    // -------------------------------------------------------------

    public function test_platform_metrics_calculate_accurately_across_all_students(): void
    {
        $admin = User::factory()->admin()->create();
        $student1 = User::factory()->create(['status' => 'active', 'monthly_allowance' => 1000.00]);
        $student2 = User::factory()->create(['status' => 'disabled', 'monthly_allowance' => 800.00]);

        $foodCat = Category::where('name', 'Food')->first();
        $jobCat = Category::where('name', 'Part-time Job')->first();

        Transaction::factory()->create([
            'user_id' => $student1->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => 50.00,
        ]);
        Transaction::factory()->create([
            'user_id' => $student2->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => 25.00,
        ]);
        Transaction::factory()->create([
            'user_id' => $student1->id,
            'category_id' => $jobCat->id,
            'type' => 'income',
            'amount' => 200.00,
        ]);

        Budget::factory()->create([
            'user_id' => $student1->id,
            'category_id' => $foodCat->id,
            'amount' => 200.00,
            'month_year' => now()->format('Y-m'),
        ]);

        /** @var AdminMetricsService $metricsService */
        $metricsService = app(AdminMetricsService::class);
        $metrics = $metricsService->getPlatformOverviewMetrics();

        // Check students
        $this->assertEquals(2, $metrics['students']['total']);
        $this->assertEquals(1, $metrics['students']['active']);
        $this->assertEquals(1, $metrics['students']['disabled']);
        $this->assertEquals(50.0, $metrics['students']['active_percentage']);

        // Check transactions
        $this->assertEquals(3, $metrics['transactions']['total_count']);
        $this->assertEquals('275.00', $metrics['transactions']['total_volume']);
        $this->assertEquals(2, $metrics['transactions']['expense_count']);
        $this->assertEquals('75.00', $metrics['transactions']['expense_volume']);
        $this->assertEquals(1, $metrics['transactions']['income_count']);
        $this->assertEquals('200.00', $metrics['transactions']['income_volume']);

        // Check budgets
        $this->assertEquals(1, $metrics['budgets']['total_budgets']);
        $this->assertEquals('200.00', $metrics['budgets']['total_budgeted_amount']);

        // Admin Dashboard renders these metrics
        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSee('Tracked Ledger Volume')
            ->assertSee('Food')
            ->assertSee('$275.00');
    }

    public function test_metrics_zero_state_handles_cleanly_without_errors(): void
    {
        // Remove all seeded categories and data to test true zero state
        DB::statement('DELETE FROM transactions');
        DB::statement('DELETE FROM budgets');
        DB::statement('DELETE FROM saving_tips');
        DB::statement('DELETE FROM category_learnings');
        DB::statement('DELETE FROM categories');
        DB::statement('DELETE FROM users');

        $admin = User::factory()->admin()->create();

        /** @var AdminMetricsService $metricsService */
        $metricsService = app(AdminMetricsService::class);
        $metrics = $metricsService->getPlatformOverviewMetrics();

        $this->assertEquals(0, $metrics['students']['total']);
        $this->assertEquals(0, $metrics['transactions']['total_count']);
        $this->assertEquals('0.00', $metrics['transactions']['total_volume']);
        $this->assertEquals(0, $metrics['categories']['total']);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSee('0 active');
    }

    public function test_most_used_categories_ranks_by_transaction_count_descending(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->first();
        $academicsCat = Category::where('name', 'Academics')->first();

        // 3 food transactions
        Transaction::factory()->count(3)->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => 10.00,
        ]);

        // 1 academics transaction
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $academicsCat->id,
            'type' => 'expense',
            'amount' => 50.00,
        ]);

        /** @var AdminMetricsService $metricsService */
        $metricsService = app(AdminMetricsService::class);
        $metrics = $metricsService->getPlatformOverviewMetrics();

        $mostUsed = $metrics['categories']['most_used'];
        $this->assertGreaterThanOrEqual(2, count($mostUsed));
        $this->assertEquals('Food', $mostUsed[0]['name']);
        $this->assertEquals(3, $mostUsed[0]['count']);
        $this->assertEquals('Academics', $mostUsed[1]['name']);
        $this->assertEquals(1, $mostUsed[1]['count']);
    }

    public function test_admin_cannot_deactivate_another_admin_through_user_manager(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        Livewire::actingAs($admin1)
            ->test(UserManager::class)
            ->call('toggleStatus', $admin2->id)
            ->assertSee('Administrator status cannot be mutated from the student manager');

        $admin2->refresh();
        $this->assertTrue($admin2->isActive());
    }

    public function test_admin_can_toggle_student_status_directly_from_dashboard(): void
    {
        $admin = User::factory()->admin()->create();
        $student = User::factory()->create(['status' => 'active']);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->call('toggleStudentStatus', $student->id)
            ->assertSee('deactivated');

        $student->refresh();
        $this->assertFalse($student->isActive());

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->call('toggleStudentStatus', $student->id)
            ->assertSee('reactivated');

        $student->refresh();
        $this->assertTrue($student->isActive());
    }

    public function test_admin_cannot_toggle_admin_status_from_dashboard(): void
    {
        $admin1 = User::factory()->admin()->create();
        $admin2 = User::factory()->admin()->create();

        Livewire::actingAs($admin1)
            ->test(AdminDashboard::class)
            ->call('toggleStudentStatus', $admin2->id)
            ->assertSee('Administrator status cannot be toggled here');

        $admin2->refresh();
        $this->assertTrue($admin2->isActive());
    }

    public function test_category_manager_filters_by_type_and_status(): void
    {
        $admin = User::factory()->admin()->create();
        $cat = Category::where('name', 'Food')->first();
        $cat->update(['is_active' => false]);

        Livewire::actingAs($admin)
            ->test(CategoryManager::class)
            ->set('filterStatus', 'inactive')
            ->assertSee('Food')
            ->set('filterStatus', 'active')
            ->assertDontSee('Food')
            ->set('filterStatus', 'all')
            ->set('filterType', 'income')
            ->assertSee('Allowance')
            ->assertDontSee('Food');
    }

    public function test_user_manager_filters_by_status_and_role_and_sorts(): void
    {
        $admin = User::factory()->admin()->create();
        $activeStudent = User::factory()->create(['name' => 'Active Student', 'status' => 'active']);
        $disabledStudent = User::factory()->create(['name' => 'Disabled Student', 'status' => 'disabled']);

        Livewire::actingAs($admin)
            ->test(UserManager::class)
            ->set('filterStatus', 'disabled')
            ->assertSee('Disabled Student')
            ->assertDontSee('Active Student')
            ->set('filterStatus', 'active')
            ->assertSee('Active Student')
            ->assertDontSee('Disabled Student')
            ->set('sortBy', 'name_asc')
            ->assertSee('Active Student');
    }
}
