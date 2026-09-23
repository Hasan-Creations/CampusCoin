<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the default system categories.
     */
    public function run(): void
    {
        $defaultCategories = [
            // Default Income Categories
            [
                'name' => 'Allowance',
                'type' => 'income',
                'icon' => 'wallet',
                'color' => '#059669',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Part-time Job',
                'type' => 'income',
                'icon' => 'activity',
                'color' => '#10B981',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Scholarship',
                'type' => 'income',
                'icon' => 'graduation-cap',
                'color' => '#D97706',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Gift',
                'type' => 'income',
                'icon' => 'target',
                'color' => '#047857',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Other Income',
                'type' => 'income',
                'icon' => 'plus',
                'color' => '#64748B',
                'is_default' => true,
                'user_id' => null,
            ],

            // Default Expense Categories
            [
                'name' => 'Food',
                'type' => 'expense',
                'icon' => 'pie-chart',
                'color' => '#E11D48',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Transport',
                'type' => 'expense',
                'icon' => 'trending-down',
                'color' => '#D97706',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Hostel/Rent',
                'type' => 'expense',
                'icon' => 'lock',
                'color' => '#6366F1',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Academics',
                'type' => 'expense',
                'icon' => 'graduation-cap',
                'color' => '#059669',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Subscriptions',
                'type' => 'expense',
                'icon' => 'calendar',
                'color' => '#8B5CF6',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Entertainment',
                'type' => 'expense',
                'icon' => 'sliders',
                'color' => '#EC4899',
                'is_default' => true,
                'user_id' => null,
            ],
            [
                'name' => 'Miscellaneous',
                'type' => 'expense',
                'icon' => 'tag',
                'color' => '#64748B',
                'is_default' => true,
                'user_id' => null,
            ],
        ];

        foreach ($defaultCategories as $category) {
            Category::firstOrCreate(
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'is_default' => true,
                    'user_id' => null,
                ],
                $category
            );
        }
    }
}
