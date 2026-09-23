<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FinancialCalculationService;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class CashFlowTrendsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_six_month_cash_flow_accurately_aggregates_income_expense_and_net(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();

        $ref = Carbon::parse('2026-09-15');

        // Month 1: 5 months ago (Apr 2026) -> Income: $500, Expense: $300
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'amount' => '500.00',
            'transaction_date' => '2026-04-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '300.00',
            'transaction_date' => '2026-04-20',
        ]);

        // Month 6: Current month (Sep 2026) -> Income: $1000, Expense: $450
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'amount' => '1000.00',
            'transaction_date' => '2026-09-05',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '450.00',
            'transaction_date' => '2026-09-12',
        ]);

        $service = app(FinancialCalculationService::class);
        $result = $service->getSixMonthCashFlow($student->id, $ref);

        $this->assertCount(6, $result['months']);

        // Check April (index 0)
        $apr = $result['months'][0];
        $this->assertEquals('2026-04', $apr['month_key']);
        $this->assertEquals('500.00', $apr['income']);
        $this->assertEquals('300.00', $apr['expense']);
        $this->assertEquals('200.00', $apr['net']);
        $this->assertEquals('positive', $apr['status']);
        $this->assertEquals(40.0, $apr['savings_rate']);

        // Check September (index 5)
        $sep = $result['months'][5];
        $this->assertEquals('2026-09', $sep['month_key']);
        $this->assertEquals('1000.00', $sep['income']);
        $this->assertEquals('450.00', $sep['expense']);
        $this->assertEquals('550.00', $sep['net']);
        $this->assertEquals('positive', $sep['status']);
        $this->assertTrue($sep['is_current']);

        // Check Totals
        $this->assertEquals('1500.00', $result['total_income']);
        $this->assertEquals('750.00', $result['total_expense']);
        $this->assertEquals('750.00', $result['total_net']);
        $this->assertEquals('125.00', $result['average_monthly_expense']);
    }

    public function test_six_month_cash_flow_handles_empty_months_and_zero_values_gracefully(): void
    {
        $student = User::factory()->create();
        $ref = Carbon::parse('2026-09-15');

        $service = app(FinancialCalculationService::class);
        $result = $service->getSixMonthCashFlow($student->id, $ref);

        $this->assertCount(6, $result['months']);
        $this->assertEquals('0.00', $result['total_income']);
        $this->assertEquals('0.00', $result['total_expense']);
        $this->assertEquals('0.00', $result['total_net']);
        $this->assertEquals('0.00', $result['average_monthly_expense']);

        foreach ($result['months'] as $m) {
            $this->assertEquals('0.00', $m['income']);
            $this->assertEquals('0.00', $m['expense']);
            $this->assertEquals('0.00', $m['net']);
            $this->assertEquals('balanced', $m['status']);
            $this->assertEquals(0.0, $m['savings_rate']);
        }
    }

    public function test_six_month_cash_flow_handles_income_only_and_expense_only_months(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $giftCat = Category::where('name', 'Gift')->where('type', 'income')->first();
        $ref = Carbon::parse('2026-09-15');

        // Aug: Only income ($400)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $giftCat->id,
            'type' => 'income',
            'amount' => '400.00',
            'transaction_date' => '2026-08-15',
        ]);

        // Sep: Only expense ($250)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '250.00',
            'transaction_date' => '2026-09-10',
        ]);

        $service = app(FinancialCalculationService::class);
        $result = $service->getSixMonthCashFlow($student->id, $ref);

        $aug = $result['months'][4]; // August
        $this->assertEquals('400.00', $aug['income']);
        $this->assertEquals('0.00', $aug['expense']);
        $this->assertEquals('400.00', $aug['net']);
        $this->assertEquals('positive', $aug['status']);

        $sep = $result['months'][5]; // September
        $this->assertEquals('0.00', $sep['income']);
        $this->assertEquals('250.00', $sep['expense']);
        $this->assertEquals('-250.00', $sep['net']);
        $this->assertEquals('negative', $sep['status']);
        $this->assertEquals(0.0, $sep['savings_rate']);
    }

    public function test_six_month_cash_flow_enforces_strict_student_data_isolation(): void
    {
        $studentA = User::factory()->create();
        $studentB = User::factory()->create();

        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();
        $ref = Carbon::parse('2026-09-15');

        // Student A has $100 income, $50 expense
        Transaction::factory()->create([
            'user_id' => $studentA->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'amount' => '100.00',
            'transaction_date' => '2026-09-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $studentA->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '50.00',
            'transaction_date' => '2026-09-12',
        ]);

        // Student B has $5000 income, $3000 expense
        Transaction::factory()->create([
            'user_id' => $studentB->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'amount' => '5000.00',
            'transaction_date' => '2026-09-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $studentB->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '3000.00',
            'transaction_date' => '2026-09-12',
        ]);

        $service = app(FinancialCalculationService::class);
        $resultA = $service->getSixMonthCashFlow($studentA->id, $ref);

        $this->assertEquals('100.00', $resultA['total_income']);
        $this->assertEquals('50.00', $resultA['total_expense']);
        $this->assertEquals('50.00', $resultA['total_net']);
    }

    public function test_category_comparison_computes_delta_and_percentage_accurately(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $transportCat = Category::where('name', 'Transport')->where('type', 'expense')->first();
        $ref = Carbon::parse('2026-09-15');

        // Food: Last month $100, This month $150 -> Delta +$50 (+50.0%)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '100.00',
            'transaction_date' => '2026-08-15',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '150.00',
            'transaction_date' => '2026-09-10',
        ]);

        // Transport: Last month $200, This month $100 -> Delta -$100 (-50.0%)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $transportCat->id,
            'type' => 'expense',
            'amount' => '200.00',
            'transaction_date' => '2026-08-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $transportCat->id,
            'type' => 'expense',
            'amount' => '100.00',
            'transaction_date' => '2026-09-05',
        ]);

        $service = app(FinancialCalculationService::class);
        $res = $service->getCategoryComparisons($student->id, 'this_month', $ref);

        $this->assertEquals('250.00', $res['current_total']);
        $this->assertEquals('300.00', $res['previous_total']);
        $this->assertEquals('-50.00', $res['total_delta']);
        $this->assertEquals('decreased', $res['total_change']['direction']);

        $categories = collect($res['categories'])->keyBy('category_id');

        $food = $categories->get($foodCat->id);
        $this->assertEquals('150.00', $food['current_spent']);
        $this->assertEquals('100.00', $food['previous_spent']);
        $this->assertEquals('50.00', $food['delta']);
        $this->assertEquals('increased', $food['direction']);
        $this->assertEquals(50.0, $food['pct_change']);
        $this->assertEquals('+50.0%', $food['pct_formatted']);
        $this->assertFalse($food['is_new']);

        $trans = $categories->get($transportCat->id);
        $this->assertEquals('100.00', $trans['current_spent']);
        $this->assertEquals('200.00', $trans['previous_spent']);
        $this->assertEquals('-100.00', $trans['delta']);
        $this->assertEquals('decreased', $trans['direction']);
        $this->assertEquals(-50.0, $trans['pct_change']);
        $this->assertEquals('-50.0%', $trans['pct_formatted']);
    }

    public function test_category_comparison_guards_against_divide_by_zero_when_previous_is_zero(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $ref = Carbon::parse('2026-09-15');

        // Food: No prior expenses, $80 this month
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '80.00',
            'transaction_date' => '2026-09-08',
        ]);

        $service = app(FinancialCalculationService::class);
        $res = $service->getCategoryComparisons($student->id, 'this_month', $ref);

        $this->assertCount(1, $res['categories']);
        $food = $res['categories'][0];

        $this->assertEquals('80.00', $food['current_spent']);
        $this->assertEquals('0.00', $food['previous_spent']);
        $this->assertEquals('80.00', $food['delta']);
        $this->assertTrue($food['is_new']);
        $this->assertEquals('+100%', $food['pct_formatted']);
        $this->assertEquals('increased', $food['direction']);
    }

    public function test_category_comparison_period_filtering(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $ref = Carbon::parse('2026-09-15');

        // July transaction (within last 3 months: Jul, Aug, Sep)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '60.00',
            'transaction_date' => '2026-07-20',
        ]);

        // September transaction (this month)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '90.00',
            'transaction_date' => '2026-09-05',
        ]);

        $service = app(FinancialCalculationService::class);

        // 'this_month' should only capture September ($90.00)
        $thisMonth = $service->getCategoryComparisons($student->id, 'this_month', $ref);
        $this->assertEquals('90.00', $thisMonth['current_total']);

        // 'last_3_months' should capture July + September ($150.00)
        $last3 = $service->getCategoryComparisons($student->id, 'last_3_months', $ref);
        $this->assertEquals('150.00', $last3['current_total']);
    }

    public function test_dashboard_component_renders_cash_flow_and_comparative_widgets(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertStatus(200)
            ->assertSee('Historical Cash Flow (Income vs. Expense)')
            ->assertSee('6-Month Velocity')
            ->assertSee('Category Spending Trends')
            ->assertSee('Comparative Analysis')
            ->assertSet('timePeriod', 'this_month');
    }

    public function test_dashboard_period_switching_updates_active_period(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSet('timePeriod', 'this_month')
            ->call('setTimePeriod', 'last_3_months')
            ->assertSet('timePeriod', 'last_3_months')
            ->call('setTimePeriod', 'last_6_months')
            ->assertSet('timePeriod', 'last_6_months')
            ->call('setTimePeriod', 'year')
            ->assertSet('timePeriod', 'year')
            // Invalid period should be ignored
            ->call('setTimePeriod', 'invalid_period')
            ->assertSet('timePeriod', 'year');
    }

    public function test_dashboard_multi_tenant_data_isolation(): void
    {
        $studentA = User::factory()->create();
        $studentB = User::factory()->create();

        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        // Student B logs a transaction with unique merchant
        Transaction::factory()->create([
            'user_id' => $studentB->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '999.99',
            'merchant' => 'SuperSecretStoreB',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        // Student A renders dashboard
        Livewire::actingAs($studentA)
            ->test(Dashboard::class)
            ->assertDontSee('SuperSecretStoreB')
            ->assertDontSee('999.99');
    }
}
