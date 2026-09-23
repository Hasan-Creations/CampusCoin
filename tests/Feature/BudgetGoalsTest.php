<?php

namespace Tests\Feature;

use App\Livewire\Student\BudgetManager;
use App\Livewire\Student\Dashboard;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BudgetGoalsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_budgets_screen_can_be_rendered(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get('/budgets');

        $response->assertStatus(200);
        $response->assertSee('Budget Goals');
        $response->assertSee('Total Budgeted');
        $response->assertSee('Set Budget Goal');
    }

    public function test_empty_state_rendered_when_no_budgets(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->assertSee('NO BUDGET GOALS SET')
            ->assertSee('Set First Budget Goal');
    }

    public function test_student_can_create_monthly_budget_for_expense_category(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $month = date('Y-m');

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('openCreateModal')
            ->set('category_id', $foodCat->id)
            ->set('amount', '250.00')
            ->set('month_year', $month)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showModal', false)
            ->assertSee('Budget goal created successfully.');

        $this->assertDatabaseHas('budgets', [
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '250.00',
            'month_year' => $month,
        ]);
    }

    public function test_student_cannot_create_budget_for_income_category(): void
    {
        $student = User::factory()->create();
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('openCreateModal')
            ->set('category_id', $allowanceCat->id)
            ->set('amount', '500.00')
            ->set('month_year', date('Y-m'))
            ->call('save')
            ->assertHasErrors(['category_id']);

        $this->assertDatabaseCount('budgets', 0);
    }

    public function test_student_cannot_create_budget_for_another_students_category(): void
    {
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();

        $privateCat = Category::factory()->create([
            'user_id' => $student1->id,
            'name' => 'Student 1 Custom Expense',
            'type' => 'expense',
            'is_default' => false,
        ]);

        Livewire::actingAs($student2)
            ->test(BudgetManager::class)
            ->call('openCreateModal')
            ->set('category_id', $privateCat->id)
            ->set('amount', '100.00')
            ->set('month_year', date('Y-m'))
            ->call('save')
            ->assertHasErrors(['category_id']);

        $this->assertDatabaseCount('budgets', 0);
    }

    public function test_student_cannot_create_duplicate_budget_for_same_category_and_month(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $month = date('Y-m');

        Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'month_year' => $month,
        ]);

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('openCreateModal')
            ->set('category_id', $foodCat->id)
            ->set('amount', '300.00')
            ->set('month_year', $month)
            ->call('save')
            ->assertHasErrors(['category_id']);

        $this->assertDatabaseCount('budgets', 1);
        $this->assertEquals('200.00', Budget::first()->amount);
    }

    public function test_student_can_update_their_budget_amount(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $month = date('Y-m');

        $budget = Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'month_year' => $month,
        ]);

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('openEditModal', $budget->id)
            ->assertSet('editingId', $budget->id)
            ->assertSet('amount', '200.00')
            ->set('amount', '350.00')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Budget goal updated successfully.');

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'amount' => '350.00',
        ]);
    }

    public function test_student_cannot_update_another_students_budget(): void
    {
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        $budget1 = Budget::create([
            'user_id' => $student1->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'month_year' => date('Y-m'),
        ]);

        Livewire::actingAs($student2)
            ->test(BudgetManager::class)
            ->call('openEditModal', $budget1->id)
            ->assertSee('Budget not found or unauthorized access.')
            ->assertSet('editingId', null);

        $this->assertEquals('200.00', $budget1->fresh()->amount);
    }

    public function test_student_can_delete_their_budget(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        $budget = Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'month_year' => date('Y-m'),
        ]);

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('confirmDelete', $budget->id)
            ->assertSet('showDeleteModal', true)
            ->call('delete')
            ->assertSet('showDeleteModal', false)
            ->assertSee('Budget goal deleted successfully.');

        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }

    public function test_student_cannot_delete_another_students_budget(): void
    {
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        $budget1 = Budget::create([
            'user_id' => $student1->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'month_year' => date('Y-m'),
        ]);

        Livewire::actingAs($student2)
            ->test(BudgetManager::class)
            ->call('confirmDelete', $budget1->id)
            ->assertSee('Budget not found or unauthorized.')
            ->assertSet('showDeleteModal', false);

        $this->assertDatabaseHas('budgets', ['id' => $budget1->id]);
    }

    public function test_budget_consumption_calculated_from_actual_expense_transactions(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        $budget = Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'month_year' => $currentMonth,
        ]);

        // Add 2 expense transactions for Food in current month
        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '35.50',
            'merchant' => 'Campus Dining Hall',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '24.50',
            'merchant' => 'Campus Coffee',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'cash',
        ]);

        $this->assertEquals('60.00', $budget->getSpentAmount());
        $this->assertEquals('40.00', $budget->getRemainingAmount());
        $this->assertEquals(60.0, $budget->getPercentageConsumed());
        $this->assertEquals('on_track', $budget->getStatus());
        $this->assertEquals('On Track', $budget->getStatusLabel());

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->assertSee('60.00')
            ->assertSee('100.00')
            ->assertSee('40.00')
            ->assertSee('On Track');
    }

    public function test_income_transactions_do_not_affect_budget_consumption(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        $budget = Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'month_year' => $currentMonth,
        ]);

        // Expense of $40 on Food
        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '40.00',
            'merchant' => 'Grocery Store',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        // Income of $500 (allowance)
        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'amount' => '500.00',
            'merchant' => 'Campus Stipend',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        // Income must not reduce or offset the spent amount on food
        $this->assertEquals('40.00', $budget->getSpentAmount());
        $this->assertEquals('60.00', $budget->getRemainingAmount());
    }

    public function test_transactions_from_other_months_do_not_affect_current_month_budget(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        $budget = Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'month_year' => $currentMonth,
        ]);

        // Transaction in previous month
        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '80.00',
            'merchant' => 'Last Month Pizza',
            'transaction_date' => $now->copy()->subMonth()->toDateString(),
            'payment_method' => 'card',
        ]);

        // Transaction in current month
        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '25.00',
            'merchant' => 'Current Month Salad',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        $this->assertEquals('25.00', $budget->getSpentAmount());
        $this->assertEquals('75.00', $budget->getRemainingAmount());
    }

    public function test_transactions_from_other_students_do_not_affect_budget_consumption(): void
    {
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        $budget1 = Budget::create([
            'user_id' => $student1->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'month_year' => $currentMonth,
        ]);

        // Student 2 spends $90 on Food
        Transaction::create([
            'user_id' => $student2->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '90.00',
            'merchant' => 'Student 2 Feast',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        // Student 1 spends $15 on Food
        Transaction::create([
            'user_id' => $student1->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '15.00',
            'merchant' => 'Student 1 Snack',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'cash',
        ]);

        $this->assertEquals('15.00', $budget1->getSpentAmount());
        $this->assertEquals('85.00', $budget1->getRemainingAmount());
    }

    public function test_near_limit_status_detected_when_expenses_reach_75_percent(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        $budget = Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'month_year' => $currentMonth,
        ]);

        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '80.00',
            'merchant' => 'Dinner',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        $this->assertTrue($budget->isNearLimit());
        $this->assertFalse($budget->isOverBudget());
        $this->assertFalse($budget->isOnTrack());
        $this->assertEquals('near_limit', $budget->getStatus());
        $this->assertEquals('Near Limit', $budget->getStatusLabel());

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->assertSee('Near Limit')
            ->assertSee('80%');
    }

    public function test_over_budget_status_detected_when_expenses_exceed_limit(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        $budget = Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'month_year' => $currentMonth,
        ]);

        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '125.00',
            'merchant' => 'Big Grocery Run',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        $this->assertTrue($budget->isOverBudget());
        $this->assertFalse($budget->isNearLimit());
        $this->assertFalse($budget->isOnTrack());
        $this->assertEquals('over_budget', $budget->getStatus());
        $this->assertEquals('Over Budget', $budget->getStatusLabel());
        $this->assertEquals('-25.00', $budget->getRemainingAmount());
        $this->assertEquals(125.0, $budget->getPercentageConsumed());

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->assertSee('Over Budget')
            ->assertSee('125%')
            ->assertSee('-$25.00');
    }

    public function test_budget_validation_rules_require_valid_amount_and_category(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('openCreateModal')
            ->set('category_id', null)
            ->set('amount', '')
            ->set('month_year', '')
            ->call('save')
            ->assertHasErrors(['category_id', 'amount', 'month_year']);

        Livewire::actingAs($student)
            ->test(BudgetManager::class)
            ->call('openCreateModal')
            ->set('category_id', 99999)
            ->set('amount', '-10.00')
            ->set('month_year', 'invalid-date')
            ->call('save')
            ->assertHasErrors(['category_id', 'amount', 'month_year']);
    }

    public function test_dashboard_displays_real_budget_goals_and_consumption(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '150.00',
            'month_year' => $currentMonth,
        ]);

        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '45.00',
            'merchant' => 'Campus Subs',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSee('Budget Goals & Spending Caps', false)
            ->assertSee('Food')
            ->assertSee('$45.00')
            ->assertSee('$150.00')
            ->assertSee('30%')
            ->assertSee('On Track');
    }

    public function test_dashboard_displays_over_budget_alert_when_exceeded(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $now = now();
        $currentMonth = $now->format('Y-m');

        Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'month_year' => $currentMonth,
        ]);

        Transaction::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '130.00',
            'merchant' => 'Dinner Banquet',
            'transaction_date' => $now->toDateString(),
            'payment_method' => 'card',
        ]);

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSee('Budget Alert: Food has exceeded monthly limit')
            ->assertSee('Over Budget')
            ->assertSee('130%');
    }
}
