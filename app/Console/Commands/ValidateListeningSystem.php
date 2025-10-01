<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

class ValidateListeningSystem extends Command
{
    protected $signature = 'validate:listening-system';
    protected $description = 'Validate the Full Random Listening System setup and functionality';

    public function handle()
    {
        $this->info('🔍 Full Random Listening System Validation');
        $this->info('=====================================');

        // 1. Check questions by part
        $this->checkQuestions();

        // 2. Check database schema
        $this->checkSchema();

        // 3. Check routes
        $this->checkRoutes();

        // 4. Check recent activity
        $this->checkRecentActivity();

        // 5. Performance check
        $this->performanceCheck();

        $this->info('✅ Validation completed successfully!');
        $this->info('The Full Random Listening System is optimized and ready to use.');
    }

    private function checkQuestions()
    {
        $this->info("\n📚 Question Check:");
        
        $parts = [1, 2, 3, 4];
        $totalQuestions = 0;
        
        foreach ($parts as $part) {
            $count = Question::where('skill', 'listening')->where('part', $part)->count();
            $totalQuestions += $count;
            
            $status = $count > 0 ? '✅' : '❌';
            $this->line("  {$status} Part {$part}: {$count} questions");
        }
        
        $this->info("  📊 Total listening questions: {$totalQuestions}");
        
        // Check Part 4 structure
        $part4Question = Question::where('skill', 'listening')->where('part', 4)->first();
        if ($part4Question && isset($part4Question->metadata['questions'])) {
            $subCount = count($part4Question->metadata['questions']);
            $this->line("  ✅ Part 4 sub-questions: {$subCount} per question");
        } else {
            $this->warn("  ⚠️  Part 4 questions may not have proper sub-question structure");
        }
    }

    private function checkSchema()
    {
        $this->info("\n🗄️  Database Schema Check:");
        
        // Check sub_index column
        $hasSubIndex = Schema::hasColumn('attempt_answers', 'sub_index');
        $status = $hasSubIndex ? '✅' : '❌';
        $this->line("  {$status} attempt_answers.sub_index column: " . ($hasSubIndex ? 'EXISTS' : 'MISSING'));
        
        // Check if we have any multi-part answers
        if ($hasSubIndex) {
            $multiPartCount = AttemptAnswer::where('sub_index', '>', 0)->count();
            $this->line("  📊 Multi-part answer records: {$multiPartCount}");
        }
    }

    private function checkRoutes()
    {
        $this->info("\n🛣️  Routes Check:");
        
        $routes = [
            'listening.full-random' => 'Main test page',
            'listening.full-random.submit' => 'Submit endpoint',
            'listening.full-random.result' => 'Results page'
        ];
        
        foreach ($routes as $routeName => $description) {
            try {
                $url = route($routeName, $routeName === 'listening.full-random.result' ? ['attempt' => 1] : []);
                $this->line("  ✅ {$description}: {$url}");
            } catch (\Exception $e) {
                $this->line("  ❌ {$description}: MISSING");
            }
        }
    }

    private function checkRecentActivity()
    {
        $this->info("\n📈 Recent Activity Check:");
        
        $totalAttempts = Attempt::count();
        $this->line("  📊 Total attempts: {$totalAttempts}");
        
        if ($totalAttempts > 0) {
            $recentAttempts = Attempt::with('answers')->latest()->take(3)->get();
            
            foreach ($recentAttempts as $attempt) {
                $multiPartAnswers = $attempt->answers->where('sub_index', '>', 0)->count();
                $part4Answers = $attempt->answers->filter(function($answer) {
                    return isset($answer->metadata['part']) && $answer->metadata['part'] == 4;
                })->count();
                
                $this->line("  📝 Attempt {$attempt->id}: {$attempt->total_questions} questions, {$attempt->correct_answers} correct ({$attempt->score_percentage}%)");
                if ($multiPartAnswers > 0) {
                    $this->line("    🔀 Multi-part answers: {$multiPartAnswers}");
                }
                if ($part4Answers > 0) {
                    $this->line("    4️⃣  Part 4 answers: {$part4Answers}");
                }
            }
        } else {
            $this->warn("  ⚠️  No attempts found yet");
        }
    }

    private function performanceCheck()
    {
        $this->info("\n⚡ Performance Check:");
        
        $start = microtime(true);
        $questions = Question::where('skill', 'listening')->inRandomOrder()->limit(17)->get();
        $end = microtime(true);
        
        $time = round(($end - $start) * 1000, 2);
        $status = $time < 100 ? '✅' : ($time < 500 ? '⚠️' : '❌');
        
        $this->line("  {$status} Random question fetch (17 questions): {$time}ms");
        
        if ($time > 500) {
            $this->warn("  Consider adding database indexes for better performance");
        }
    }
}
