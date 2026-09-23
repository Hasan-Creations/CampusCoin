<?php

namespace Database\Seeders;

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
        User::firstOrCreate(
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
    }
}
