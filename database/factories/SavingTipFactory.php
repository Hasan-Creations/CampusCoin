<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\SavingTip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingTip>
 */
class SavingTipFactory extends Factory
{
    protected $model = SavingTip::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'rule_key' => 'category_above_average',
            'category_id' => Category::factory(),
            'title' => 'Spending above 3-month average',
            'message' => 'Your spending in this category has exceeded historical averages.',
            'suggestion' => 'Pacing your spending back to typical levels can yield significant savings.',
            'trigger_data' => [
                'current_spending' => '120.00',
                'historical_average' => '80.00',
                'delta' => '40.00',
            ],
            'estimated_savings' => '40.00',
            'status' => 'active',
            'dismissed_at' => null,
            'pinned_at' => null,
        ];
    }

    /**
     * Indicate that the tip is pinned.
     */
    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pinned',
            'pinned_at' => now(),
            'dismissed_at' => null,
        ]);
    }

    /**
     * Indicate that the tip is dismissed.
     */
    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dismissed',
            'dismissed_at' => now(),
            'pinned_at' => null,
        ]);
    }
}
