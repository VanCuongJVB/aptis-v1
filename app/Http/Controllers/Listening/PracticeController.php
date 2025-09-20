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

        // Recompute attempt totals at item-level across listening parts so headers show accurate accuracy
        // Use defensive normalization to avoid exceptions from unexpected metadata shapes.
        try {
            $totalItems = 0;
            $correctItems = 0;
            $computedTotals = null;
            $computedDetails = [];

            $normalizeSelected = function($ans) {
                if (! $ans) return null;
                $meta = $ans->metadata ?? null;
                if (is_array($meta) && isset($meta['selected']['option_id'])) return $meta['selected']['option_id'];
                if (is_array($meta) && isset($meta['selected']) && !is_array($meta['selected'])) return $meta['selected'];
                if (is_array($meta) && isset($meta['selected']) && is_array($meta['selected'])) {
                    // try common shapes: first scalar value or option_id inside
                    $s = $meta['selected'];
                    if (isset($s['option_id'])) return $s['option_id'];
                    if (isset($s[0]) && !is_array($s[0])) return $s[0];
                    return null;
                }
                if (isset($ans->selected_option_id)) return $ans->selected_option_id;
                return null;
            };

            foreach ($questions as $q) {
                try {
                    $meta = $q->metadata ?? [];
                    $part = $q->part ?? ($meta['part'] ?? null);

                    // multiple choice single items (parts 1,16,17 and other MC types)
                    if (in_array($part, [1,16,17]) || ($meta['type'] ?? '') === 'mc') {
                        $totalItems++;
                        $correctIndex = $meta['correct_index'] ?? $meta['correct'] ?? null;
                        // normalize scalar correct index
                        if (is_array($correctIndex) && isset($correctIndex['option_id'])) $correctIndex = $correctIndex['option_id'];
                        if (is_array($correctIndex) && isset($correctIndex[0]) && !is_array($correctIndex[0])) $correctIndex = $correctIndex[0];

                        $ans = $answers->get($q->id);
                        $selected = $normalizeSelected($ans);

                        if (!is_null($correctIndex) && !is_null($selected)) {
                            if ((is_scalar($selected) && is_scalar($correctIndex) && ((string)$selected === (string)$correctIndex || (int)$selected === (int)$correctIndex))) {
                                $correctItems++;
                            }
                        }
                        continue;
                    }

                    // Part 4 grouped/pair items: metadata may include 'questions' array with per-sub correct_index
                    if ($part == 4) {
                        if (isset($meta['questions']) && is_array($meta['questions'])) {
                            // normalize user's selected answers for this question
                            $ans = $answers->get($q->id);
                            $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;
                            $selectedList = [];
                            if (is_array($ansMeta)) {
                                if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $selectedList = array_values($ansMeta['selected']);
                                elseif (isset($ansMeta['selected'])) $selectedList = [$ansMeta['selected']];
                            } elseif (is_string($ansMeta)) {
                                $dec = json_decode($ansMeta, true);
                                if (is_array($dec) && isset($dec['selected']) && is_array($dec['selected'])) $selectedList = array_values($dec['selected']);
                            }

                            $subQs = $meta['questions'];
                            for ($i = 0; $i < count($subQs); $i++) {
                                $sub = $subQs[$i];
                                $correctIdx = $sub['correct_index'] ?? $sub['correct'] ?? null;
                                $userSel = $selectedList[$i] ?? null;
                                $totalItems++;
                                if ($correctIdx !== null && $userSel !== null) {
                                    if ((string)$userSel === (string)$correctIdx || ((is_numeric($userSel) || is_numeric($correctIdx)) && (int)$userSel === (int)$correctIdx)) {
                                        $correctItems++;
                                    }
                                }
                            }
                            continue;
                        }
                        // fallback: treat as single MC when no 'questions' array present
                        $totalItems++;
                        $correctIndex = $meta['correct_index'] ?? $meta['correct'] ?? null;
                        $ans = $answers->get($q->id);
                        $selected = $normalizeSelected($ans);
                        if (!is_null($correctIndex) && !is_null($selected)) {
                            if ((string)$selected === (string)$correctIndex || ((is_numeric($selected) || is_numeric($correctIndex)) && (int)$selected === (int)$correctIndex)) {
                                $correctItems++;
                            }
                        }
                        continue;
                    }

                    // Speaker completion / mapping (part 14) and listening speaker-match (part 2)
                    if ($part == 14 || ($meta['type'] ?? '') === 'speakers' || $part == 2 || ($meta['type'] ?? '') === 'listening_speakers_match') {
                        $correct = $meta['answers'] ?? [];
                        $ans = $answers->get($q->id);
                        $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;

                        // Normalize user's mapping into an array of indices (0-based)
                        $valsUser = [];
                        if (is_array($ansMeta)) {
                            if (isset($ansMeta['order']) && is_array($ansMeta['order'])) {
                                $valsUser = array_values($ansMeta['order']);
                            } elseif (isset($ansMeta['originalIndices']) && is_array($ansMeta['originalIndices'])) {
                                $valsUser = array_values($ansMeta['originalIndices']);
                            } elseif (isset($ansMeta['texts']) && is_array($ansMeta['texts']) && isset($meta['options']) && is_array($meta['options'])) {
                                // map texts back to option indexes when available
                                foreach ($ansMeta['texts'] as $t) {
                                    $found = array_search($t, $meta['options']);
                                    $valsUser[] = $found === false ? null : $found;
                                }
                            } elseif (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) {
                                $valsUser = array_values($ansMeta['selected']);
                            } elseif (isset($ansMeta['selected'])) {
                                $valsUser = is_array($ansMeta['selected']) ? array_values($ansMeta['selected']) : [$ansMeta['selected']];
                            }
                        }

                        $valsUser = array_values($valsUser);
                        $valsCorr = is_array($correct) ? array_values($correct) : [$correct];

                        // Detect 1-based vs 0-based indices by comparing minima and normalize to 0-based
                        $numericUser = array_filter($valsUser, 'is_numeric');
                        $numericCorr = array_filter($valsCorr, 'is_numeric');
                        if (!empty($numericUser) && !empty($numericCorr)) {
                            $minUser = (int)min($numericUser);
                            $minCorr = (int)min($numericCorr);
                            if ($minUser > $minCorr) {
                                // user indices appear offset (e.g. 1-based while correct is 0-based)
                                $offset = $minUser - $minCorr;
                                $valsUser = array_map(function($v) use ($offset) { return is_numeric($v) ? ((int)$v - $offset) : $v; }, $valsUser);
                            }
                        }

                        $totalItems += count($valsCorr);
                        // grade per-item matches and record detail
                        $matches = [];
                        $matchCount = 0;
                        for ($i = 0; $i < count($valsCorr); $i++) {
                            $u = $valsUser[$i] ?? null;
                            $c = $valsCorr[$i] ?? null;
                            $ok = false;
                            if ($u !== null && $c !== null) {
                                if ((string)$u === (string)$c || ((is_numeric($u) || is_numeric($c)) && (int)$u === (int)$c)) {
                                    $ok = true;
                                    $correctItems++;
                                    $matchCount++;
                                }
                            }
                            $matches[] = ['user' => $u, 'correct' => $c, 'match' => $ok];
                        }

                        $computedDetails[$q->id] = [
                            'question_id' => $q->id,
                            'valsUser' => $valsUser,
                            'valsCorr' => $valsCorr,
                            'matches' => $matches,
                            'matchCount' => $matchCount
                        ];
                        continue;
                    }

                    // Who expresses (part 15)
                    if ($part == 15 || ($meta['type'] ?? '') === 'who_expresses') {
                        $correct = $meta['answers'] ?? [];
                        $ans = $answers->get($q->id);
                        $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;
                        $selected = $ansMeta['selected'] ?? $ansMeta ?? [];
                        $valsUser = is_array($selected) ? array_values($selected) : [$selected];
                        $valsCorr = is_array($correct) ? array_values($correct) : [$correct];
                        $totalItems += count($valsCorr);
                        if (json_encode($valsUser) === json_encode($valsCorr)) $correctItems += count($valsCorr);
                        continue;
                    }

                    // Part 3: grouped multiple-select items (items + options)
                    if ($part == 3 || (!empty($meta['items']) && is_array($meta['items']))) {
                        $items = is_array($meta['items']) ? array_values($meta['items']) : [];
                        $answersKey = $meta['answers'] ?? $meta['correct'] ?? [];

                        $ans = $answers->get($q->id);
                        $ansMeta = $ans && isset($ans->metadata) ? $ans->metadata : null;

                        // normalize user selections into an array per item
                        $valsUser = [];
                        if (is_array($ansMeta)) {
                            if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) {
                                $valsUser = array_values($ansMeta['selected']);
                            } elseif (isset($ansMeta['values']) && is_array($ansMeta['values'])) {
                                $valsUser = array_values($ansMeta['values']);
                            } elseif (isset($ansMeta['value']) && is_array($ansMeta['value'])) {
                                $valsUser = array_values($ansMeta['value']);
                            } else {
                                // attempt to use positional entries
                                $valsUser = array_values($ansMeta);
                            }
                        } elseif (is_string($ansMeta)) {
                            $dec = json_decode($ansMeta, true);
                            if (is_array($dec)) $valsUser = array_values($dec);
                        }

                        $valsUser = array_values($valsUser);
                        $valsCorr = is_array($answersKey) ? array_values($answersKey) : [$answersKey];

                        $totalItems += count($valsCorr);
                        $matches = [];
                        $matchCount = 0;

                        for ($i = 0; $i < count($valsCorr); $i++) {
                            $u = $valsUser[$i] ?? null;
                            $c = $valsCorr[$i] ?? null;
                            // normalize texts to compare when options exist
                            $ok = false;
                            if ($u !== null && $c !== null) {
                                if ((string)$u === (string)$c || ((is_numeric($u) || is_numeric($c)) && (int)$u === (int)$c)) {
                                    $ok = true;
                                    $correctItems++;
                                    $matchCount++;
                                }
                            }
                            $matches[] = ['user' => $u, 'correct' => $c, 'match' => $ok];
                        }

                        $computedDetails[$q->id] = [
                            'question_id' => $q->id,
                            'valsUser' => $valsUser,
                            'valsCorr' => $valsCorr,
                            'matches' => $matches,
                            'matchCount' => $matchCount
                        ];

                        continue;
                    }

                    // fallback: treat as single item question
                    $totalItems++;
                    $ans = $answers->get($q->id);
                    if ($ans && ($ans->is_correct ?? false)) $correctItems++;
                } catch (\Throwable $inner) {
                    // skip problematic question but continue recompute for others
                    continue;
                }
            }

            $scorePercentage = $totalItems > 0 ? round(($correctItems / $totalItems) * 100, 2) : 0;
            $attempt->update([
                'total_questions' => $totalItems,
                'correct_answers' => $correctItems,
                'score_percentage' => $scorePercentage
            ]);

            $computedTotals = ['total' => $totalItems, 'correct' => $correctItems, 'score' => $scorePercentage, 'duration' => $duration];
        } catch (\Throwable $e) {
            // ignore and leave attempt as-is
        }

        return view('student.listening.result', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $questions,
            'answers' => $answers,
            'duration' => $duration,
            'computedTotals' => $computedTotals ?? null,
            'computedDetails' => $computedDetails ?? null
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
            // Multiple choice (parts 1,16,17 and others) OR Part 4 grouped pairs
            if (in_array($part, [1,16,17]) || ($meta['type'] ?? '') === 'mc') {
                $correctIndex = $meta['correct_index'] ?? $meta['correct'] ?? null;
                $selected = $answerMeta['selected']['option_id'] ?? $answerMeta['selected'] ?? ($answerMeta['option_id'] ?? null);
                $result['is_correct'] = !is_null($correctIndex) && ((string)$selected === (string)$correctIndex || (int)$selected === (int)$correctIndex);
                $result['correct_data'] = $correctIndex;
                return $result;
            }

            // Part 4: support grouped/pair questions shape where metadata contains 'questions' array
            if ($part == 4) {
                // if metadata has a 'questions' array, treat as grouped items
                if (isset($meta['questions']) && is_array($meta['questions'])) {
                    $subQs = $meta['questions'];
                    // normalize selected array
                    $selectedList = [];
                    if (is_array($answerMeta) && isset($answerMeta['selected']) && is_array($answerMeta['selected'])) {
                        $selectedList = array_values($answerMeta['selected']);
                    } elseif (is_array($answerMeta) && isset($answerMeta['selected'])) {
                        $selectedList = [$answerMeta['selected']];
                    } elseif (is_string($answerMeta)) {
                        $dec = json_decode($answerMeta, true);
                        if (is_array($dec) && isset($dec['selected']) && is_array($dec['selected'])) $selectedList = array_values($dec['selected']);
                    }

                    $allCorrect = true;
                    $correctData = [];
                    for ($i = 0; $i < count($subQs); $i++) {
                        $sub = $subQs[$i];
                        $correctIdx = $sub['correct_index'] ?? $sub['correct'] ?? null;
                        $userSel = $selectedList[$i] ?? null;
                        $correctData[] = $correctIdx;
                        if ($correctIdx === null) { $allCorrect = false; continue; }
                        if ((string)$userSel === (string)$correctIdx || ((is_numeric($userSel) || is_numeric($correctIdx)) && (int)$userSel === (int)$correctIdx)) {
                            // ok
                        } else {
                            $allCorrect = false;
                        }
                    }

                    $result['is_correct'] = $allCorrect;
                    $result['correct_data'] = $correctData;
                    return $result;
                }
                // fallback: treat as single MC when no 'questions' array present
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
