<?php

namespace App\Http\Controllers\Listening;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use App\Models\ReadingSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PracticeController extends Controller
{
    public function index()
    {
        return redirect()->route('student.dashboard');
    }

    public function partDetail(int $part)
    {
        if ($part < 1 || $part > 4) return redirect()->route('listening.sets.index');

        $quizzes = Quiz::where('skill', 'listening')
            ->where('part', $part)
            ->where('is_published', true)
            ->withCount('questions')
            ->orderBy('difficulty')
            ->orderBy('created_at', 'desc')
            ->get();

        $attempts = Attempt::where('user_id', Auth::id())
            ->whereIn('quiz_id', $quizzes->pluck('id'))
            ->where('status', 'submitted')
            ->get()
            ->groupBy('quiz_id');

        return view('student.listening.part', [
            'part' => $part,
            'quizzes' => $quizzes,
            'attempts' => $attempts
        ]);
    }

    public function startQuiz(Request $request, Quiz $quiz)
    {
        if ($quiz->skill !== 'listening' || !$quiz->is_published) {
            return redirect()->route('listening.sets.index')
                ->with('error', 'Bộ đề không hợp lệ hoặc chưa được xuất bản');
        }

        $setId = $request->query('set_id', null);
        $mode = $request->query('mode', 'learning');

        $metadata = ['mode' => $mode];
        $questionOrder = [];

        if ($setId) {
            $set = ReadingSet::where('id', $setId)->where('quiz_id', $quiz->id)->first();
            if ($set) {
                $metadata['listening_set_id'] = $set->id;
                $questionOrder = $set->questions()->orderBy('order')->pluck('id')->toArray();
            }
        }

        if (empty($questionOrder)) {
            $questionOrder = $quiz->questions()->orderBy('order')->pluck('id')->toArray();
        }

        $metadata['question_order'] = $questionOrder;

        $attempt = Attempt::create([
            'user_id' => Auth::id(),
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'status' => 'in_progress',
            'metadata' => $metadata
        ]);

        return redirect()->route('listening.practice.question', ['attempt' => $attempt, 'position' => 1]);
    }

    public function showQuestion(Request $request, Attempt $attempt, int $position)
    {
        if ($attempt->user_id !== Auth::id()) abort(403);
        if ($attempt->isSubmitted()) return redirect()->route('listening.practice.result', $attempt);

        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();

        $questionsCollection = Question::whereIn('id', $order)
            ->with(['options' => function($q){ $q->orderBy('id'); }])->get()->keyBy('id');

        $payloadQuestions = collect($order)->map(function($id) use ($questionsCollection) {
            $q = $questionsCollection->get($id);
            if (! $q) return null;
            $meta = $q->metadata ?? [];

            // normalize listening shapes (MC, speakers, who-expresses)
            $meta = array_merge([ 'options' => $meta['options'] ?? [], 'correct_index' => $meta['correct_index'] ?? null ], $meta);

            return [ 'id' => $q->id, 'part' => $q->part ?? ($meta['part'] ?? null), 'type' => $meta['type'] ?? 'unknown', 'metadata' => $meta ];
        })->filter()->values()->all();

        $payload = ['questions' => $payloadQuestions];

        if (!empty($attempt->metadata['full_part']) && $attempt->metadata['full_part']) {
            $questions = collect($order)->map(function($id) use ($questionsCollection) { return $questionsCollection->get($id); })->filter()->values();

            $answersMap = AttemptAnswer::where('attempt_id', $attempt->id)->whereIn('question_id', $order)->get()->keyBy('question_id');

            $totalQuestions = count($order);
            $answeredCount = $answersMap->count();

            return view('student.listening.question', [
                'attempt' => $attempt,
                'quiz' => $attempt->quiz,
                'allQuestions' => $questions,
                'answersMap' => $answersMap,
                'position' => 1,
                'total' => $totalQuestions,
                'answered' => $answeredCount,
            ])->with('initialPayload', $payload);
        }

        $questionId = $order[$position - 1] ?? null;
        if (!$questionId) return redirect()->route('listening.practice.finish', $attempt);

        $question = Question::find($questionId);
        if (!$question) return redirect()->route('listening.practice.finish', $attempt);

        $answer = AttemptAnswer::where('attempt_id', $attempt->id)->where('question_id', $question->id)->first();

        $totalQuestions = count($order);
        $answeredCount = AttemptAnswer::where('attempt_id', $attempt->id)->whereIn('question_id', $order)->count();

        return view('student.listening.question', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'question' => $question,
            'position' => $position,
            'total' => $totalQuestions,
            'answered' => $answeredCount,
            'answer' => $answer,
            'previousPosition' => $position > 1 ? $position - 1 : null,
            'nextPosition' => $position < $totalQuestions ? $position + 1 : null
        ])->with('initialPayload', $payload);
    }

    public function submitAnswer(Request $request, Attempt $attempt, Question $question)
    {
        if ($attempt->user_id !== Auth::id()) abort(403);
        if ($attempt->isSubmitted()) return redirect()->route('listening.practice.result', $attempt);
        $answerMeta = $request->input('metadata', $request->input('answer_meta', []));
        // Accept selected_option_id from frontend (compatibility with templates)
        if ($request->has('selected_option_id')) {
            if (!is_array($answerMeta)) $answerMeta = [];
            // prefer structured selected metadata
            $answerMeta['selected'] = ['option_id' => $request->input('selected_option_id')];
        }
        if (empty($answerMeta)) {
            $answerMeta = [];
            if ($request->has('option_id')) { $answerMeta['selected'] = ['option_id' => $request->input('option_id')]; }
            if ($request->has('selected')) { $answerMeta['selected'] = $request->input('selected'); }
        }

    $selOption = $request->input('selected_option_id', $request->input('option_id', null));

        if ($request->ajax() && $request->input('action') === 'submit') {
            $grading = $this->gradeAnswer($question, $answerMeta);
            $isCorrect = $grading['is_correct'];
            $correctData = $grading['correct_data'];

            DB::transaction(function() use ($attempt, $question, $selOption, $answerMeta, $isCorrect) {
                $existing = AttemptAnswer::where('attempt_id', $attempt->id)->where('question_id', $question->id)->first();

                AttemptAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $question->id],
                    ['selected_option_id' => $selOption, 'is_correct' => $isCorrect, 'metadata' => $answerMeta]
                );

                $total = (int)($attempt->total_questions ?? 0);
                $correct = (int)($attempt->correct_answers ?? 0);

                if (! $existing) {
                    $total += 1;
                    if ($isCorrect) $correct += 1;
                } else {
                    $prevCorrect = (bool)$existing->is_correct;
                    if ($prevCorrect !== $isCorrect) {
                        $correct += $isCorrect ? 1 : -1;
                        if ($correct < 0) $correct = 0;
                    }
                }

                $attempt->total_questions = $total;
                $attempt->correct_answers = $correct;
                $attempt->save();
            });

            $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
            $totalQuestions = count($order);
            $answeredCount = AttemptAnswer::where('attempt_id', $attempt->id)->whereIn('question_id', $order)->count();

            $response = ['success' => true, 'is_correct' => $isCorrect, 'correct' => $correctData];

            if ($answeredCount >= $totalQuestions) {
                $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)->whereIn('question_id', $order)->where('is_correct', true)->count();
                $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

                $attempt->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'score_percentage' => $scorePercentage,
                    'score_points' => $correctAnswers
                ]);

                $response['submitted'] = true;
                $response['redirect'] = route('listening.practice.result', $attempt);
            } else {
                $posIndex = array_search($question->id, $order);
                $nextPosition = $posIndex === false ? null : $posIndex + 2;
                $response['next_position'] = $nextPosition;
            }

            return response()->json($response);
        }

        if ($request->ajax()) {
            if ($request->input('action') === 'finish') {
                $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
                $totalQuestions = count($order);

                $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)->whereIn('question_id', $order)->where('is_correct', true)->count();
                $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

                $attempt->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                    'total_questions' => $totalQuestions,
                    'correct_answers' => $correctAnswers,
                    'score_percentage' => $scorePercentage,
                    'score_points' => $correctAnswers
                ]);

                return response()->json(['success' => true, 'redirect' => route('listening.practice.result', $attempt)]);
            }

            return response()->json(['success' => true, 'message' => 'Đã lưu câu trả lời']);
        }

        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
        $currentPosition = array_search($question->id, $order);
        $currentPosition = $currentPosition === false ? 1 : $currentPosition + 1;

        if ($request->input('action') === 'finish' || $currentPosition >= $attempt->quiz->questions()->count()) {
            return redirect()->route('listening.practice.finish', $attempt);
        } else {
            return redirect()->route('listening.practice.question', ['attempt' => $attempt, 'position' => $currentPosition + 1]);
        }
    }

    public function finishAttempt(Request $request, Attempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) abort(403);
        if ($attempt->isSubmitted()) return redirect()->route('listening.practice.result', $attempt);

        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();
        $totalQuestions = count($order);

        $correctAnswers = AttemptAnswer::where('attempt_id', $attempt->id)->whereIn('question_id', $order)->where('is_correct', true)->count();
        $scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

        $attempt->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'total_questions' => $totalQuestions,
            'correct_answers' => $correctAnswers,
            'score_percentage' => $scorePercentage,
            'score_points' => $correctAnswers
        ]);

        return redirect()->route('listening.practice.result', $attempt);
    }

    /**
     * Persist a batch of answers for an attempt and finalize scoring.
     * Expects JSON: { answers: { questionId: { selected: <index|value>, is_correct: bool } } }
     */
    public function batchSubmit(Request $request, Attempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) abort(403);

        $payload = $request->input('answers', []);
        $final = $request->boolean('final', false);

        DB::transaction(function() use ($attempt, $payload, $final, &$total, &$correct) {
            $total = 0;
            $correct = 0;

            foreach ($payload as $questionId => $entry) {
                $questionId = (int)$questionId;
                $selected = $entry['selected'] ?? null;
                // if frontend didn't compute is_correct, attempt to grade server-side
                $isCorrect = isset($entry['is_correct']) ? (bool)$entry['is_correct'] : null;

                // try to grade when missing - keep simple: compare against question metadata
                if (is_null($isCorrect)) {
                    try {
                        $q = Question::find($questionId);
                        $g = $this->gradeAnswer($q, ['selected' => $selected]);
                        $isCorrect = $g['is_correct'] ?? false;
                    } catch (\Exception $e) {
                        $isCorrect = false;
                    }
                }

                AttemptAnswer::updateOrCreate(
                    ['attempt_id' => $attempt->id, 'question_id' => $questionId],
                    ['selected_option_id' => $selected, 'is_correct' => $isCorrect, 'metadata' => $entry]
                );

                $total += 1;
                if ($isCorrect) $correct += 1;
            }

            $scorePercentage = $total > 0 ? round(($correct / $total) * 100, 2) : 0;

            // update totals, only finalize when final flag is true
            $updateData = [
                'total_questions' => $total,
                'correct_answers' => $correct,
                'score_percentage' => $scorePercentage,
                'score_points' => $correct
            ];

            if ($final) {
                $updateData['status'] = 'submitted';
                $updateData['submitted_at'] = now();
            }

            $attempt->update($updateData);
        });

        $response = ['success' => true, 'message' => 'Batch saved', 'submitted' => (bool)$final, 'total' => $total ?? 0, 'correct' => $correct ?? 0];
        if ($final) {
            $response['redirect'] = route('listening.practice.result', $attempt);
        }

        return response()->json($response);
    }

    public function showResult(Attempt $attempt)
    {
        if ($attempt->user_id !== Auth::id()) abort(403);

        $order = $attempt->metadata['question_order'] ?? $attempt->quiz->questions()->orderBy('order')->pluck('id')->toArray();

        $questionsCollection = Question::whereIn('id', $order)
            ->with(['options' => function($q){ $q->orderBy('id'); }])
            ->get()
            ->keyBy('id');

        $questions = collect($order)->map(function($id) use ($questionsCollection) { return $questionsCollection->get($id); })->filter()->values();

        $answers = AttemptAnswer::where('attempt_id', $attempt->id)->with('selectedOption')->get()->keyBy('question_id');

        $duration = null;
        if ($attempt->started_at && $attempt->submitted_at) {
            $duration = $attempt->submitted_at->diffInMinutes($attempt->started_at);
        }

        return view('student.listening.result', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $questions,
            'answers' => $answers,
            'duration' => $duration
        ]);
    }

    public function history()
    {
        $attempts = Attempt::where('user_id', Auth::id())
            ->whereHas('quiz', function($q){ $q->where('skill', 'listening'); })
            ->where('status', 'submitted')
            ->with('quiz')
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        return view('student.listening.history', ['attempts' => $attempts]);
    }

    private function gradeAnswer(Question $question, $answerMeta)
    {
        $meta = $question->metadata ?? [];
        $part = $question->part ?? ($meta['part'] ?? null);
        $result = ['is_correct' => false, 'correct_data' => null];

        try {
            // Multiple choice (parts 1,16,17 and others)
            if (in_array($part, [1,16,17]) || ($meta['type'] ?? '') === 'mc') {
                $correctIndex = $meta['correct_index'] ?? $meta['correct'] ?? null;
                $selected = $answerMeta['selected']['option_id'] ?? $answerMeta['selected'] ?? ($answerMeta['option_id'] ?? null);
                $result['is_correct'] = !is_null($correctIndex) && ((string)$selected === (string)$correctIndex || (int)$selected === (int)$correctIndex);
                $result['correct_data'] = $correctIndex;
                return $result;
            }

            // Speaker completion (part 14) - expect answers mapping
            if ($part == 14 || ($meta['type'] ?? '') === 'speakers') {
                $correct = $meta['answers'] ?? [];
                $selected = $answerMeta['selected'] ?? $answerMeta ?? [];
                $result['is_correct'] = json_encode(array_values($selected)) === json_encode(array_values($correct));
                $result['correct_data'] = $correct;
                return $result;
            }

            // Who expresses (part 15) - options like Man/Woman/Both
            if ($part == 15 || ($meta['type'] ?? '') === 'who_expresses') {
                $correct = $meta['answers'] ?? [];
                $selected = $answerMeta['selected'] ?? $answerMeta ?? [];
                $result['is_correct'] = json_encode(array_values($selected)) === json_encode(array_values($correct));
                $result['correct_data'] = $correct;
                return $result;
            }
        } catch (\Throwable $e) {
            // ignore and return false
        }

        return $result;
    }
}
