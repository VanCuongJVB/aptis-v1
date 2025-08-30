<?php

namespace Tests\Feature\Reading;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgressTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_progress()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(['type' => 'reading']);
        
        // Create some completed attempts
        Attempt::factory()
            ->count(5)
            ->for($user)
            ->for($quiz)
            ->create([
                'status' => 'completed',
                'score' => 0.8
            ]);

        $response = $this->actingAs($user)
            ->get('/reading/progress');

        $response->assertStatus(200)
            ->assertViewIs('student.reading.progress.index')
            ->assertViewHas('stats')
            ->assertSee('80%'); // Average score
    }

    public function test_user_can_view_detailed_stats()
    {
        $user = User::factory()->create();
        
        // Create attempts across different parts
        for ($part = 1; $part <= 4; $part++) {
            $quiz = Quiz::factory()->create(['type' => 'reading']);
            $questions = Question::factory()
                ->count(5)
                ->create([
                    'quiz_id' => $quiz->id,
                    'part' => $part
                ]);
                
            Attempt::factory()
                ->for($user)
                ->for($quiz)
                ->create([
                    'status' => 'completed',
                    'score' => 0.8
                ]);
        }

        $response = $this->actingAs($user)
            ->get('/reading/progress/stats');

        $response->assertStatus(200)
            ->assertViewIs('student.reading.progress.stats')
            ->assertViewHas(['dailyStats', 'typeStats']);
    }

    public function test_user_can_view_attempt_history()
    {
        $user = User::factory()->create();
        $attempts = Attempt::factory()
            ->count(10)
            ->for($user)
            ->create();

        $response = $this->actingAs($user)
            ->get('/reading/progress/history');

        $response->assertStatus(200)
            ->assertViewIs('student.reading.progress.history')
            ->assertViewHas('attempts');
    }

    public function test_user_can_filter_attempt_history()
    {
        $user = User::factory()->create();
        
        // Create attempts for different parts
        for ($part = 1; $part <= 4; $part++) {
            $quiz = Quiz::factory()->create(['type' => 'reading']);
            $questions = Question::factory()
                ->create([
                    'quiz_id' => $quiz->id,
                    'part' => $part
                ]);
                
            Attempt::factory()
                ->for($user)
                ->for($quiz)
                ->create();
        }

        $response = $this->actingAs($user)
            ->get('/reading/progress/history?part=1&type=test');

        $response->assertStatus(200)
            ->assertViewHas('attempts');
        
        // Verify filtered results
        $attempts = $response->viewData('attempts');
        foreach ($attempts as $attempt) {
            $this->assertTrue($attempt->items->first()->question->part === 1);
            $this->assertEquals('test', $attempt->mode);
        }
    }
}
