<?php

namespace Tests\Feature;

use App\Livewire\Student\Dashboard;
use App\Livewire\Student\SavingTipsManager;
use App\Models\Budget;
use App\Models\Category;
use App\Models\SavingTip;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SavingTipsService;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SavingTipsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_unauthenticated_user_cannot_access_saving_tips_screen(): void
    {
        $response = $this->get('/tips');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_student_can_view_saving_tips_screen(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->get('/tips');

        $response->assertStatus(200);
        $response->assertSee('Saving Opportunities');
        $response->assertSee('Re-evaluate Ledger');
        $response->assertSee('Identified Potential Savings');
        $response->assertSee('Active Opportunities');
        $response->assertSee('Pinned Strategies');
    }

    public function test_saving_tips_manager_displays_empty_state_when_no_tips_exist(): void
    {
        $student = User::factory()->create([
            'savings_goal' => 0.00,
        ]);

        Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->assertSee('No Active Budget Deviations Detected')
            ->assertSee('Log Transaction');
    }

    public function test_rule_category_above_average_triggers_when_spending_exceeds_threshold(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        $refDate = Carbon::parse('2026-10-15');

        // Past 3 months: July, August, September with average $100/mo ($100 each month)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'type' => 'expense',
            'transaction_date' => '2026-07-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'type' => 'expense',
            'transaction_date' => '2026-08-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '100.00',
            'type' => 'expense',
            'transaction_date' => '2026-09-10',
        ]);

        // Current month (October): Spends $150 (50% above average of $100, delta $50 >= $15)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '150.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-05',
        ]);

        $service = app(SavingTipsService::class);
        $tips = $service->evaluateCategoryAboveAverage($student, $refDate);

        $this->assertCount(1, $tips);
        $this->assertSame('category_above_average', $tips[0]['rule_key']);
        $this->assertSame($foodCat->id, $tips[0]['category_id']);
        $this->assertSame('50.00', $tips[0]['estimated_savings']);
        $this->assertStringContainsString('+50%', $tips[0]['title']);
    }

    public function test_rule_category_above_average_does_not_trigger_when_below_threshold(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $refDate = Carbon::parse('2026-10-15');

        // Past 3 months: $100/mo avg
        for ($m = 7; $m <= 9; $m++) {
            Transaction::factory()->create([
                'user_id' => $student->id,
                'category_id' => $foodCat->id,
                'amount' => '100.00',
                'type' => 'expense',
                'transaction_date' => "2026-0{$m}-10",
            ]);
        }

        // Current month: Spends $110 (only 10% above average, threshold is >20%)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '110.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-05',
        ]);

        $service = app(SavingTipsService::class);
        $tips = $service->evaluateCategoryAboveAverage($student, $refDate);

        $this->assertEmpty($tips);
    }

    public function test_rule_category_budget_alert_triggers_when_budget_exceeded(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $refDate = Carbon::parse('2026-10-15');

        // Monthly budget: $200.00
        Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'month_year' => '2026-10',
        ]);

        // Spent: $250.00 (Exceeded by $50.00)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '250.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-10',
        ]);

        $service = app(SavingTipsService::class);
        $tips = $service->evaluateCategoryBudgetAlert($student, $refDate);

        $this->assertCount(1, $tips);
        $this->assertSame('category_budget_alert', $tips[0]['rule_key']);
        $this->assertSame('exceeded', $tips[0]['trigger_data']['status']);
        $this->assertSame('50.00', $tips[0]['estimated_savings']);
        $this->assertStringContainsString('Exceeded monthly budget cap by $50.00', $tips[0]['title']);
    }

    public function test_rule_category_budget_alert_triggers_when_approaching_budget(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $refDate = Carbon::parse('2026-10-15');

        // Budget: $200.00
        Budget::create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'month_year' => '2026-10',
        ]);

        // Spent: $170.00 (85% of budget, approaching threshold is >=80%)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '170.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-10',
        ]);

        $service = app(SavingTipsService::class);
        $tips = $service->evaluateCategoryBudgetAlert($student, $refDate);

        $this->assertCount(1, $tips);
        $this->assertSame('category_budget_alert', $tips[0]['rule_key']);
        $this->assertSame('approaching', $tips[0]['trigger_data']['status']);
        $this->assertSame('30.00', $tips[0]['estimated_savings']);
        $this->assertStringContainsString('Nearing monthly budget cap (85% consumed)', $tips[0]['title']);
    }

    public function test_rule_high_spending_share_triggers_when_category_dominates_over_40_percent(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $transportCat = Category::where('name', 'Transport')->where('type', 'expense')->first();
        $refDate = Carbon::parse('2026-10-15');

        // Food: $300, Transport: $100 -> Total $400. Food is 75% (> 40%)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '300.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-05',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $transportCat->id,
            'amount' => '100.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-08',
        ]);

        $service = app(SavingTipsService::class);
        $tips = $service->evaluateHighSpendingShare($student, $refDate);

        $this->assertCount(1, $tips);
        $this->assertSame('high_spending_share', $tips[0]['rule_key']);
        $this->assertSame($foodCat->id, $tips[0]['category_id']);
        $this->assertStringContainsString('Dominates 75% of your total spending', $tips[0]['title']);
        // Target 40% of $400 is $160; excess is $300 - $160 = $140.00
        $this->assertSame('140.00', $tips[0]['estimated_savings']);
    }

    public function test_rule_month_over_month_growth_triggers_when_spending_rises_over_25_percent_and_50_dollars(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $refDate = Carbon::parse('2026-10-15');

        // Previous month (September): $200.00
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '200.00',
            'type' => 'expense',
            'transaction_date' => '2026-09-15',
        ]);

        // Current month (October): $350.00 (75% increase, delta $150.00 > $50.00)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '350.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-10',
        ]);

        $service = app(SavingTipsService::class);
        $tips = $service->evaluateMonthOverMonthGrowth($student, $refDate);

        $this->assertCount(1, $tips);
        $this->assertSame('month_over_month_growth', $tips[0]['rule_key']);
        $this->assertNull($tips[0]['category_id']);
        $this->assertSame('150.00', $tips[0]['estimated_savings']);
        $this->assertStringContainsString('Overall spending is up 75% compared to last month', $tips[0]['title']);
    }

    public function test_rule_savings_goal_lagging_triggers_when_net_savings_is_behind_monthly_goal(): void
    {
        $student = User::factory()->create([
            'savings_goal' => '300.00',
        ]);
        $salaryCat = Category::where('name', 'Allowance')->where('type', 'income')->first();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $refDate = Carbon::parse('2026-10-15');

        // Income: $800.00
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $salaryCat->id,
            'amount' => '800.00',
            'type' => 'income',
            'transaction_date' => '2026-10-01',
        ]);

        // Expenses: $700.00 -> Net savings: $100.00. Goal: $300.00 -> Deficit: $200.00
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '700.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-10',
        ]);

        $service = app(SavingTipsService::class);
        $tips = $service->evaluateSavingsGoalLagging($student, $refDate);

        $this->assertCount(1, $tips);
        $this->assertSame('savings_goal_lagging', $tips[0]['rule_key']);
        $this->assertNull($tips[0]['category_id']);
        $this->assertSame('200.00', $tips[0]['estimated_savings']);
        $this->assertStringContainsString('Monthly savings target is lagging by $200.00', $tips[0]['title']);
    }

    public function test_saving_tips_service_ranks_tips_by_estimated_savings_descending(): void
    {
        $student = User::factory()->create([
            'savings_goal' => '300.00',
        ]);
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $salaryCat = Category::where('name', 'Allowance')->where('type', 'income')->first();
        $refDate = Carbon::parse('2026-10-15');

        // Past 3 months for food: $100/mo
        for ($m = 7; $m <= 9; $m++) {
            Transaction::factory()->create([
                'user_id' => $student->id,
                'category_id' => $foodCat->id,
                'amount' => '100.00',
                'type' => 'expense',
                'transaction_date' => "2026-0{$m}-10",
            ]);
        }

        // Current month: Food $200 (delta $100), Income $500 (savings = $300 - $300 = $0, deficit $300)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $salaryCat->id,
            'amount' => '500.00',
            'type' => 'income',
            'transaction_date' => '2026-10-01',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'amount' => '500.00',
            'type' => 'expense',
            'transaction_date' => '2026-10-10',
        ]);

        $service = app(SavingTipsService::class);
        $evaluated = $service->evaluate($student, $refDate);

        $this->assertGreaterThanOrEqual(2, count($evaluated));
        for ($i = 0; $i < count($evaluated) - 1; $i++) {
            $this->assertGreaterThanOrEqual(
                (float) $evaluated[$i + 1]['estimated_savings'],
                (float) $evaluated[$i]['estimated_savings']
            );
        }
    }

    public function test_student_can_pin_and_unpin_saving_tip_via_livewire(): void
    {
        $student = User::factory()->create();
        $tip = SavingTip::factory()->create([
            'user_id' => $student->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->call('pinTip', $tip->id)
            ->assertSee('Saved tip pinned to your bookmarks.');

        $tip->refresh();
        $this->assertTrue($tip->isPinned());
        $this->assertNotNull($tip->pinned_at);

        Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->call('unpinTip', $tip->id)
            ->assertSee('Tip unpinned and returned to active list.');

        $tip->refresh();
        $this->assertTrue($tip->isActive());
        $this->assertNull($tip->pinned_at);
    }

    public function test_student_can_dismiss_and_restore_saving_tip_via_livewire(): void
    {
        $student = User::factory()->create();
        $tip = SavingTip::factory()->create([
            'user_id' => $student->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->call('dismissTip', $tip->id)
            ->assertSee('Tip dismissed.');

        $tip->refresh();
        $this->assertTrue($tip->isDismissed());
        $this->assertNotNull($tip->dismissed_at);

        Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->call('restoreTip', $tip->id)
            ->assertSee('Tip restored to active opportunities.');

        $tip->refresh();
        $this->assertTrue($tip->isActive());
        $this->assertNull($tip->dismissed_at);
    }

    public function test_sync_tips_preserves_pinned_and_dismissed_states_and_cleans_up_obsolete_active_tips(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $transportCat = Category::where('name', 'Transport')->where('type', 'expense')->first();

        // 1. Pinned tip for Food
        $pinnedTip = SavingTip::factory()->pinned()->create([
            'user_id' => $student->id,
            'rule_key' => 'category_above_average',
            'category_id' => $foodCat->id,
            'estimated_savings' => '50.00',
        ]);

        // 2. Dismissed tip for Transport
        $dismissedTip = SavingTip::factory()->dismissed()->create([
            'user_id' => $student->id,
            'rule_key' => 'high_spending_share',
            'category_id' => $transportCat->id,
            'estimated_savings' => '30.00',
        ]);

        // 3. Obsolete active tip that has no ledger trigger
        $obsoleteTip = SavingTip::factory()->create([
            'user_id' => $student->id,
            'rule_key' => 'month_over_month_growth',
            'category_id' => null,
            'status' => 'active',
        ]);

        $service = app(SavingTipsService::class);
        $synced = $service->syncTips($student, Carbon::parse('2026-10-15'));

        // Pinned and dismissed must survive
        $this->assertDatabaseHas('saving_tips', ['id' => $pinnedTip->id, 'status' => 'pinned']);
        $this->assertDatabaseHas('saving_tips', ['id' => $dismissedTip->id, 'status' => 'dismissed']);

        // Obsolete active tip must be deleted
        $this->assertDatabaseMissing('saving_tips', ['id' => $obsoleteTip->id]);
    }

    public function test_multi_tenant_isolation_student_cannot_view_or_manipulate_another_students_tips(): void
    {
        $studentA = User::factory()->create();
        $studentB = User::factory()->create();

        $tipB = SavingTip::factory()->create([
            'user_id' => $studentB->id,
            'title' => 'Secret Tip for Student B',
            'status' => 'active',
        ]);

        // Student A renders SavingTipsManager: must not see Student B's tip
        Livewire::actingAs($studentA)
            ->test(SavingTipsManager::class)
            ->assertDontSee('Secret Tip for Student B');

        // Student A attempts to pin Student B's tip: must fail with 404
        Livewire::actingAs($studentA)
            ->test(SavingTipsManager::class)
            ->call('pinTip', $tipB->id)
            ->assertStatus(404);

        $this->assertTrue($tipB->fresh()->isActive());
    }

    public function test_saving_tips_manager_tab_navigation_and_counts(): void
    {
        $student = User::factory()->create();

        SavingTip::factory()->create([
            'user_id' => $student->id,
            'title' => 'Active Strategy Alpha',
            'status' => 'active',
        ]);
        SavingTip::factory()->pinned()->create([
            'user_id' => $student->id,
            'title' => 'Pinned Strategy Beta',
        ]);
        SavingTip::factory()->dismissed()->create([
            'user_id' => $student->id,
            'title' => 'Dismissed Strategy Gamma',
        ]);

        $test = Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->assertSee('Active Strategy Alpha')
            ->assertDontSee('Pinned Strategy Beta')
            ->assertDontSee('Dismissed Strategy Gamma');

        // Switch to Pinned tab
        $test->call('setTab', 'pinned')
            ->assertSee('Pinned Strategy Beta')
            ->assertDontSee('Active Strategy Alpha');

        // Switch to Dismissed tab
        $test->call('setTab', 'dismissed')
            ->assertSee('Dismissed Strategy Gamma')
            ->assertDontSee('Pinned Strategy Beta');
    }

    public function test_dashboard_displays_top_saving_tips_widget_ordered_by_pinned_then_savings(): void
    {
        $student = User::factory()->create();

        // Tip 1: Active with high savings
        $tipActiveHigh = SavingTip::factory()->create([
            'user_id' => $student->id,
            'title' => 'High Savings Active Tip',
            'estimated_savings' => '120.00',
            'status' => 'active',
        ]);

        // Tip 2: Pinned with lower savings (should be ordered first because it is pinned)
        $tipPinned = SavingTip::factory()->pinned()->create([
            'user_id' => $student->id,
            'title' => 'Pinned Moderate Tip',
            'estimated_savings' => '40.00',
        ]);

        // Tip 3: Active with moderate savings
        $tipActiveMid = SavingTip::factory()->create([
            'user_id' => $student->id,
            'title' => 'Mid Savings Active Tip',
            'estimated_savings' => '80.00',
            'status' => 'active',
        ]);

        // Tip 4: Active with lowest savings (should not be in top 3)
        $tipActiveLow = SavingTip::factory()->create([
            'user_id' => $student->id,
            'title' => 'Low Savings Active Tip',
            'estimated_savings' => '10.00',
            'status' => 'active',
        ]);

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->assertSee('Saving Opportunities')
            ->assertSee('High Savings Active Tip')
            ->assertSee('Pinned Moderate Tip')
            ->assertSee('Mid Savings Active Tip')
            ->assertDontSee('Low Savings Active Tip');
    }

    public function test_student_can_pin_and_dismiss_tip_from_dashboard_widget(): void
    {
        $student = User::factory()->create();
        $tip = SavingTip::factory()->create([
            'user_id' => $student->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->call('pinTip', $tip->id);

        $this->assertTrue($tip->fresh()->isPinned());

        Livewire::actingAs($student)
            ->test(Dashboard::class)
            ->call('dismissTip', $tip->id);

        $this->assertTrue($tip->fresh()->isDismissed());
    }

    public function test_saving_tips_manager_refresh_action_re_evaluates_tips(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(SavingTipsManager::class)
            ->call('refreshTips')
            ->assertSee('Saving tips re-evaluated against your latest financial ledger.');
    }
}
