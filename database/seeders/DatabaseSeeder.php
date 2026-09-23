<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Default Administrator
        User::firstOrCreate(
            ['email' => 'admin@campuscoin.edu'],
            [
                'name' => 'Campus Coin Admin',
                'password' => bcrypt('AdminSecure123!'),
                'role' => 'admin',
                'status' => 'active',
                'academic_year' => null,
                'monthly_allowance' => 0.00,
                'savings_goal' => 0.00,
                'email_verified_at' => now(),
            ]
        );

        // Default Student User for evaluation & testing
        $student = User::firstOrCreate(
            ['email' => 'alex.rivera@campus.edu'],
            [
                'name' => 'Alex Rivera',
                'password' => bcrypt('StudentSecure123!'),
                'role' => 'student',
                'status' => 'active',
                'academic_year' => 'Junior',
                'monthly_allowance' => 1200.00,
                'savings_goal' => 300.00,
                'email_verified_at' => now(),
            ]
        );

        // Second Student for Multi-Tenant Isolation Testing
        User::firstOrCreate(
            ['email' => 'maria.santos@campus.edu'],
            [
                'name' => 'Maria Santos',
                'password' => bcrypt('StudentSecure123!'),
                'role' => 'student',
                'status' => 'active',
                'academic_year' => 'Sophomore',
                'monthly_allowance' => 950.00,
                'savings_goal' => 200.00,
                'email_verified_at' => now(),
            ]
        );

        // Seed System Default Categories
        $this->call(CategorySeeder::class);

        // Seed initial sample transactions for demonstration & testing
        $allowanceCat = Category::where('name', 'Allowance')->where('type', 'income')->first();
        $foodCat = Category::where('name', 'Food')->where('type', 'expense')->first();
        $academicsCat = Category::where('name', 'Academics')->where('type', 'expense')->first();
        $transportCat = Category::where('name', 'Transport')->where('type', 'expense')->first();

        if ($allowanceCat && $foodCat && $academicsCat && $transportCat) {
            Transaction::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'merchant' => 'Family Allowance Transfer',
                    'transaction_date' => now()->startOfMonth()->toDateString(),
                ],
                [
                    'category_id' => $allowanceCat->id,
                    'type' => 'income',
                    'amount' => 1200.00,
                    'description' => 'Monthly family living allowance baseline',
                    'payment_method' => 'bank_transfer',
                    'is_recurring' => true,
                ]
            );

            Transaction::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'merchant' => 'Campus Dining Hall',
                    'transaction_date' => now()->subDays(2)->toDateString(),
                ],
                [
                    'category_id' => $foodCat->id,
                    'type' => 'expense',
                    'amount' => 24.50,
                    'description' => 'Lunch & coffee with study group',
                    'payment_method' => 'card',
                    'is_recurring' => false,
                ]
            );

            Transaction::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'merchant' => 'University Bookstore',
                    'transaction_date' => now()->subDays(4)->toDateString(),
                ],
                [
                    'category_id' => $academicsCat->id,
                    'type' => 'expense',
                    'amount' => 68.00,
                    'description' => 'Algorithms & Data Structures textbook',
                    'payment_method' => 'card',
                    'is_recurring' => false,
                ]
            );

            Transaction::firstOrCreate(
                [
                    'user_id' => $student->id,
                    'merchant' => 'Campus Metro Shuttle',
                    'transaction_date' => now()->subDays(6)->toDateString(),
                ],
                [
                    'category_id' => $transportCat->id,
                    'type' => 'expense',
                    'amount' => 15.00,
                    'description' => 'Weekly campus transit card reload',
                    'payment_method' => 'digital_wallet',
                    'is_recurring' => false,
                ]
            );
        }
    }
}
