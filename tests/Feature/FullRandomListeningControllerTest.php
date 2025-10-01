<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Question;
use App\Models\Attempt;
use App\Models\AttemptAnswer;

class FullRandomListeningControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $questions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test questions for each part
        $this->questions = collect([
            // Part 1 question
            Question::factory()->create([
                'skill' => 'listening',
                'part' => 1,
                'stem' => 'Test Part 1 Question',
                'metadata' => [
                    'audio' => 'test-audio.mp3',
                    'options' => ['Option A', 'Option B', 'Option C'],
                    'correct_index' => 1
                ]
            ]),
            // Part 4 question
            Question::factory()->create([
                'skill' => 'listening',
                'part' => 4,
                'stem' => 'Test Part 4 Question',
                'metadata' => [
                    'audio' => 'test-audio.mp3',
                    'questions' => [
                        [
                            'sub' => 'A',
                            'stem' => 'Sub question 1?',
                            'options' => ['Option A', 'Option B', 'Option C'],
                            'correct_index' => 1
                        ],
                        [
                            'sub' => 'B', 
                            'stem' => 'Sub question 2?',
                            'options' => ['Option A', 'Option B', 'Option C'],
                            'correct_index' => 2
                        ]
                    ]
                ]
            ])
        ]);
    }

    public function test_can_submit_part1_answer()
    {
        $question = $this->questions->where('part', 1)->first();
        
        $answers = [
            [
                'qid' => $question->id,
                'userAnswer' => 1,
                'correctAnswer' => 1,
                'part' => 1,
                'correct' => true
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('listening.full-random.submit'), [
                'answers' => $answers
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('attempt_answers', [
            'question_id' => $question->id,
            'sub_index' => 0,
            'is_correct' => true,
            'text_answer' => '1'
        ]);
    }

    public function test_can_submit_part4_answers()
    {
        $question = $this->questions->where('part', 4)->first();
        
        $answers = [
            [
                'qid' => $question->id,
                'userAnswer' => [1, 2],
                'correctAnswer' => [1, 2],
                'part' => 4,
                'correct' => true
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('listening.full-random.submit'), [
                'answers' => $answers
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        // Check that 2 sub-answers were created
        $attempt = Attempt::where('user_id', $this->user->id)->first();
        $subAnswers = AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->orderBy('sub_index')
            ->get();
            
        $this->assertCount(2, $subAnswers);
        $this->assertEquals(0, $subAnswers[0]->sub_index);
        $this->assertEquals(1, $subAnswers[1]->sub_index);
        $this->assertTrue($subAnswers[0]->is_correct);
        $this->assertTrue($subAnswers[1]->is_correct);
    }

    public function test_handles_null_user_answers_part4()
    {
        $question = $this->questions->where('part', 4)->first();
        
        $answers = [
            [
                'qid' => $question->id,
                'userAnswer' => null, // User didn't answer
                'correctAnswer' => [1, 2],
                'part' => 4,
                'correct' => false
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('listening.full-random.submit'), [
                'answers' => $answers
            ]);

        $response->assertStatus(200);
        
        // Should create single answer record for Part 1 logic when userAnswer is null
        $attempt = Attempt::where('user_id', $this->user->id)->first();
        $answers = AttemptAnswer::where('attempt_id', $attempt->id)->get();
        
        $this->assertCount(1, $answers);
        $this->assertFalse($answers[0]->is_correct);
    }

    public function test_score_calculation_mixed_parts()
    {
        $part1Question = $this->questions->where('part', 1)->first();
        $part4Question = $this->questions->where('part', 4)->first();
        
        $answers = [
            [
                'qid' => $part1Question->id,
                'userAnswer' => 1,
                'correctAnswer' => 1,
                'part' => 1,
                'correct' => true
            ],
            [
                'qid' => $part4Question->id,
                'userAnswer' => [1, 0], // First correct, second wrong
                'correctAnswer' => [1, 2],
                'part' => 4,
                'correct' => false
            ]
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('listening.full-random.submit'), [
                'answers' => $answers
            ]);

        $response->assertStatus(200);
        
        $attempt = Attempt::where('user_id', $this->user->id)->first();
        
        // 2 correct out of 3 total (1 from part1 + 1 correct from part4)
        $this->assertEquals(2, $attempt->correct_answers);
        $this->assertEquals(3, $attempt->total_questions);
        $this->assertEquals(66.67, $attempt->score_percentage);
    }

    public function test_can_view_results()
    {
        // Create attempt with mixed answers
        $attempt = Attempt::create([
            'user_id' => $this->user->id,
            'quiz_id' => 1,
            'status' => 'submitted',
            'started_at' => now(),
            'submitted_at' => now(),
            'total_questions' => 3,
            'correct_answers' => 2,
            'score_percentage' => 66.67,
        ]);

        $question = $this->questions->where('part', 4)->first();
        
        // Create sub-answers for Part 4
        AttemptAnswer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'sub_index' => 0,
            'is_correct' => true,
            'text_answer' => '1',
            'metadata' => [
                'userAnswer' => 1,
                'correct' => 1,
                'part' => 4,
                'sub_index' => 0
            ]
        ]);
        
        AttemptAnswer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'sub_index' => 1,
            'is_correct' => false,
            'text_answer' => '0',
            'metadata' => [
                'userAnswer' => 0,
                'correct' => 2,
                'part' => 4,
                'sub_index' => 1
            ]
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('listening.full-random.result', $attempt));

        $response->assertStatus(200);
        $response->assertViewIs('student.listening.full_random_result');
        $response->assertViewHas('attempt');
        $response->assertViewHas('answers');
        
        // Check that answers are correctly reconstructed
        $viewData = $response->viewData('answers');
        $part4Answer = $viewData->where('part', 4)->first();
        
        $this->assertIsArray($part4Answer['userAnswer']);
        $this->assertIsArray($part4Answer['correctAnswer']);
        $this->assertEquals([1, 0], $part4Answer['userAnswer']);
        $this->assertEquals([1, 2], $part4Answer['correctAnswer']);
        $this->assertFalse($part4Answer['correct']);
    }
}