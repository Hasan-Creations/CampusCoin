<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('app.admin.password');

        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException('Set ADMIN_PASSWORD before seeding the administrator.');
        }

        $admin = User::updateOrCreate(
            ['email' => config('app.admin.email')],
            [
                'name' => config('app.admin.name'),
                'password' => Hash::make($password),
                'status' => 'active',
            ],
        );

        $admin->forceFill([
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();
    }
}
