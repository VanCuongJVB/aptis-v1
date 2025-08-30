<?php

namespace Tests\Feature\Reading;

use App\Models\Attempt;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_test()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(['type' => 'reading']);
        $questions = Question::factory()
            ->count(20)
            ->sequence(fn ($sequence) => [
                'quiz_id' => $quiz->id,
                'part' => ceil($sequence->index / 5)
            ])
            ->create();

        $response = $this->actingAs($user)
            ->post('/reading/test/start');

        $response->assertStatus(200)
            ->assertViewIs('student.reading.test.show');

        $this->assertDatabaseHas('attempts', [
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'mode' => 'test'
        ]);
    }

    public function test_user_can_submit_test()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(['type' => 'reading']);
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(20)
            ->sequence(fn ($sequence) => [
                'quiz_id' => $quiz->id,
                'part' => ceil($sequence->index / 5)
            ])
            ->create();
        
        $attempt = Attempt::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'mode' => 'test'
        ]);

        $answers = $questions->mapWithKeys(function ($question) {
            return [$question->id => [
                'option_id' => $question->options->first()->id
            ]];
        })->all();

        $response = $this->actingAs($user)
            ->post("/reading/test/{$attempt->id}/submit", [
                'answers' => $answers
            ]);

        $response->assertStatus(200)
            ->assertViewIs('student.reading.test.result')
            ->assertViewHas(['attempt', 'score']);

        $this->assertDatabaseHas('attempts', [
            'id' => $attempt->id,
            'status' => 'completed'
        ]);
    }

    public function test_cannot_submit_test_twice()
    {
        $user = User::factory()->create();
        $attempt = Attempt::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($user)
            ->post("/reading/test/{$attempt->id}/submit", [
                'answers' => []
            ]);

        $response->assertStatus(403);
    }
}
