<?php

namespace Tests\Feature\Reading;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrillModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_drill_mode()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(['type' => 'reading']);
        $questions = Question::factory()
            ->count(5)
            ->create([
                'quiz_id' => $quiz->id,
                'part' => 1
            ]);

        $response = $this->actingAs($user)
            ->get('/reading/drill/1');

        $response->assertStatus(200)
            ->assertViewIs('student.reading.drill.show')
            ->assertViewHas('questions');
    }

    public function test_guest_cannot_access_drill_mode()
    {
        $response = $this->get('/reading/drill/1');
        $response->assertRedirect('/login');
    }

    public function test_user_can_submit_drill_answers()
    {
        $user = User::factory()->create();
        $quiz = Quiz::factory()->create(['type' => 'reading']);
        $question = Question::factory()
            ->hasOptions(4)
            ->create([
                'quiz_id' => $quiz->id,
                'part' => 1
            ]);

        $response = $this->actingAs($user)
            ->post('/reading/drill/submit', [
                'question_id' => $question->id,
                'selected_option_id' => $question->options->first()->id
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'correct',
                'explanation',
                'next_question'
            ]);

        $this->assertDatabaseHas('attempt_items', [
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);
    }
}
