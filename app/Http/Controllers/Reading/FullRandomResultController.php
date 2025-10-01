<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FullRandomResultController extends Controller
{
    /**
     * Store the results of a full random reading test
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'answers' => 'required|array',
            'answers.*.qid' => 'required|integer|exists:questions,id',
            'answers.*.part' => 'required|integer|min:1|max:4',
            'answers.*.correct' => 'required|boolean',
            'answers.*.userAnswer' => 'nullable',
            'answers.*.correctAnswer' => 'nullable',
        ]);

        // Create a virtual quiz for this attempt
        $quiz = new Quiz();
        $quiz->title = 'Full Random Reading Test';
        $quiz->description = 'Auto-generated full reading test with random questions';
        $quiz->skill = 'reading';
        $quiz->part = 0; // Use part 0 to indicate it's a full test
        $quiz->metadata = ['type' => 'full_random']; // Store the type in metadata
        $quiz->save();

        // Create an attempt
        $attempt = new Attempt();
        $attempt->user_id = Auth::id();
        $attempt->quiz_id = $quiz->id;
        $attempt->started_at = now()->subMinutes(15); // Assume they started 15 min ago
        $attempt->submitted_at = now();
        
        // Extract question IDs and build metadata
        $questionIds = collect($request->answers)->pluck('qid')->toArray();
        $questionParts = collect($request->answers)->pluck('part', 'qid')->toArray();
        
        // Store question order and parts in metadata
        $attempt->metadata = [
            'question_order' => $questionIds,
            'question_parts' => $questionParts,
            'is_full_random' => true,
        ];
        
        $attempt->save();

        // Process and store answers
        $correctCount = 0;
        $totalQuestions = count($request->answers);
        
        foreach ($request->answers as $answer) {
            $attemptAnswer = new AttemptAnswer();
            $attemptAnswer->attempt_id = $attempt->id;
            $attemptAnswer->question_id = $answer['qid'];
            $partNumber = $answer['part'] ?? 0;
            
            // Handle different answer formats based on the part
            $userAnswer = $answer['userAnswer'];
            $correctAnswer = $answer['correctAnswer'] ?? null;
            $is_correct = $answer['correct'] === true ? true : false; // Ensure boolean value
            $questionMeta = null;
            
            // Try to get the question's metadata for better context
            $question = Question::find($answer['qid']);
            if ($question) {
                if (is_string($question->metadata) && !empty($question->metadata)) {
                    try {
                        $questionMeta = json_decode($question->metadata, true) ?: null;
                    } catch (\Exception $e) {
                        $questionMeta = null;
                    }
                } elseif (is_array($question->metadata) || is_object($question->metadata)) {
                    $questionMeta = (array)$question->metadata;
                }
            }
            
            // Build metadata based on part-specific answer structure
            $metadata = [
                'user_answer' => $userAnswer,
                'correct_answer' => $correctAnswer,
                'part' => $partNumber,
                'original' => [
                    'userAnswer' => $userAnswer,
                    'correctAnswer' => $correctAnswer,
                ]
            ];
            
            // Part-specific processing
            if ($partNumber === 1) {
                // Part 1 - Fill in the blanks/multiple choice
                if (is_numeric($userAnswer)) {
                    // If it's a direct option selection, store it
                    $attemptAnswer->selected_option_id = $userAnswer;
                } elseif (is_array($userAnswer)) {
                    // For array-type answers (word-by-word comparison)
                    $metadata['selected'] = array_map(function($val) {
                        return is_array($val) ? $val['text'] ?? (string)$val : (string)$val;
                    }, (array)$userAnswer);

                    // Also store the correct answers if available
                    if (isset($questionMeta['correct_answers'])) {
                        $metadata['correct_answer'] = array_map(function($val) {
                            return is_array($val) ? $val['text'] ?? (string)$val : (string)$val; 
                        }, (array)$questionMeta['correct_answers']);
                    } elseif (isset($correctAnswer) && is_array($correctAnswer)) {
                        $metadata['correct_answer'] = array_map(function($val) {
                            return is_array($val) ? $val['text'] ?? (string)$val : (string)$val;
                        }, $correctAnswer);
                    }
                }
            } elseif ($partNumber === 2) {
                // Part 2 - Sentence ordering
                if (is_array($userAnswer)) {
                    // Store the user's order and the correct order
                    $metadata['selected'] = $userAnswer;
                    
                    // If we have the question metadata with correct_order, add it for reference
                    if ($questionMeta && isset($questionMeta['correct_order'])) {
                        $metadata['correct_order'] = $questionMeta['correct_order'];
                    }
                }
            } elseif ($partNumber === 3) {
                // Part 3 - Category matching
                if (is_array($userAnswer) || is_object($userAnswer)) {
                    // Store the structure mapping categories to options
                    $metadata['selected'] = $userAnswer;
                    
                    // If we have answers from the question metadata, preserve them
                    if ($questionMeta && isset($questionMeta['answers'])) {
                        $metadata['answers'] = $questionMeta['answers'];
                    }
                }
            } elseif ($partNumber === 4) {
                // Part 4 - Reading comprehension/paragraph ordering
                if (is_array($userAnswer)) {
                    // Store array-based answers
                    $metadata['selected'] = $userAnswer;
                } elseif (is_object($userAnswer)) {
                    // Handle object-style answers (key-value pairs)
                    $metadata['selected'] = (array)$userAnswer;
                }
            }
            
            // Make sure is_correct is a boolean
            $attemptAnswer->is_correct = $is_correct;
            $attemptAnswer->metadata = $metadata;
            $attemptAnswer->save();
            
            if ($is_correct) {
                $correctCount++;
            }
        }
        
        // Update attempt stats
        $attempt->correct_answers = $correctCount;
        $attempt->total_questions = $totalQuestions;
        
        // Let the model calculate the score
        $attempt->calculateScore();
        $attempt->save();
        
        return response()->json([
            'success' => true,
            'attempt' => $attempt->id,
            'redirect' => route('reading.full_random_result.show', $attempt)
        ]);
    }

    /**
     * Show the results of a full random reading test
     */
    public function show(Attempt $attempt)
    {
        // Check permissions
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'You do not have permission to view this attempt');
        }

        // Verify this is a full random reading attempt
        if (!isset($attempt->metadata['is_full_random']) || $attempt->metadata['is_full_random'] !== true) {
            abort(404, 'This is not a full random reading test result');
        }
        
        // Get questions based on stored order
        $order = $attempt->metadata['question_order'] ?? [];
        $questionsCollection = Question::whereIn('id', $order)
            ->with(['options' => function($query) {
                $query->orderBy('id');
            }])
            ->get()
            ->keyBy('id');

        // Preserve the original order
        $questions = collect($order)->map(function($id) use ($questionsCollection) {
            return $questionsCollection->get($id);
        })->filter()->values();
            
        // Get answers
        $answers = AttemptAnswer::where('attempt_id', $attempt->id)
            ->with('selectedOption')
            ->get()
            ->keyBy('question_id');
        
        // Group questions by part
        $groupedQuestions = $questions->groupBy(function ($question) use ($attempt) {
            $partMap = $attempt->metadata['question_parts'] ?? [];
            return $partMap[$question->id] ?? $question->part;
        });
        
        // Make sure parts are sorted in the right order (1, 2, 3, 4)
        $sortedGroups = collect([]);
        for ($i = 1; $i <= 4; $i++) {
            if ($groupedQuestions->has($i)) {
                $sortedGroups->put($i, $groupedQuestions->get($i));
            }
        }
        $groupedQuestions = $sortedGroups;
        
        $quiz = $attempt->quiz;
        
        return view('student.reading.full_random_result', compact(
            'attempt', 
            'questions', 
            'answers', 
            'quiz', 
            'groupedQuestions'
        ));
    }
}
