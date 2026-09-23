<?php

namespace Tests\Feature;

use App\Livewire\Student\CategoryManager;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_categories_screen_can_be_rendered(): void
    {
        $student = User::factory()->create();

        $response = $this->actingAs($student)->get('/categories');

        $response->assertStatus(200);
        $response->assertSee('Category Management');
        $response->assertSee('Food');
        $response->assertSee('Allowance');
        $response->assertSee('Academics');
    }

    public function test_student_sees_default_categories_and_personal_categories(): void
    {
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();

        // Student 1 personal category
        $cat1 = Category::factory()->create([
            'user_id' => $student1->id,
            'name' => 'Campus Gym Pass',
            'type' => 'expense',
            'is_default' => false,
        ]);

        // Student 2 personal category
        $cat2 = Category::factory()->create([
            'user_id' => $student2->id,
            'name' => 'Freelance Design',
            'type' => 'income',
            'is_default' => false,
        ]);

        Livewire::actingAs($student1)
            ->test(CategoryManager::class)
            ->assertSee('Food') // default
            ->assertSee('Allowance') // default
            ->assertSee('Campus Gym Pass') // student 1 own
            ->assertDontSee('Freelance Design'); // student 2 isolated
    }

    public function test_student_can_create_personal_category(): void
    {
        $student = User::factory()->create();

        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->set('name', 'Canteen Snacks')
            ->set('type', 'expense')
            ->set('icon', 'tag')
            ->set('color', '#E11D48')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'user_id' => $student->id,
            'name' => 'Canteen Snacks',
            'type' => 'expense',
            'is_default' => false,
        ]);
    }

    public function test_student_can_edit_own_category(): void
    {
        $student = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $student->id,
            'name' => 'Old Title',
            'type' => 'expense',
        ]);

        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->call('openEditModal', $category->id)
            ->set('name', 'Updated Books & Notes')
            ->call('saveCategory')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'user_id' => $student->id,
            'name' => 'Updated Books & Notes',
        ]);
    }

    public function test_student_cannot_edit_global_default_category(): void
    {
        $student = User::factory()->create();
        $defaultCat = Category::where('is_default', true)->first();

        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->call('openEditModal', $defaultCat->id)
            ->assertSet('editingId', null)
            ->assertSee('Access denied');
    }

    public function test_student_cannot_edit_another_students_category(): void
    {
        $student1 = User::factory()->create();
        $student2 = User::factory()->create();

        $otherCategory = Category::factory()->create([
            'user_id' => $student2->id,
            'name' => 'Student 2 Private Category',
        ]);

        Livewire::actingAs($student1)
            ->test(CategoryManager::class)
            ->call('openEditModal', $otherCategory->id)
            ->assertSet('editingId', null)
            ->assertSee('Access denied');
    }

    public function test_student_can_delete_own_category_without_transactions(): void
    {
        $student = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $student->id,
            'name' => 'Temporary Category',
        ]);

        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->call('deleteCategory', $category->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_safe_handling_prevents_deleting_category_with_transactions(): void
    {
        $student = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $student->id,
            'name' => 'Used Category',
        ]);

        Transaction::factory()->create([
            'user_id' => $student->id,
            'category_id' => $category->id,
            'amount' => 50.00,
        ]);

        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->call('deleteCategory', $category->id)
            ->assertSee('Cannot delete')
            ->assertSee('transaction(s) assigned to it');

        // Verify still in database
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_student_cannot_delete_system_default_category(): void
    {
        $student = User::factory()->create();
        $defaultCat = Category::where('is_default', true)->first();

        Livewire::actingAs($student)
            ->test(CategoryManager::class)
            ->call('deleteCategory', $defaultCat->id)
            ->assertSee('Cannot delete this category');

        $this->assertDatabaseHas('categories', [
            'id' => $defaultCat->id,
        ]);
    }
}
