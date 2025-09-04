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

        // Create Reading Part 1 Quiz
        $readingPart1Quiz = Quiz::create([
            'title' => 'Reading Part 1 - Work & Office',
            'description' => 'Practice reading Part 1 with work-related topics',
            'skill' => 'reading',
            'part' => 1,
            'is_published' => true,
            'duration_minutes' => 15,
            'show_explanation' => true,
        ]);

        // Create questions for Reading Part 1
        $passage = "Dear John,\n\n1. I hope you are ........ (well/good/fine) today.\n2. We need to ........ (discuss/talk/speak) the project.\n3. Can you ........ (send/give/provide) me the report?\n4. The meeting will be ........ (at/in/on) 3 PM.\n5. Please ........ (bring/take/carry) your laptop.";
        
        $questions = [
            [
                'stem' => 'I hope you are ______ today.',
                'options' => ['well', 'good', 'fine'],
                'correct' => 0
            ],
            [
                'stem' => 'We need to ______ the project.',
                'options' => ['discuss', 'talk', 'speak'],
                'correct' => 0
            ],
            [
                'stem' => 'Can you ______ me the report?',
                'options' => ['send', 'give', 'provide'],
                'correct' => 0
            ],
            [
                'stem' => 'The meeting will be ______ 3 PM.',
                'options' => ['at', 'in', 'on'],
                'correct' => 0
            ],
            [
                'stem' => 'Please ______ your laptop.',
                'options' => ['bring', 'take', 'carry'],
                'correct' => 0
            ],
        ];

        foreach ($questions as $index => $questionData) {
            $question = Question::create([
                'quiz_id' => $readingPart1Quiz->id,
                'stem' => $questionData['stem'],
                'explanation' => 'This is the correct answer for this context.',
                'skill' => 'reading',
                'part' => 1,
                'type' => 'single_choice',
                'order' => $index + 1,
                'metadata' => ['passage' => $passage]
            ]);

            // Create options
            foreach ($questionData['options'] as $optIndex => $optText) {
                Option::create([
                    'question_id' => $question->id,
                    'label' => chr(65 + $optIndex), // A, B, C
                    'content' => $optText,
                    'is_correct' => $optIndex === $questionData['correct'],
                    'order' => $optIndex + 1,
                ]);
            }
        }

        echo "✅ Database seeded successfully!\n";
        echo "👤 Admin: admin@aptis.local / 123456\n";
        echo "👥 Students: student1@aptis.local / 123456\n";
        echo "📚 1 Reading Part 1 Quiz with 5 questions created\n";
    }
}
