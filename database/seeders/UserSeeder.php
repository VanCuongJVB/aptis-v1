<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456'),
                'is_admin' => true,
                'is_active' => true,
                'access_starts_at' => $now,
                'access_ends_at' => (clone $now)->addDays(30),
                'email_verified_at' => $now,
            ]
        );

        User::updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student',
                'password' => Hash::make('123456'),
                'is_admin' => false,
                'is_active' => true,
                'access_starts_at' => $now,
                'access_ends_at' => (clone $now)->addDays(30),
                'email_verified_at' => $now,
            ]
        );
    }
}
