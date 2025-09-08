<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use App\Models\UserSession;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@aptis.local',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create test students
        $students = [
            ['name' => 'Nguyen Van A', 'email' => 'student1@aptis.local'],
            ['name' => 'Tran Thi B', 'email' => 'student2@aptis.local'],
            ['name' => 'Le Van C', 'email' => 'student3@aptis.local'],
        ];

        foreach ($students as $student) {
            $user = User::create([
                'name' => $student['name'],
                'email' => $student['email'],
                'password' => Hash::make('123456'),
                'role' => 'student',
                'is_active' => true,
                'access_expires_at' => now()->addDays(30),
            ]);

            // Tạo mẫu các session thiết bị cho học viên
            if ($student['email'] === 'student1@aptis.local') {
                \App\Models\UserSession::create([
                    'user_id' => $user->id,
                    'device_fingerprint' => 'device_' . md5('laptop_' . $user->id),
                    'device_name' => 'Desktop Device - Chrome',
                    'ip_address' => '192.168.1.101',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'is_active' => true,
                    'last_active_at' => now()->subMinutes(15),
                ]);
            }
        }

        $this->call([\Database\Seeders\ReadingSetSeeder::class]);
    }
}
