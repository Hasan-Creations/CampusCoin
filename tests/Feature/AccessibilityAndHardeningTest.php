<?php

namespace Tests\Feature;

use App\Livewire\Admin\CategoryManager as AdminCategoryManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\UserManager as AdminUserManager;
use App\Livewire\Student\BudgetManager;
use App\Livewire\Student\CategoryManager;
use App\Livewire\Student\MonthlyReports;
use App\Livewire\Student\SavingTipsManager;
use App\Livewire\Student\TransactionList;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AccessibilityAndHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_welcome_page_contains_accessibility_features(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('class="skip-to-content"', false);
        $response->assertSee('href="#main-content"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('tabindex="-1"', false);
        $response->assertSee('Toggle dark mode', false);
    }

    public function test_auth_pages_have_accessible_forms_and_autocomplete_attributes(): void
    {
        // Student Login
        $loginRes = $this->get('/login');
        $loginRes->assertStatus(200);
        $loginRes->assertSee('class="skip-to-content"', false);
        $loginRes->assertSee('id="main-content"', false);
        $loginRes->assertSee('autocomplete="email"', false);
        $loginRes->assertSee('autocomplete="current-password"', false);
        $loginRes->assertSee('aria-label="Student Sign In Form"', false);

        // Student Register
        $regRes = $this->get('/register');
        $regRes->assertStatus(200);
        $regRes->assertSee('class="skip-to-content"', false);
        $regRes->assertSee('autocomplete="name"', false);
        $regRes->assertSee('autocomplete="email"', false);
        $regRes->assertSee('autocomplete="new-password"', false);
        $regRes->assertSee('aria-label="Student Registration Form"', false);

        // Admin Login
        $adminLoginRes = $this->get('/admin/login');
        $adminLoginRes->assertStatus(200);
        $adminLoginRes->assertSee('class="skip-to-content"', false);
        $adminLoginRes->assertSee('autocomplete="email"', false);
        $adminLoginRes->assertSee('autocomplete="current-password"', false);
        $adminLoginRes->assertSee('aria-label="Administrator Direct Access Form"', false);
    }

    public function test_student_layout_contains_skip_navigation_and_accessibility_controls(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('class="skip-to-content"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('tabindex="-1"', false);
        $response->assertSee('Toggle dark mode', false);
        $response->assertSee('Open mobile navigation menu', false);
        $response->assertSee('mobileNavOpen', false);
    }

    public function test_admin_layout_contains_skip_navigation_and_accessibility_controls(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('class="skip-to-content"', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('tabindex="-1"', false);
        $response->assertSee('Toggle dark mode', false);
        $response->assertSee('Open admin navigation menu', false);
    }

    public function test_transaction_list_table_has_accessible_headers_and_dialog_semantics(): void
    {
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $category = Category::first();

        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 12.50,
            'transaction_date' => now()->toDateString(),
            'merchant' => 'Campus Cafe',
            'payment_method' => 'card',
        ]);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->assertSeeHtml('<th scope="col"')
            ->assertSeeHtml('aria-sort=')
            ->call('openCreateModal')
            ->assertSeeHtml('role="dialog"')
            ->assertSeeHtml('aria-modal="true"')
            ->assertSeeHtml('aria-labelledby="modal-transaction-title"');
    }

    public function test_category_managers_have_accessible_modal_dialogs_and_labels(): void
    {
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        // Student Category Manager
        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->call('openCreateModal')
            ->assertSeeHtml('role="dialog"')
            ->assertSeeHtml('aria-modal="true"')
            ->assertSeeHtml('aria-labelledby="student-category-modal-title"');

        // Admin Category Manager
        Livewire::actingAs($admin)
            ->test(AdminCategoryManager::class)
            ->assertSeeHtml('<th scope="col"')
            ->call('openCreateModal')
            ->assertSeeHtml('role="dialog"')
            ->assertSeeHtml('aria-modal="true"')
            ->assertSeeHtml('aria-labelledby="admin-category-modal-title"');
    }

    public function test_budget_manager_has_dialog_semantics_and_labels(): void
    {
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('openCreateModal')
            ->assertSeeHtml('role="dialog"')
            ->assertSeeHtml('aria-modal="true"')
            ->assertSeeHtml('aria-labelledby="modal-budget-title"');
    }

    public function test_monthly_reports_view_contains_accessible_tables_and_tablist(): void
    {
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $category = Category::first();

        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 15.00,
            'transaction_date' => now()->toDateString(),
            'merchant' => 'Campus Bookstore',
            'payment_method' => 'card',
        ]);

        Livewire::actingAs($student)
            ->test(MonthlyReports::class)
            ->assertSeeHtml('role="tablist"')
            ->assertSeeHtml('aria-label="Report Views"')
            ->assertSeeHtml('role="tab"')
            ->assertSeeHtml('<th scope="col"');
    }

    public function test_saving_tips_manager_has_tablist_and_accessible_actions(): void
    {
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);

        Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->assertSeeHtml('role="tablist"')
            ->assertSeeHtml('role="tab"')
            ->assertSee('Active Opportunities')
            ->assertSee('Pinned');
    }

    public function test_admin_user_manager_has_dialog_semantics_and_accessible_headers(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test(AdminUserManager::class)
            ->assertSeeHtml('<th scope="col"')
            ->call('inspectUser', $student->id)
            ->assertSeeHtml('role="dialog"')
            ->assertSeeHtml('aria-modal="true"')
            ->assertSeeHtml('aria-labelledby="admin-inspect-user-title"');
    }

    public function test_admin_dashboard_has_accessible_table_headers_and_status_alerts(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSeeHtml('<th scope="col"')
            ->assertSee('Most-Used Categories')
            ->assertSee('Recent Registered Campus Accounts');
    }

    public function test_input_sanitization_and_xss_prevention_on_transactions(): void
    {
        $student = User::factory()->create(['role' => 'student', 'status' => 'active']);
        $category = Category::first();

        $xssMerchant = 'Café <script>alert("xss")</script>';
        $xssDescription = 'Espresso <b onmouseover="alert(1)">shot</b>';

        $tx = Transaction::create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 4.50,
            'transaction_date' => now()->toDateString(),
            'merchant' => $xssMerchant,
            'description' => $xssDescription,
            'payment_method' => 'card',
        ]);

        $response = $this->actingAs($student)->get('/transactions');
        $response->assertStatus(200);

        // Assert that the raw script tags are NOT rendered raw into the DOM
        $response->assertDontSee('<script>alert("xss")</script>', false);
        // Assert that Blade has properly HTML-entity-encoded the merchant string
        $response->assertSee(e($xssMerchant), false);
    }
}
