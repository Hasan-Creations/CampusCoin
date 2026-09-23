<?php

namespace Tests\Feature;

use App\Livewire\Student\MonthlyReports;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\FinancialCalculationService;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class MonthlyReportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_reports_screen_can_be_rendered(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('Monthly Financial Reports');
        $response->assertSee('Total Inflow');
        $response->assertSee('Total Outflow');
        $response->assertSee('Category Spending Breakdown');
        $response->assertSee('Download PDF');
        $response->assertSee('CSV Export');
    }

    public function test_guest_cannot_access_reports_screen(): void
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    public function test_report_summary_totals_accurately_calculate_inflow_outflow_and_net(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();

        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-30');

        // Income: $1000.00
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'amount' => '1000.00',
            'transaction_date' => '2026-09-05',
        ]);

        // Expenses: $300.00 + $150.00 = $450.00
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '300.00',
            'transaction_date' => '2026-09-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '150.00',
            'transaction_date' => '2026-09-15',
        ]);

        $service = app(FinancialCalculationService::class);
        $summary = $service->getReportSummary($student->id, $start, $end);

        $this->assertEquals('1000.00', $summary['total_income']);
        $this->assertEquals('450.00', $summary['total_expense']);
        $this->assertEquals('550.00', $summary['net_movement']);
        $this->assertEquals('positive', $summary['status']);
        $this->assertEquals(3, $summary['total_count']);
        $this->assertEquals(1, $summary['income_count']);
        $this->assertEquals(2, $summary['expense_count']);
        $this->assertEquals(55.0, $summary['savings_rate']);
    }

    public function test_category_wise_spending_report_computes_shares_counts_and_averages(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $transportCat = Category::where('name', 'Transport')->where('type', 'expense')->first();

        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-30');

        // Food: 2 transactions ($200 + $100 = $300)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '200.00',
            'transaction_date' => '2026-09-05',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '100.00',
            'transaction_date' => '2026-09-10',
        ]);

        // Transport: 1 transaction ($100)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $transportCat->id,
            'type' => 'expense',
            'amount' => '100.00',
            'transaction_date' => '2026-09-12',
        ]);

        $service = app(FinancialCalculationService::class);
        $report = $service->getCategoryWiseReport($student->id, $start, $end);

        $this->assertEquals('400.00', $report['total_spent']);
        $this->assertEquals(2, $report['category_count']);

        $byCat = collect($report['categories'])->keyBy('category_id');

        $food = $byCat->get($foodCat->id);
        $this->assertEquals('300.00', $food['spent']);
        $this->assertEquals(2, $food['count']);
        $this->assertEquals('150.00', $food['average_amount']);
        $this->assertEquals(75.0, $food['percentage_of_total']);

        $trans = $byCat->get($transportCat->id);
        $this->assertEquals('100.00', $trans['spent']);
        $this->assertEquals(1, $trans['count']);
        $this->assertEquals('100.00', $trans['average_amount']);
        $this->assertEquals(25.0, $trans['percentage_of_total']);
    }

    public function test_category_comparison_handles_zero_spending_and_new_categories_safely(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        $start = Carbon::parse('2026-09-01');
        $end = Carbon::parse('2026-09-30');

        // New category with $75 spend this month and zero in prior period
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '75.00',
            'transaction_date' => '2026-09-10',
        ]);

        $service = app(FinancialCalculationService::class);
        $report = $service->getCategoryWiseReport($student->id, $start, $end);

        $food = $report['categories'][0];
        $this->assertEquals('75.00', $food['spent']);
        $this->assertEquals('0.00', $food['prev_spent']);
        $this->assertEquals('75.00', $food['delta']);
        $this->assertTrue($food['is_new']);
        $this->assertEquals('+100%', $food['pct_formatted']);
    }

    public function test_daily_velocity_summary_groups_by_calendar_day(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();
        $ref = Carbon::parse('2026-09-20');

        // Sep 10: Income $500, Expense $50
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'amount' => '500.00',
            'transaction_date' => '2026-09-10',
        ]);
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '50.00',
            'transaction_date' => '2026-09-10',
        ]);

        // Sep 15: Expense $80
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '80.00',
            'transaction_date' => '2026-09-15',
        ]);

        $service = app(FinancialCalculationService::class);
        $daily = $service->getCurrentMonthDailySummary($student->id, $ref);

        $this->assertEquals(2, $daily['total_days_active']);

        $byDate = collect($daily['days'])->keyBy('date');

        $sep10 = $byDate->get('2026-09-10');
        $this->assertEquals('500.00', $sep10['income']);
        $this->assertEquals('50.00', $sep10['expense']);
        $this->assertEquals('450.00', $sep10['net']);
        $this->assertEquals('positive', $sep10['status']);
        $this->assertEquals(2, $sep10['count']);

        $sep15 = $byDate->get('2026-09-15');
        $this->assertEquals('0.00', $sep15['income']);
        $this->assertEquals('80.00', $sep15['expense']);
        $this->assertEquals('-80.00', $sep15['net']);
        $this->assertEquals('negative', $sep15['status']);
        $this->assertEquals(1, $sep15['count']);
    }

    public function test_weekly_cash_movement_summary_groups_by_weeks(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $ref = Carbon::parse('2026-09-20');

        // Week 1 (Sep 3): $40
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '40.00',
            'transaction_date' => '2026-09-03',
        ]);

        // Week 3 (Sep 18): $90
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '90.00',
            'transaction_date' => '2026-09-18',
        ]);

        $service = app(FinancialCalculationService::class);
        $weekly = $service->getCurrentMonthWeeklySummary($student->id, $ref);

        $this->assertCount(5, $weekly['weeks']);

        $w1 = $weekly['weeks'][0];
        $this->assertEquals('40.00', $w1['expense']);
        $this->assertEquals(1, $w1['count']);

        $w2 = $weekly['weeks'][1];
        $this->assertEquals('0.00', $w2['expense']);
        $this->assertEquals(0, $w2['count']);

        $w3 = $weekly['weeks'][2];
        $this->assertEquals('90.00', $w3['expense']);
        $this->assertEquals(1, $w3['count']);
    }

    public function test_date_range_filtering_excludes_out_of_range_entries(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        // In range: Sep 10 ($100)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '100.00',
            'merchant' => 'InRangeMerchant',
            'transaction_date' => '2026-09-10',
        ]);

        // Out of range: Jan 2025 ($999.00)
        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '999.00',
            'merchant' => 'OutOfRangeMerchant',
            'transaction_date' => '2025-01-01',
        ]);

        Livewire::actingAs($student)
            ->test(MonthlyReports::class)
            ->set('dateFrom', '2026-09-01')
            ->set('dateTo', '2026-09-30')
            ->set('reportTab', 'ledger')
            ->assertSee('InRangeMerchant')
            ->assertSee('100.00')
            ->assertDontSee('OutOfRangeMerchant')
            ->assertDontSee('999.00');
    }

    public function test_category_and_type_filtering_works_simultaneously(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $transportCat = Category::where('name', 'Transport')->where('type', 'expense')->first();
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'merchant' => 'FoodVendorTarget',
            'amount' => '45.00',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $transportCat->id,
            'type' => 'expense',
            'merchant' => 'MetroTransitOther',
            'amount' => '30.00',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $allowanceCat->id,
            'type' => 'income',
            'merchant' => 'FamilyAllowanceOther',
            'amount' => '500.00',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        Livewire::actingAs($student)
            ->test(MonthlyReports::class)
            ->set('categoryFilter', (string) $foodCat->id)
            ->set('typeFilter', 'expense')
            ->set('reportTab', 'ledger')
            ->assertSee('FoodVendorTarget')
            ->assertDontSee('MetroTransitOther')
            ->assertDontSee('FamilyAllowanceOther');
    }

    public function test_empty_period_returns_clean_zero_summary(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(MonthlyReports::class)
            ->assertSee('$0.00')
            ->assertSee('No category expenses found');
    }

    public function test_student_multi_tenant_isolation_in_reports(): void
    {
        $studentA = User::factory()->create();
        $studentB = User::factory()->create();

        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        Transaction::factory()->create([
            'user_id' => $studentB->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'merchant' => 'StudentBPrivateMerchant',
            'amount' => '777.77',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        Livewire::actingAs($studentA)
            ->test(MonthlyReports::class)
            ->assertDontSee('StudentBPrivateMerchant')
            ->assertDontSee('777.77');
    }

    public function test_pdf_export_endpoint_returns_valid_download_or_preview(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'amount' => '120.00',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        // Test HTML preview mode
        $previewResp = $this->actingAs($student)->get('/reports/export/pdf?preview=1');
        $previewResp->assertStatus(200);
        $previewResp->assertSee('Campus Coin — Financial Statement');
        $previewResp->assertSee($student->name);
        $previewResp->assertSee('120.00');

        // Test PDF download mode
        $pdfResp = $this->actingAs($student)->get('/reports/export/pdf');
        $pdfResp->assertStatus(200);
        $pdfResp->assertHeader('content-type', 'application/pdf');
    }

    public function test_csv_export_endpoint_returns_streamed_csv(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'merchant' => 'CampusBakeryCsv',
            'amount' => '25.50',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        $csvResp = $this->actingAs($student)->get('/reports/export/csv');
        $csvResp->assertStatus(200);
        $this->assertStringContainsString('text/csv', $csvResp->headers->get('content-type'));

        $content = $csvResp->streamedContent();
        $this->assertStringContainsString('Campus Coin — Financial Statement', $content);
        $this->assertStringContainsString($student->name, $content);
        $this->assertStringContainsString('CampusBakeryCsv', $content);
        $this->assertStringContainsString('25.50', $content);
    }

    public function test_export_endpoints_prevent_cross_student_data_leakage(): void
    {
        $studentA = User::factory()->create();
        $studentB = User::factory()->create();

        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        Transaction::factory()->create([
            'user_id' => $studentB->id,
            'category_id' => $foodCat->id,
            'type' => 'expense',
            'merchant' => 'ClassifiedSecretMerchantB',
            'amount' => '888.88',
            'transaction_date' => Carbon::now()->toDateString(),
        ]);

        // Student A exports CSV
        $csvResp = $this->actingAs($studentA)->get('/reports/export/csv');
        $content = $csvResp->streamedContent();
        $this->assertStringNotContainsString('ClassifiedSecretMerchantB', $content);
        $this->assertStringNotContainsString('888.88', $content);

        // Student A exports preview HTML
        $htmlResp = $this->actingAs($studentA)->get('/reports/export/pdf?preview=1');
        $htmlResp->assertDontSee('ClassifiedSecretMerchantB');
        $htmlResp->assertDontSee('888.88');
    }
}
