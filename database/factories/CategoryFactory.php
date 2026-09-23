<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(2, true),
            'type' => fake()->randomElement(['income', 'expense']),
            'icon' => fake()->randomElement(['tag', 'wallet', 'activity', 'pie-chart', 'calendar', 'plus']),
            'color' => fake()->randomElement(['#059669', '#10B981', '#E11D48', '#D97706', '#6366F1']),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the category is a system default.
     */
    public function systemDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_default' => true,
        ]);
    }

    /**
     * Indicate that the category is an income category.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }

    /**
     * Indicate that the category is an expense category.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }
}
