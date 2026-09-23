<?php

namespace Tests\Feature;

use App\Livewire\Student\TransactionList;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TransactionLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_transactions_screen_can_be_rendered(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get('/transactions');

        $response->assertStatus(200);
        $response->assertSee('Transaction History');
        $response->assertSee('Add Transaction');
        $response->assertSee('Export CSV');
    }

    public function test_empty_state_rendered_when_no_transactions(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->assertSee('NO TRANSACTIONS RECORDED YET')
            ->assertSee('Start tracking your campus expenses to unlock insights.')
            ->assertSee('Add First Transaction');
    }

    public function test_student_can_create_expense_transaction_with_exact_decimal(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->set('type', 'expense')
            ->set('amount', '18.75')
            ->set('merchant', 'Campus Coffee Cart')
            ->set('category_id', $foodCat->id)
            ->set('transaction_date', '2026-09-24')
            ->set('payment_method', 'card')
            ->set('description', 'Latte and blueberry muffin before physics lecture')
            ->set('is_recurring', false)
            ->call('saveTransaction')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Campus Coffee Cart',
            'amount' => '18.75',
            'type' => 'expense',
            'category_id' => $foodCat->id,
        ]);
    }

    public function test_student_can_create_income_transaction(): void
    {
        $student = User::factory()->create();
        $jobCat = Category::where('name', 'Part-time Job')->where('type', 'income')->first();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->set('type', 'income')
            ->set('amount', '320.00')
            ->set('merchant', 'Library Front Desk Stipend')
            ->set('category_id', $jobCat->id)
            ->set('transaction_date', '2026-09-24')
            ->set('payment_method', 'bank_transfer')
            ->set('is_recurring', true)
            ->call('saveTransaction')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $student->id,
            'merchant' => 'Library Front Desk Stipend',
            'amount' => '320.00',
            'type' => 'income',
            'is_recurring' => true,
        ]);
    }

    public function test_student_can_edit_own_transaction(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->first();

        $transaction = Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'merchant' => 'Old Merchant',
            'amount' => '25.00',
            'type' => 'expense',
        ]);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openEditModal', $transaction->id)
            ->set('merchant', 'Updated Canteen Kitchen')
            ->set('amount', '29.50')
            ->call('saveTransaction')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'merchant' => 'Updated Canteen Kitchen',
            'amount' => '29.50',
        ]);
    }

    public function test_student_can_delete_own_transaction(): void
    {
        $student = User::factory()->create();
        $transaction = Transaction::factory()->create([
            'user_id' => $student->id,
            'amount' => '10.00',
        ]);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('deleteTransaction', $transaction->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_student_cannot_edit_or_delete_another_students_transaction(): void
    {
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();

        $otherTransaction = Transaction::factory()->create([
            'user_id' => $student2->id,
            'merchant' => 'Student 2 Private Bill',
            'amount' => '100.00',
        ]);

        // Attempt edit by student 1
        Livewire::actingAs($student1)
            ->test(TransactionList::class)
            ->call('openEditModal', $otherTransaction->id)
            ->assertSet('editingId', null)
            ->assertSee('Transaction not found or unauthorized');

        // Attempt delete by student 1
        Livewire::actingAs($student1)
            ->test(TransactionList::class)
            ->call('deleteTransaction', $otherTransaction->id)
            ->assertSee('could not be found or unauthorized');

        // Verify still in database
        $this->assertDatabaseHas('transactions', [
            'id' => $otherTransaction->id,
            'merchant' => 'Student 2 Private Bill',
        ]);
    }

    public function test_transactions_can_be_filtered_by_type(): void
    {
        $student = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $student->id,
            'merchant' => 'Coffee Expense',
            'type' => 'expense',
        ]);

        Transaction::factory()->create([
            'user_id' => $student->id,
            'merchant' => 'Tutor Income',
            'type' => 'income',
        ]);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->set('typeFilter', 'expense')
            ->assertSee('Coffee Expense')
            ->assertDontSee('Tutor Income')
            ->set('typeFilter', 'income')
            ->assertSee('Tutor Income')
            ->assertDontSee('Coffee Expense');
    }

    public function test_transactions_can_be_searched_by_merchant(): void
    {
        $student = User::factory()->create();

        Transaction::factory()->create([
            'user_id' => $student->id,
            'merchant' => 'Campus Gym Store',
        ]);

        Transaction::factory()->create([
            'user_id' => $student->id,
            'merchant' => 'University Diner',
        ]);

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->set('search', 'Gym')
            ->assertSee('Campus Gym Store')
            ->assertDontSee('University Diner');
    }

    public function test_category_must_match_transaction_nature(): void
    {
        $student = User::factory()->create();
        $incomeCat = Category::where('name', 'Allowance')->where('type', 'income')->first();

        // Try to assign Allowance (income) to an expense transaction
        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->set('type', 'expense')
            ->set('amount', '20.00')
            ->set('merchant', 'Illegal Match')
            ->set('category_id', $incomeCat->id)
            ->set('transaction_date', '2026-09-24')
            ->set('payment_method', 'card')
            ->call('saveTransaction')
            ->assertHasErrors(['category_id']);
    }

    public function test_csv_export_returns_streamed_file_with_valid_data(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->first();

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $foodCat->id,
            'merchant' => 'Exportable Dining Expense',
            'amount' => '42.50',
            'type' => 'expense',
        ]);

        $response = Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('exportCsv');

        $response->assertFileDownloaded();
    }

    public function test_quick_add_modal_does_not_prefill_category_and_requires_explicit_selection(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->assertSet('category_id', null)
            ->set('amount', '15.00')
            ->set('merchant', 'Campus Bookstore')
            ->call('saveTransaction')
            ->assertHasErrors(['category_id' => 'required']);
    }

    public function test_quick_add_modal_renders_smart_dynamic_labels_and_placeholders(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->assertSet('type', 'expense')
            ->assertSee('Where did you spend it?')
            ->assertSee('e.g. Canteen, Bookstore')
            ->set('type', 'income')
            ->assertSee('Where is this from?')
            ->assertSee('e.g. Monthly Allowance, Freelance');
    }

    public function test_quick_add_modal_records_transaction_with_streamlined_defaults(): void
    {
        $student = User::factory()->create();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();

        // 5-second Quick-Add flow: only amount, category chip, and merchant needed
        Livewire::actingAs($student)
            ->test(TransactionList::class)
            ->call('openCreateModal')
            ->set('amount', '9.50')
            ->set('category_id', $foodCat->id)
            ->set('merchant', 'Quick Bento Box')
            ->call('saveTransaction')
            ->assertHasNoErrors();

        $transaction = Transaction::where('merchant', 'Quick Bento Box')->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('9.50', (string) $transaction->amount);
        $this->assertEquals('expense', $transaction->type);
        $this->assertEquals($foodCat->id, $transaction->category_id);
        $this->assertEquals('card', $transaction->payment_method);
        $this->assertEquals(date('Y-m-d'), $transaction->transaction_date->format('Y-m-d'));
        $this->assertFalse((bool) $transaction->is_recurring);
    }
}
