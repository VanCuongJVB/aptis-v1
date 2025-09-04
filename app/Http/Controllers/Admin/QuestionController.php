<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionController extends Controller
{
    public function create(Quiz $quiz, Request $request)
    {
        $part = (int) $request->integer('part', 1);
        $types = $quiz->allowedTypesForPart($part);
        $type = $request->get('type', $types[0] ?? null);

        abort_unless($part >= 1 && $part <= 4, 404);
        abort_unless($type && in_array($type, $types, true), 404);

        return view('admin.questions.create', compact('quiz', 'part', 'type'));
    }

    public function store(Quiz $quiz, Request $request)
    {
        $part = (int) $request->input('part');
        $type = $request->input('type');

        // validate type hợp lệ với skill+part
        $allowed = $quiz->allowedTypesForPart($part);
        abort_unless(in_array($type, $allowed, true), 422);

        // base validate
        $base = $request->validate([
            'part' => ['required', 'integer', 'min:1', 'max:4'],
            'type' => ['required', 'string', Rule::in(config('aptis.question_types'))],
            'order' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'stem' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'audio_url' => ['nullable', 'url'],
        ]);

        $meta = [];

        switch ($type) {
            case 'dropdown': // Reading P1 – mỗi question = 1 câu + options + 1 đáp án
            case 'mcq_single': // Listening – giống dropdown nhưng style khác phía student
                $validated = $request->validate([
                    'options' => ['required', 'array', 'min:2'],
                    'options.*.label' => ['required', 'string'],
                    'correct_index' => ['required', 'integer', 'min:0'],
                ]);
                break;

            case 'ordering': // Reading P2
                $validated = $request->validate([
                    'items_text' => ['required', 'string'], // 5 dòng, mỗi dòng 1 câu
                ]);
                $items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['items_text']))));
                if (count($items) < 5) {
                    return back()->withErrors(['items_text' => 'Cần tối thiểu 5 câu, mỗi câu trên 1 dòng.'])->withInput();
                }
                $meta['items'] = $items;
                break;

            case 'matching': // Reading P3
                $validated = $request->validate([
                    'sources_text' => ['required', 'string'],   // 4 dòng, A-D
                    'items_text'   => ['required', 'string'],   // 7 dòng, 1-7
                    'answer_text'  => ['required', 'string'],   // ví dụ: "1:A,2:C,3:B,4:D,5:B,6:A,7:C"
                ]);
                $sources = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['sources_text']))));
                $items   = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['items_text']))));
                if (count($sources) < 4) return back()->withErrors(['sources_text' => 'Cần 4 dòng nguồn (A–D).'])->withInput();
                if (count($items) < 7)   return back()->withErrors(['items_text' => 'Cần 7 dòng phát biểu (1–7).'])->withInput();

                // parse answer map
                $map = [];
                foreach (preg_split('/\s*,\s*/', trim($validated['answer_text'])) as $pair) {
                    if (!$pair) continue;
                    [$i, $s] = array_map('trim', preg_split('/\s*:\s*/', $pair));
                    if (!is_numeric($i)) continue;
                    $s = strtoupper($s);
                    if (!in_array($s, ['A', 'B', 'C', 'D'], true)) continue;
                    $map[(int)$i] = $s;
                }
                if (count($map) < 7) {
                    return back()->withErrors(['answer_text' => 'Cần đủ 7 cặp dạng "1:A,2:B,..."'])->withInput();
                }
                $meta['sources'] = $sources; // A-D
                $meta['items']   = $items;   // 1-7
                $meta['answer']  = $map;     // {1:'A',...}
                break;

            case 'heading_matching': // Reading P4
                $validated = $request->validate([
                    'paragraphs_text' => ['required', 'string'], // 8 block, ngăn bằng dòng --- (ba gạch)
                    'headings_text'   => ['required', 'string'], // 7 dòng
                    'answer_text'     => ['required', 'string'], // "1:A,2:B,3:C,4:D,5:E,6:F,7:G" (1..7 mapped)
                ]);
                // split paragraphs by --- line
                $paragraphs = array_values(array_filter(array_map('trim', preg_split('/^\s*---\s*$/m', $validated['paragraphs_text']))));
                $headings   = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['headings_text']))));
                if (count($paragraphs) < 8) return back()->withErrors(['paragraphs_text' => 'Cần 8 đoạn, ngăn nhau bằng dòng chứa "---".'])->withInput();
                if (count($headings)   < 7) return back()->withErrors(['headings_text' => 'Cần 7 tiêu đề (mỗi tiêu đề 1 dòng).'])->withInput();

                $map = [];
                foreach (preg_split('/\s*,\s*/', trim($validated['answer_text'])) as $pair) {
                    if (!$pair) continue;
                    [$i, $h] = array_map('trim', preg_split('/\s*:\s*/', $pair));
                    if (!is_numeric($i)) continue;
                    // Tiêu đề gán chữ cái A-G theo thứ tự
                    $h = strtoupper($h);
                    if (!preg_match('/^[A-G]$/', $h)) continue;
                    $map[(int)$i] = $h;
                }
                if (count($map) < 7) {
                    return back()->withErrors(['answer_text' => 'Cần đủ 7 cặp "1:A,2:B,..." (1..7 map về A..G).'])->withInput();
                }
                $meta['paragraphs'] = $paragraphs; // 1..8
                $meta['headings']   = $headings;   // A..G
                $meta['answer']     = $map;        // {1:'A',...}
                break;

            default:
                abort(422, 'Unsupported question type.');
        }

        // Tạo question
        $question = new Question(array_merge($base, ['meta' => $meta]));
        $question->quiz()->associate($quiz);
        // order auto tăng cuối danh sách trong part
        if (empty($question->order)) {
            $max = $quiz->questions()->where('part', $part)->max('order') ?? 0;
            $question->order = $max + 1;
        }
        $question->save();

        // Nếu là dropdown/mcq_single: tạo options
        if (in_array($type, ['dropdown', 'mcq_single'], true)) {
            $opts = $request->input('options', []);
            $correct = (int) $request->input('correct_index');
            foreach ($opts as $i => $opt) {
                $o = new Option([
                    'label' => $opt['label'] ?? '',
                    'is_correct' => ($i === $correct),
                ]);
                $o->question()->associate($question);
                $o->save();
            }
        }

        return redirect()->route('admin.reading.sets.edit', ['quiz' => $quiz, 'part' => $question->part])->with('ok', 'Đã thêm câu hỏi.');
    }

    public function edit(Question $question)
    {
        $quiz = $question->quiz;
        $part = $question->part;
        $type = $question->type;
        $types = $quiz->allowedTypesForPart($part);

        abort_unless(in_array($type, $types, true), 404);

        return view('admin.questions.edit', compact('quiz', 'question', 'part', 'type'));
    }

    public function update(Request $request, Question $question)
    {
        $quiz = $question->quiz;
        $part = (int) $request->input('part');
        $type = $request->input('type');

        $allowed = $quiz->allowedTypesForPart($part);
        abort_unless(in_array($type, $allowed, true), 422);

        $base = $request->validate([
            'part' => ['required', 'integer', 'min:1', 'max:4'],
            'type' => ['required', 'string', Rule::in(config('aptis.question_types'))],
            'order' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'stem' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'audio_url' => ['nullable', 'url'],
        ]);

        $meta = [];

        switch ($type) {
            case 'dropdown':
            case 'mcq_single':
                $validated = $request->validate([
                    'options' => ['required', 'array', 'min:2'],
                    'options.*.label' => ['required', 'string'],
                    'correct_index' => ['required', 'integer', 'min:0'],
                ]);
                break;

            case 'ordering':
                $validated = $request->validate(['items_text' => ['required', 'string']]);
                $items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['items_text']))));
                if (count($items) < 5) return back()->withErrors(['items_text' => 'Cần ≥5 dòng.'])->withInput();
                $meta['items'] = $items;
                break;

            case 'matching':
                $validated = $request->validate([
                    'sources_text' => ['required', 'string'],
                    'items_text'   => ['required', 'string'],
                    'answer_text'  => ['required', 'string'],
                ]);
                $sources = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['sources_text']))));
                $items   = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['items_text']))));
                if (count($sources) < 4) return back()->withErrors(['sources_text' => 'Cần 4 dòng.'])->withInput();
                if (count($items) < 7)   return back()->withErrors(['items_text' => 'Cần 7 dòng.'])->withInput();

                $map = [];
                foreach (preg_split('/\s*,\s*/', trim($validated['answer_text'])) as $pair) {
                    if (!$pair) continue;
                    [$i, $s] = array_map('trim', preg_split('/\s*:\s*/', $pair));
                    if (!is_numeric($i)) continue;
                    $s = strtoupper($s);
                    if (!in_array($s, ['A', 'B', 'C', 'D'], true)) continue;
                    $map[(int)$i] = $s;
                }
                if (count($map) < 7) return back()->withErrors(['answer_text' => 'Cần đủ 7 cặp.'])->withInput();

                $meta['sources'] = $sources;
                $meta['items']   = $items;
                $meta['answer']  = $map;
                break;

            case 'heading_matching':
                $validated = $request->validate([
                    'paragraphs_text' => ['required', 'string'],
                    'headings_text'   => ['required', 'string'],
                    'answer_text'     => ['required', 'string'],
                ]);
                $paragraphs = array_values(array_filter(array_map('trim', preg_split('/^\s*---\s*$/m', $validated['paragraphs_text']))));
                $headings   = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['headings_text']))));
                if (count($paragraphs) < 8) return back()->withErrors(['paragraphs_text' => 'Cần 8 đoạn.'])->withInput();
                if (count($headings) < 7)   return back()->withErrors(['headings_text' => 'Cần 7 tiêu đề.'])->withInput();

                $map = [];
                foreach (preg_split('/\s*,\s*/', trim($validated['answer_text'])) as $pair) {
                    if (!$pair) continue;
                    [$i, $h] = array_map('trim', preg_split('/\s*:\s*/', $pair));
                    if (!is_numeric($i)) continue;
                    $h = strtoupper($h);
                    if (!preg_match('/^[A-G]$/', $h)) continue;
                    $map[(int)$i] = $h;
                }
                if (count($map) < 7) return back()->withErrors(['answer_text' => 'Cần đủ 7 cặp.'])->withInput();

                $meta['paragraphs'] = $paragraphs;
                $meta['headings']   = $headings;
                $meta['answer']     = $map;
                break;
        }

        $question->fill(array_merge($base, ['meta' => $meta]));
        $question->save();

        if (in_array($type, ['dropdown', 'mcq_single'], true)) {
            // update options
            $question->options()->delete();
            $opts = $request->input('options', []);
            $correct = (int) $request->input('correct_index');
            foreach ($opts as $i => $opt) {
                $o = new Option([
                    'label' => $opt['label'] ?? '',
                    'is_correct' => ($i === $correct),
                ]);
                $o->question()->associate($question);
                $o->save();
            }
        }

        return redirect()->route('admin.reading.sets.edit', ['quiz' => $quiz, 'part' => $question->part])->with('ok', 'Đã lưu câu hỏi.');
    }

    public function destroy(Question $question)
    {
        $quiz = $question->quiz;
        $question->options()->delete();
        $question->delete();
        return redirect()->route('admin.reading.sets.edit', ['quiz' => $quiz, 'part' => $question->part])->with('ok', 'Đã xoá câu hỏi.');
    }

    // Assign a question to a quiz
    public function assignQuiz(Request $request, Question $question)
    {
        $request->validate([
            'quiz_id' => 'nullable|exists:quizzes,id'
        ]);

        $quizId = $request->input('quiz_id');
        
        // If assigning to a new quiz, determine the new order
        if ($quizId) {
            $quiz = Quiz::findOrFail($quizId);
            $maxOrder = $quiz->questions()->where('part', $question->part)->max('order') ?? 0;
            $question->update([
                'quiz_id' => $quizId,
                'order' => $maxOrder + 1
            ]);
            $message = 'Question assigned to ' . $quiz->title;
        } else {
            // Remove from current quiz
            $question->update([
                'quiz_id' => null,
                'order' => 0
            ]);
            $message = 'Question removed from set';
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    // List all questions for a specific part across quizzes
    public function partIndex(Request $request, $part)
    {
        $part = (int) $part;
        abort_unless($part >= 1 && $part <= 7, 404);

        $query = Question::where('part', $part)->with('quiz')->orderBy('created_at', 'desc');
        $questions = $query->paginate(25);

        return view('admin.reading.questions.index', compact('questions', 'part'));
    }
}
