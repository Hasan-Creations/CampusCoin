<?php

namespace Tests\Feature;

use App\Livewire\Student\TransactionList;
use App\Models\Category;
use App\Models\CategoryLearning;
use App\Models\User;
use App\Services\AiCategorizationService;
use App\Services\Categorization\HeuristicCategorizationProvider;
use App\Services\Categorization\OpenAiCategorizationProvider;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AiCategorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_heuristic_provider_suggests_appropriate_student_categories(): void
    {
        $student = User::factory()->create();
        $categories = Category::forUser($student->id)->get();
        $provider = app(HeuristicCategorizationProvider::class);

        // Food & Dining
        $foodSuggestion = $provider->suggestCategory('Starbucks Frappuccino', $categories, $student);
        $this->assertNotNull($foodSuggestion);
        $this->assertEquals('Food', $foodSuggestion->categoryName);
        $this->assertGreaterThanOrEqual(0.75, $foodSuggestion->confidence);
        $this->assertEquals('rules', $foodSuggestion->source);

        // Academics & Books
        $booksSuggestion = $provider->suggestCategory('University Bookstore Textbooks', $categories, $student);
        $this->assertNotNull($booksSuggestion);
        $this->assertEquals('Academics', $booksSuggestion->categoryName);

        // Transportation
        $transportSuggestion = $provider->suggestCategory('Uber Ride to Campus', $categories, $student);
        $this->assertNotNull($transportSuggestion);
        $this->assertEquals('Transport', $transportSuggestion->categoryName);

        // Subscriptions
        $subSuggestion = $provider->suggestCategory('Spotify Premium Student', $categories, $student);
        $this->assertNotNull($subSuggestion);
        $this->assertEquals('Subscriptions', $subSuggestion->categoryName);

        // Hostel / Housing
        $housingSuggestion = $provider->suggestCategory('Dormitory Room Rent', $categories, $student);
        $this->assertNotNull($housingSuggestion);
        $this->assertEquals('Hostel/Rent', $housingSuggestion->categoryName);

        // Unknown / gibberish should return null
        $unknown = $provider->suggestCategory('XyZqWk 999888', $categories, $student);
        $this->assertNull($unknown);
    }

    public function test_openai_provider_returns_ai_suggestion_when_api_succeeds(): void
    {
        $student = User::factory()->create();
        $categories = Category::forUser($student->id)->get();
        $foodCat = $categories->firstWhere('name', 'Food');

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'category_id' => $foodCat->id,
                                'confidence' => 0.95,
                                'explanation' => 'Campus Canteen is a food dining venue.',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $heuristic = app(HeuristicCategorizationProvider::class);
        $provider = new OpenAiCategorizationProvider($heuristic, apiKey: 'test-fake-key');

        $suggestion = $provider->suggestCategory('Campus Canteen Special', $categories, $student);

        $this->assertNotNull($suggestion);
        $this->assertEquals($foodCat->id, $suggestion->categoryId);
        $this->assertEquals('Food', $suggestion->categoryName);
        $this->assertEquals(0.95, $suggestion->confidence);
        $this->assertEquals('ai', $suggestion->source);
        $this->assertEquals('high', $suggestion->confidenceLevel);
    }

    public function test_openai_provider_falls_back_to_heuristic_on_network_failure_or_timeout(): void
    {
        $student = User::factory()->create();
        $categories = Category::forUser($student->id)->get();

        // Simulate network failure / 500 error
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response(['error' => 'Rate limit exceeded'], 429),
        ]);

        $heuristic = app(HeuristicCategorizationProvider::class);
        $provider = new OpenAiCategorizationProvider($heuristic, apiKey: 'test-fake-key');

        // Description contains "Uber", so heuristic can handle it safely
        $suggestion = $provider->suggestCategory('Uber Trip', $categories, $student);

        $this->assertNotNull($suggestion);
        $this->assertEquals('Transport', $suggestion->categoryName);
        $this->assertEquals('rules', $suggestion->source);
    }

    public function test_openai_provider_falls_back_when_no_api_key_configured(): void
    {
        $student = User::factory()->create();
        $categories = Category::forUser($student->id)->get();

        $heuristic = app(HeuristicCategorizationProvider::class);
        $provider = new OpenAiCategorizationProvider($heuristic, apiKey: null);

        $suggestion = $provider->suggestCategory('Netflix Subscription', $categories, $student);

        $this->assertNotNull($suggestion);
        $this->assertEquals('Subscriptions', $suggestion->categoryName);
        $this->assertEquals('rules', $suggestion->source);
    }

    public function test_openai_provider_rejects_hallucinated_category_and_falls_back(): void
    {
        $student = User::factory()->create();
        $categories = Category::forUser($student->id)->get();

        // Model returns an invalid category ID not in availableCategories
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => json_encode([
                                'category_id' => 99999,
                                'confidence' => 0.99,
                                'explanation' => 'Fake invented category ID',
                            ]),
                        ],
                    ],
                ],
            ], 200),
        ]);

        $heuristic = app(HeuristicCategorizationProvider::class);
        $provider = new OpenAiCategorizationProvider($heuristic, apiKey: 'test-fake-key');

        $suggestion = $provider->suggestCategory('Chipotle Mexican Grill', $categories, $student);

        // Fallback to heuristic detects "Chipotle" -> Food
        $this->assertNotNull($suggestion);
        $this->assertEquals('Food', $suggestion->categoryName);
        $this->assertEquals('rules', $suggestion->source);
    }

    public function test_student_learned_correction_takes_precedence_over_ai_and_heuristic(): void
    {
        $student = User::factory()->create();
        $categories = Category::forUser($student->id)->get();
        $miscCat = $categories->firstWhere('name', 'Miscellaneous');
        $this->assertNotNull($miscCat);

        /** @var AiCategorizationService $service */
        $service = app(AiCategorizationService::class);

        // Student explicitly mapped "Corner Market" to "Miscellaneous" (even though market usually maps to Food)
        $service->recordCorrection($student->id, 'Corner Market', $miscCat->id);

        $suggestion = $service->suggestCategory('Corner Market', $categories, $student);

        $this->assertNotNull($suggestion);
        $this->assertEquals($miscCat->id, $suggestion->categoryId);
        $this->assertEquals('Miscellaneous', $suggestion->categoryName);
        $this->assertEquals('learned', $suggestion->source);
        $this->assertEquals('high', $suggestion->confidenceLevel);
        $this->assertStringContainsString('Learned from your previous choice', $suggestion->explanation);
    }

    public function test_learned_corrections_are_strictly_isolated_between_students(): void
    {
        $studentA = User::factory()->create();
        $studentB = User::factory()->create();

        $categoriesA = Category::forUser($studentA->id)->get();
        $categoriesB = Category::forUser($studentB->id)->get();

        $entertainmentCatA = $categoriesA->firstWhere('name', 'Entertainment');

        /** @var AiCategorizationService $service */
        $service = app(AiCategorizationService::class);

        // Student A maps custom phrase "Secret Underground Club" to Entertainment
        $service->recordCorrection($studentA->id, 'Secret Underground Club', $entertainmentCatA->id);

        // Student A gets learned suggestion
        $suggestionA = $service->suggestCategory('Secret Underground Club', $categoriesA, $studentA);
        $this->assertNotNull($suggestionA);
        $this->assertEquals('Entertainment', $suggestionA->categoryName);
        $this->assertEquals('learned', $suggestionA->source);

        // Student B requests suggestion for same phrase: must NOT receive Student A's mapping!
        $suggestionB = $service->suggestCategory('Secret Underground Club', $categoriesB, $studentB);
        // Should be null or fall back to general rules, never 'learned' for Student B
        if ($suggestionB) {
            $this->assertNotEquals('learned', $suggestionB->source);
        } else {
            $this->assertNull($suggestionB);
        }

        // Verify database isolation
        $this->assertDatabaseHas('category_learnings', [
            'user_id' => $studentA->id,
            'keyword' => 'secret underground club',
        ]);
        $this->assertDatabaseMissing('category_learnings', [
            'user_id' => $studentB->id,
            'keyword' => 'secret underground club',
        ]);
    }

    public function test_repeated_corrections_increment_usage_count_and_boost_confidence(): void
    {
        $student = User::factory()->create();
        $categories = Category::forUser($student->id)->get();
        $foodCat = $categories->firstWhere('name', 'Food');

        /** @var AiCategorizationService $service */
        $service = app(AiCategorizationService::class);

        $service->recordCorrection($student->id, 'Boba Tea Shop', $foodCat->id);
        $firstLearning = CategoryLearning::where('user_id', $student->id)->where('keyword', 'boba tea shop')->first();
        $this->assertEquals(1, $firstLearning->usage_count);

        $firstSuggestion = $service->suggestCategory('Boba Tea Shop', $categories, $student);
        $initialConfidence = $firstSuggestion->confidence;

        // Second correction with same keyword
        $service->recordCorrection($student->id, 'Boba Tea Shop', $foodCat->id);
        $updatedLearning = CategoryLearning::where('user_id', $student->id)->where('keyword', 'boba tea shop')->first();
        $this->assertEquals(2, $updatedLearning->usage_count);

        $secondSuggestion = $service->suggestCategory('Boba Tea Shop', $categories, $student);
        $this->assertGreaterThan($initialConfidence, $secondSuggestion->confidence);
    }

    public function test_livewire_transaction_entry_shows_advisory_suggestion_and_accepts_it(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->set('type', 'expense')
            ->set('merchant', 'Starbucks Cafe')
            ->set('amount', '6.50')
            ->call('requestCategorySuggestion')
            ->assertSet('activeSuggestion.categoryName', 'Food')
            ->assertSet('category_id', null) // Not yet applied automatically
            ->call('acceptSuggestion')
            ->assertSet('category_id', $foodCat->id)
            ->assertSet('suggestionAccepted', true)
            ->call('saveTransaction');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Starbucks Cafe',
            'category_id' => $foodCat->id,
            'ai_suggested' => true,
        ]);
    }

    public function test_manual_category_selection_overrides_ai_suggestion(): void
    {
        $student = User::factory()->create();
        $entertainmentCat = Category::where('name', 'Entertainment')->where('type', 'expense')->first();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->set('type', 'expense')
            ->set('merchant', 'Campus Cafe') // Suggests Food
            ->set('amount', '12.00')
            ->call('requestCategorySuggestion')
            ->assertSet('activeSuggestion.categoryName', 'Food')
            // Student manually clicks Entertainment category instead
            ->call('selectCategory', $entertainmentCat->id)
            ->assertSet('category_id', $entertainmentCat->id)
            ->assertSet('manualCategorySelected', true)
            ->call('saveTransaction');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Campus Cafe',
            'category_id' => $entertainmentCat->id,
            'ai_suggested' => false,
        ]);

        // Student manual choice is remembered as a learned mapping
        $this->assertDatabaseHas('category_learnings', [
            'user_id' => $student->id,
            'keyword' => 'campus cafe',
            'category_id' => $entertainmentCat->id,
        ]);
    }

    public function test_transaction_entry_works_normally_when_ai_has_no_suggestion(): void
    {
        $student = User::factory()->create();
        $miscCat = Category::where('name', 'Miscellaneous')->where('type', 'expense')->first();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->set('type', 'expense')
            ->set('merchant', 'Custom Obscure Vendor 9876')
            ->set('amount', '25.00')
            ->call('requestCategorySuggestion')
            ->assertSet('activeSuggestion', null)
            ->call('selectCategory', $miscCat->id)
            ->call('saveTransaction');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Custom Obscure Vendor 9876',
            'category_id' => $miscCat->id,
            'ai_suggested' => false,
        ]);
    }

    public function test_csv_upload_parses_batch_and_generates_ai_suggestions(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $booksCat = Category::where('name', 'Academics')->where('type', 'expense')->first();

        $csvContent = implode("\n", [
            'Date,Description,Amount,Type',
            '2026-09-20,Starbucks Coffee,5.75,expense',
            '2026-09-21,Campus Bookstore Supplies,42.50,expense',
            '2026-09-22,Uber Ride Home,14.20,expense',
        ]);

        $file = UploadedFile::fake()->createWithContent('bank_statement.csv', $csvContent);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openImportModal')
            ->set('csvFile', $file)
            ->call('processCsvUpload')
            ->assertSet('importStepReview', true)
            ->assertCount('importRows', 3);
    }

    public function test_csv_import_confirmation_creates_transactions_and_learnings(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $transportCat = Category::where('name', 'Transport')->where('type', 'expense')->first();

        $csvContent = implode("\n", [
            'Date,Description,Amount,Type',
            '2026-09-20,Starbucks Coffee,6.50,expense',
            '2026-09-21,Uber Campus Shuttle,15.00,expense',
        ]);

        $file = UploadedFile::fake()->createWithContent('transactions.csv', $csvContent);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openImportModal')
            ->set('csvFile', $file)
            ->call('processCsvUpload')
            ->assertSet('importStepReview', true)
            ->call('confirmImport')
            ->assertSet('showImportModal', false)
            ->assertSet('importStepReview', false);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Starbucks Coffee',
            'category_id' => $foodCat->id,
            'ai_suggested' => true,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Uber Campus Shuttle',
            'category_id' => $transportCat->id,
            'ai_suggested' => true,
        ]);

        $this->assertDatabaseHas('category_learnings', [
            'user_id' => $student->id,
            'keyword' => 'starbucks coffee',
            'category_id' => $foodCat->id,
        ]);
    }

    public function test_csv_batch_bounded_to_50_rows(): void
    {
        $student = User::factory()->create();

        $lines = ['Date,Description,Amount,Type'];
        for ($i = 1; $i <= 60; $i++) {
            $lines[] = "2026-09-20,Transaction {$i},10.00,expense";
        }
        $csvContent = implode("\n", $lines);

        $file = UploadedFile::fake()->createWithContent('bulk.csv', $csvContent);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openImportModal')
            ->set('csvFile', $file)
            ->call('processCsvUpload')
            ->assertSet('importStepReview', true)
            ->assertCount('importRows', 50); // Bounded strictly to 50 rows
    }

    public function test_csv_import_handles_invalid_rows_safely(): void
    {
        $student = User::factory()->create();

        $csvContent = implode("\n", [
            'Date,Description,Amount,Type',
            ',Missing Date Transaction,10.00,expense',
            '2026-09-20,,15.00,expense',
            '2026-09-21,Negative Amount,-5.00,expense',
            '2026-09-22,Valid Starbucks,7.50,expense',
        ]);

        $file = UploadedFile::fake()->createWithContent('invalid_rows.csv', $csvContent);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openImportModal')
            ->set('csvFile', $file)
            ->call('processCsvUpload')
            ->assertSet('importStepReview', true)
            ->call('confirmImport');

        // Only the valid row should be imported
        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Valid Starbucks',
        ]);

        $this->assertDatabaseMissing('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Negative Amount',
        ]);
    }
}
