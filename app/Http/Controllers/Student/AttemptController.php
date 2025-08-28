<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\{Quiz, Attempt, AttemptItem};

class AttemptController extends Controller
{
    public function start(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        Attempt::create(['user_id' => Auth::id(), 'quiz_id' => $quiz->id, 'started_at' => now()]);
        return redirect()->route('student.quizzes.show', $quiz);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $answers = $request->input('answers', []);

        $attempt = Attempt::create([
            'user_id'      => $request->user()->id,
            'quiz_id'      => $quiz->id,
            'started_at'   => now(),
            'submitted_at' => now(),
        ]);

        $quiz->load('questions.options');

        $questionCount = max(1, $quiz->questions->count());
        $scoreSum = 0.0; // có thể cộng số thực (phần đúng theo item)

        foreach ($quiz->questions as $question) {
            if ($question->type === 'matching') {
                $meta      = $question->meta ?? [];
                $items     = $meta['items'] ?? [];
                $key       = $meta['answer'] ?? [];   // ví dụ: ["1"=>"A","2"=>"C",...]
                $sources   = $meta['sources'] ?? [];
                $pairCount = intdiv(count($sources), 2);

                // labels hợp lệ (A..Z tùy số nguồn)
                $labels = [];
                for ($i = 0; $i < max(1, $pairCount); $i++) {
                    $labels[] = chr(65 + $i);
                }

                // bài làm của SV cho câu này:
                $stuMap = $answers[$question->id] ?? []; // ["1"=>"B","2"=>"C",...]

                // Chuẩn hóa & whitelist
                $normalized = [];
                foreach ($stuMap as $idx => $val) {
                    $v = strtoupper(trim((string)$val));
                    if (in_array($v, $labels, true)) {
                        $normalized[(string)$idx] = $v;
                    }
                }

                // Chấm từng item
                $itemCount   = count($items);
                $itemCorrect = 0;
                for ($i = 1; $i <= $itemCount; $i++) {
                    $ix   = (string)$i;
                    $gold = isset($key[$ix]) ? strtoupper((string)$key[$ix]) : null;
                    $ans  = $normalized[$ix] ?? null;
                    if ($gold && $ans && $gold === $ans) {
                        $itemCorrect++;
                    }
                }

                // Tính điểm cho câu (tỷ lệ đúng các item)
                $qScore   = ($itemCount > 0) ? ($itemCorrect / $itemCount) : 0.0;
                $isCorrect = ($itemCount > 0) ? ($itemCorrect === $itemCount) : false; // nếu muốn đánh dấu "đúng hoàn toàn"

                // Lưu AttemptItem (lưu map chữ cái)
                AttemptItem::create([
                    'attempt_id'          => $attempt->id,
                    'question_id'         => $question->id,
                    'selected_option_ids' => $normalized, // lưu map { "1":"A", ... }
                    'is_correct'          => $isCorrect,
                    'time_spent_sec'      => 0,
                ]);

                $scoreSum += $qScore;
            } else {
                // single/multiple: giữ logic cũ
                $selected = array_map('intval', $answers[$question->id] ?? []);
                sort($selected);

                $correctIds = $question->options()->where('is_correct', true)->pluck('id')->toArray();
                sort($correctIds);

                $isCorrect = $question->type === 'single'
                    ? (count($selected) === 1) && ($selected[0] === ($correctIds[0] ?? null))
                    : ($selected === $correctIds);

                // Điểm câu: đúng = 1, sai = 0
                $qScore = $isCorrect ? 1.0 : 0.0;

                AttemptItem::create([
                    'attempt_id'          => $attempt->id,
                    'question_id'         => $question->id,
                    'selected_option_ids' => $selected, // array<int>
                    'is_correct'          => $isCorrect,
                    'time_spent_sec'      => 0,
                ]);

                $scoreSum += $qScore;
            }
        }

        // Tính score tổng (trung bình điểm mỗi câu * 100)
        $scorePercent = round(($scoreSum / $questionCount) * 100, 2);

        // Nếu bạn vẫn muốn có "score_raw" theo số câu đúng tròn (không phù hợp khi có matching phân số),
        // có thể lưu thêm "score_points" là tổng điểm thực:
        $attempt->update([
            'score_raw'     => $scoreSum,     // bây giờ là điểm thực (có thể không nguyên)
            'score_percent' => $scorePercent,
        ]);

        return redirect()->route('student.attempts.result', $attempt);
    }


    public function result(Attempt $attempt)
    {
        $attempt->load(['quiz.questions.options', 'items']);
        $itemsByQid = $attempt->items->keyBy('question_id');
        return view('student.attempts.show', compact('attempt', 'itemsByQid'));
    }
}
