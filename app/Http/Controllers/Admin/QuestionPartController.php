<?php


namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\ReadingSet;

class QuestionPartController extends Controller
{
    // Reading Part 1
    public function createReadingPart1()
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        return view('admin.quizzes.question_form', ['question' => new Question(), 'quizzes' => $quizzes, 'sets' => $sets]);
    }

    public function storeReadingPart1(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'paragraphs' => 'required|array|size:5',
            'paragraphs.*' => 'required|string',
            'choices' => 'required|array|size:5',
            'choices.*' => 'required|array|size:3',
            'correct_answers' => 'required|array|size:5',
            'correct_answers.*' => 'required|string',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề câu hỏi',
            'paragraphs.required' => 'Nhập đủ 5 câu đoạn văn',
            'choices.required' => 'Nhập đủ 3 lựa chọn cho mỗi chỗ trống',
            'correct_answers.required' => 'Chọn đáp án đúng cho mỗi chỗ trống',
        ]);
        $data['type'] = 'reading_gap_filling';
        $metadata = [
            'choices' => $data['choices'],
            'blank_keys' => ['BLANK1', 'BLANK2', 'BLANK3', 'BLANK4', 'BLANK5'],
            'paragraphs' => $data['paragraphs'],
            'correct_answers' => $data['correct_answers'],
        ];
        $question = new Question();
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 1;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi thành công');
    }

    public function editReadingPart1(Question $question)
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        return view('admin.quizzes.question_form', ['question' => $question, 'quizzes' => $quizzes, 'sets' => $sets]);
    }

    public function updateReadingPart1(Request $request, Question $question)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'paragraphs' => 'required|array|size:5',
            'paragraphs.*' => 'required|string',
            'choices' => 'required|array|size:5',
            'choices.*' => 'required|array|size:3',
            'correct_answers' => 'required|array|size:5',
            'correct_answers.*' => 'required|string',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề câu hỏi',
            'paragraphs.required' => 'Nhập đủ 5 câu đoạn văn',
            'choices.required' => 'Nhập đủ 3 lựa chọn cho mỗi chỗ trống',
            'correct_answers.required' => 'Chọn đáp án đúng cho mỗi chỗ trống',
        ]);
        $data['type'] = 'reading_gap_filling';
        $metadata = [
            'choices' => $data['choices'],
            'blank_keys' => ['BLANK1', 'BLANK2', 'BLANK3', 'BLANK4', 'BLANK5'],
            'paragraphs' => $data['paragraphs'],
            'correct_answers' => $data['correct_answers'],
        ];
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 1;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi thành công');
    }

    public function destroyReadingPart1(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Question removed');
    }

    // TODO: Add more part methods (ReadingPart2, ListeningPart1, etc.)

    // Reading Part 2
    public function createReadingPart2()
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        return view('admin.quizzes.question_form_part2', ['question' => new Question(), 'quizzes' => $quizzes, 'sets' => $sets]);
    }

    public function storeReadingPart2(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'sentences' => 'required|array|min:4',
            'sentences.*' => 'required|string',
            'display_order' => 'required|string',
            'correct_order' => 'required|string',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề',
            'sentences.required' => 'Nhập ít nhất 4 câu',
            'sentences.*.required' => 'Không được để trống câu',
            'display_order.required' => 'Kéo thả để trộn thứ tự hiển thị',
            'correct_order.required' => 'Thiết lập thứ tự đúng',
        ]);
        $data['type'] = 'reading_notice_matching';
        $displayOrder = array_map('intval', array_map('trim', explode(',', $data['display_order'])));
        $sentences = $data['sentences'];
        $correctOrder = $displayOrder;
        // Log debug
        $metadata = [
            'sentences' => $sentences,
            'correct_order' => $correctOrder,
            'display_order' => $displayOrder,
        ];
        $question = new Question();
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 2;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi part 2 thành công');
    }

    public function editReadingPart2(Question $question)
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        return view('admin.quizzes.question_form_part2', ['question' => $question, 'quizzes' => $quizzes, 'sets' => $sets]);
    }

    public function updateReadingPart2(Request $request, Question $question)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'sentences' => 'required|array|min:4',
            'sentences.*' => 'required|string',
            'display_order' => 'required|string',
            'correct_order' => 'required|string',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề',
            'sentences.required' => 'Nhập ít nhất 4 câu',
            'sentences.*.required' => 'Không được để trống câu',
            'display_order.required' => 'Kéo thả để trộn thứ tự hiển thị',
            'correct_order.required' => 'Thiết lập thứ tự đúng',
        ]);
        $data['type'] = 'reading_notice_matching';
        $displayOrder = array_map('intval', array_map('trim', explode(',', $data['display_order'])));
        $sentences = $data['sentences'];
        $correctOrder = $displayOrder;
        // Log debug
        $metadata = [
            'sentences' => $sentences,
            'correct_order' => $correctOrder,
            'display_order' => $displayOrder,
        ];
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 2;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi part 2 thành công');
    }

    public function destroyReadingPart2(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Xoá câu hỏi part 2 thành công');
    }

    public function createReadingPart3()
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        $quizId = request('quiz_id');
        $setId = request('reading_set_id');
        $setObj = $sets->where('id', $setId)->first();
        $quizObj = $setObj ? $quizzes->where('id', $setObj->quiz_id)->first() : null;
        $quizTitle = $quizObj ? $quizObj->title : '---';
        $setTitle = $setObj ? $setObj->title : '---';
        return view('admin.quizzes.question_form_part3', [
            'question' => new Question(),
            'quizzes' => $quizzes,
            'sets' => $sets,
            'quizTitle' => $quizTitle,
            'setTitle' => $setTitle,
        ]);
    }

    public function storeReadingPart3(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.text' => 'required|string',
            'items.*.label' => 'required|string',
            'options' => 'required|array|min:1',
            'options.*' => 'required|string',
            'answers' => 'required|array',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề',
            'items.required' => 'Nhập ít nhất 1 đoạn văn',
            'items.*.text.required' => 'Không được để trống đoạn văn',
            'items.*.label.required' => 'Không được để trống label',
            'options.required' => 'Nhập ít nhất 1 option',
            'options.*.required' => 'Không được để trống option',
            'answers.required' => 'Phải nhập đáp án cho từng label',
        ]);
        $data['type'] = 'reading_paragraph_matching';
        // Ép kiểu answers về int
        $answers = [];
        foreach ($data['answers'] as $label => $arr) {
            $answers[$label] = array_map('intval', (array)$arr);
        }
        $metadata = [
            'items' => $data['items'],
            'options' => $data['options'],
            'answers' => $answers,
        ];
        $question = new Question();
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 3;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi part 3 thành công');
    }

    public function editReadingPart3(Question $question)
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        $quizId = $question->quiz_id;
        $setId = $question->reading_set_id;
        $setObj = $sets->where('id', $setId)->first();
        $quizObj = $setObj ? $quizzes->where('id', $setObj->quiz_id)->first() : null;
        $quizTitle = $quizObj ? $quizObj->title : '---';
        $setTitle = $setObj ? $setObj->title : '---';
        return view('admin.quizzes.question_form_part3', [
            'question' => $question,
            'quizzes' => $quizzes,
            'sets' => $sets,
            'quizTitle' => $quizTitle,
            'setTitle' => $setTitle,
        ]);
    }

    public function updateReadingPart3(Request $request, Question $question)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'items' => 'required|array|min:1',
            'items.*.text' => 'required|string',
            'items.*.label' => 'required|string',
            'options' => 'required|array|min:1',
            'options.*' => 'required|string',
            'answers' => 'required|array',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề',
            'items.required' => 'Nhập ít nhất 1 đoạn văn',
            'items.*.text.required' => 'Không được để trống đoạn văn',
            'items.*.label.required' => 'Không được để trống label',
            'options.required' => 'Nhập ít nhất 1 option',
            'options.*.required' => 'Không được để trống option',
            'answers.required' => 'Phải nhập đáp án cho từng label',
        ]);
        $data['type'] = 'reading_paragraph_matching';
        // Ép kiểu answers về int
        $answers = [];
        foreach ($data['answers'] as $label => $arr) {
            $answers[$label] = array_map('intval', (array)$arr);
        }
        $metadata = [
            'items' => $data['items'],
            'options' => $data['options'],
            'answers' => $answers,
        ];
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 3;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi part 3 thành công');
    }

    public function destroyReadingPart3(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Xoá câu hỏi part 3 thành công');
    }

    // Reading Part 4
    public function createReadingPart4()
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        $quizId = request('quiz_id');
        $setId = request('reading_set_id');
        $setObj = $sets->where('id', $setId)->first();
        $quizObj = $setObj ? $quizzes->where('id', $setObj->quiz_id)->first() : null;
        $quizTitle = $quizObj ? $quizObj->title : '---';
        $setTitle = $setObj ? $setObj->title : '---';
        return view('admin.quizzes.question_form_part4', [
            'question' => new Question(),
            'quizzes' => $quizzes,
            'sets' => $sets,
            'quizTitle' => $quizTitle,
            'setTitle' => $setTitle,
        ]);
    }

    public function storeReadingPart4(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'paragraphs' => 'required|array|size:7',
            'paragraphs.*' => 'required|string',
            'options' => 'required|array|size:7',
            'options.*' => 'required|string',
            'correct' => 'required|array|size:7',
            'correct.*' => 'required|integer',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề',
            'paragraphs.required' => 'Nhập đủ 7 đoạn văn',
            'options.required' => 'Nhập đủ 7 heading',
            'correct.required' => 'Mapping đủ 7 đáp án',
        ]);
        $data['type'] = 'reading_heading_matching';
        // Ép kiểu correct về int
        $correct = array_map('intval', $data['correct']);
        $metadata = [
            'paragraphs' => $data['paragraphs'],
            'options' => $data['options'],
            'correct' => $correct,
        ];
        $question = new Question();
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 4;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi part 4 thành công');
    }

    public function editReadingPart4(Question $question)
    {
        $quizzes = Quiz::orderBy('title')->get();
        $sets = ReadingSet::orderBy('title')->get();
        $quizId = $question->quiz_id;
        $setId = $question->reading_set_id;
        $setObj = $sets->where('id', $setId)->first();
        $quizObj = $setObj ? $quizzes->where('id', $setObj->quiz_id)->first() : null;
        $quizTitle = $quizObj ? $quizObj->title : '---';
        $setTitle = $setObj ? $setObj->title : '---';
        return view('admin.quizzes.question_form_part4', [
            'question' => $question,
            'quizzes' => $quizzes,
            'sets' => $sets,
            'quizTitle' => $quizTitle,
            'setTitle' => $setTitle,
        ]);
    }

    public function updateReadingPart4(Request $request, Question $question)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'required|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'paragraphs' => 'required|array|size:7',
            'paragraphs.*' => 'required|string',
            'options' => 'required|array|size:7',
            'options.*' => 'required|string',
            'correct' => 'required|array|size:7',
            'correct.*' => 'required|integer',
        ], [
            'quiz_id.required' => 'Vui lòng chọn quiz',
            'reading_set_id.required' => 'Vui lòng chọn set',
            'stem.required' => 'Nhập tiêu đề',
            'paragraphs.required' => 'Nhập đủ 7 đoạn văn',
            'options.required' => 'Nhập đủ 7 heading',
            'correct.required' => 'Mapping đủ 7 đáp án',
        ]);
        $data['type'] = 'reading_heading_matching';
        $correct = array_map('intval', $data['correct']);
        $metadata = [
            'paragraphs' => $data['paragraphs'],
            'options' => $data['options'],
            'correct' => $correct,
        ];
        $question->quiz_id = $data['quiz_id'];
        $question->reading_set_id = $data['reading_set_id'];
        $question->stem = $data['stem'];
        $question->type = $data['type'];
        $question->order = $data['order'] ?? 1;
        $question->skill = 'reading';
        $question->part = 4;
        $question->metadata = $metadata;
        $question->save();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi part 4 thành công');
    }

    // Listening Part 1
    public function createListeningPart1()
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();

        // Nếu có request('reading_set_id') và chưa có old('quiz_id'), tự động lấy quiz_id của set đó
        $defaultQuizId = null;
        $readingSetId = request('reading_set_id');
        if ($readingSetId && !old('quiz_id')) {
            $setObj = $sets->where('id', $readingSetId)->first();
            if ($setObj) {
                $defaultQuizId = $setObj->quiz_id;
                // inject vào request để old('quiz_id') lấy được
                request()->merge(['quiz_id' => $defaultQuizId]);
            }
        }

        return view('admin.questions.listening.part1_form', [
            'question' => new Question(['skill' => 'listening', 'part' => 1]),
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function storeListeningPart1(Request $request)
    {
        $data = $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'reading_set_id' => 'nullable|exists:sets,id',
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'description' => 'nullable|string',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
            'correct_index' => 'required|integer|min:0',
            'audio' => 'nullable|file|mimes:mp3,wav',
        ]);
        // Xử lý upload audio
        $audioPath = null;
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store('listening/part1/set1/', 'public');
        }

        $metadata = [
            'stem' => $data['stem'],
            'description' => $data['description'] ?? '',
            'audio' => $audioPath,
            'options' => $data['options'],
            'correct_index' => $data['correct_index'],
        ];

        $question = Question::create([
            'quiz_id' => $data['quiz_id'],
            'reading_set_id' => $data['reading_set_id'] ?? null,
            'stem' => $data['stem'],
            'skill' => 'listening',
            'part' => 1,
            'type' => 'single_choice',
            'order' => $data['order'] ?? 1,
            'audio_path' => $audioPath,
            'metadata' => $metadata,
        ]);

        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi Listening Part 1 thành công!');
    }

    public function editListeningPart1(Question $question)
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        // Đảm bảo quiz hiện tại luôn có trong danh sách (nếu không phải listening)
        if ($question->quiz && !$quizzes->contains('id', $question->quiz_id)) {
            $quizzes->push($question->quiz);
        }
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();
        // Đảm bảo set hiện tại luôn có trong danh sách
        if ($question->readingSet && !$sets->contains('id', $question->reading_set_id)) {
            $sets->push($question->readingSet);
        }
        return view('admin.questions.listening.part1_form', [
            'question' => $question,
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function updateListeningPart1(Request $request, Question $question)
    {
        $data = $request->validate([
            'stem' => 'required|string',
            'order' => 'nullable|integer',
            'description' => 'nullable|string',
            'correct_index' => 'required|integer|min:0',
            'audio' => 'nullable|file|mimes:mp3,wav',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string',
        ]);

        $audioPath = $question->audio_path;
        if ($request->hasFile('audio')) {
            if ($audioPath) {
                Storage::disk('public')->delete($audioPath);
            }
            $file = $request->file('audio');
            $uniqueName = md5(uniqid() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $audioPath = $file->storeAs('listening/part1/set1/', $uniqueName, 'public');
        }

        $metadata = [
            'description' => $data['description'] ?? '',
            'options' => $data['options'],
            'correct_index' => $data['correct_index'],
            'audio' => $audioPath,
        ];

        $question->update([
            'stem' => $data['stem'],
            'order' => $data['order'] ?? $question->order,
            'audio_path' => $audioPath,
            'metadata' => $metadata,
        ]);

        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi Listening Part 1 thành công!');
    }

    public function destroyListeningPart1(Question $question)
    {
        if ($question->audio_path) {
            Storage::disk('public')->delete($question->audio_path);
        }
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Đã xóa câu hỏi Listening Part 1!');
    }

    // Listening Part 2
    public function createListeningPart2()
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();
        $defaultQuizId = null;
        $readingSetId = request('reading_set_id');
        if ($readingSetId && !old('quiz_id')) {
            $setObj = $sets->where('id', $readingSetId)->first();
            if ($setObj) {
                $defaultQuizId = $setObj->quiz_id;
                request()->merge(['quiz_id' => $defaultQuizId]);
            }
        }
        return view('admin.questions.listening.part2_form', [
            'question' => new Question(['skill' => 'listening', 'part' => 2]),
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function storeListeningPart2(Request $request)
    {
        try {
            $data = $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'reading_set_id' => 'nullable|exists:sets,id',
                'stem' => 'required|string',
                'order' => 'nullable|integer',
                'description' => 'nullable|string',
                'options' => 'required|array|min:2',
                'options.*' => 'required|string',
                'answers' => 'required|array|min:1',
                'answers.*' => 'required|integer',
                'speakers' => 'required|array|min:1',
                'speakers.*.label' => 'required|string',
                'speakers.*.audio' => 'nullable|string',
                'speakers.*.description' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }

        // Xử lý upload audio cho từng speaker
        $speakers = $data['speakers'];
        foreach ($speakers as $i => &$sp) {
            if ($request->hasFile("speakers.$i.audio_file")) {
                $file = $request->file("speakers.$i.audio_file");
                $uniqueName = md5(uniqid() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                $sp['audio'] = $file->storeAs('listening/part2/set2/', $uniqueName, 'public');
            }
            // Nếu không upload thì giữ nguyên đường dẫn nhập vào
        }
        unset($sp);

        $metadata = [
            'description' => $data['description'] ?? '',
            'options' => $data['options'],
            'answers' => $data['answers'],
            'speakers' => $speakers,
        ];

        $question = Question::create([
            'quiz_id' => $data['quiz_id'],
            'reading_set_id' => $data['reading_set_id'] ?? null,
            'stem' => $data['stem'],
            'skill' => 'listening',
            'part' => 2,
            'type' => 'multi_speaker',
            'order' => $data['order'] ?? 1,
            'metadata' => $metadata,
        ]);

        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi Listening Part 2 thành công!');
    }

    public function editListeningPart2(Question $question)
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();
        return view('admin.questions.listening.part2_form', [
            'question' => $question,
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function updateListeningPart2(Request $request, Question $question)
    {
        try {
            $data = $request->validate([
                'stem' => 'required|string',
                'order' => 'nullable|integer',
                'description' => 'nullable|string',
                'options' => 'required|array|min:2',
                'options.*' => 'required|string',
                'answers' => 'required|array|min:1',
                'answers.*' => 'required|integer',
                'speakers' => 'required|array|min:1',
                'speakers.*.label' => 'required|string',
                'speakers.*.audio' => 'nullable|string',
                'speakers.*.description' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }

        // Xử lý upload audio cho từng speaker
        $speakers = $data['speakers'];
        foreach ($speakers as $i => &$sp) {
            if ($request->hasFile("speakers.$i.audio_file")) {
                $file = $request->file("speakers.$i.audio_file");
                $uniqueName = md5(uniqid() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
                $sp['audio'] = $file->storeAs('listening/part2/set/2/', $uniqueName, 'public');
            }
            // Nếu không upload thì giữ nguyên đường dẫn nhập vào
        }
        unset($sp);

        $metadata = [
            'description' => $data['description'] ?? '',
            'options' => $data['options'],
            'answers' => $data['answers'],
            'speakers' => $speakers,
        ];

        $question->update([
            'stem' => $data['stem'],
            'order' => $data['order'] ?? $question->order,
            'metadata' => $metadata,
        ]);

        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi Listening Part 2 thành công!');
    }

    public function destroyListeningPart2(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Đã xóa câu hỏi Listening Part 2!');
    }

    // Listening Part 3
    public function createListeningPart3()
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();
        $defaultQuizId = null;
        $readingSetId = request('reading_set_id');
        if ($readingSetId && !old('quiz_id')) {
            $setObj = $sets->where('id', $readingSetId)->first();
            if ($setObj) {
                $defaultQuizId = $setObj->quiz_id;
                request()->merge(['quiz_id' => $defaultQuizId]);
            }
        }
        return view('admin.questions.listening.part3_form', [
            'question' => new Question(['skill' => 'listening', 'part' => 3]),
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function storeListeningPart3(Request $request)
    {
        try {
            $data = $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'reading_set_id' => 'nullable|exists:sets,id',
                'title' => 'required|string',
                'description' => 'nullable|string',
                'items' => 'required|array|size:4',
                'items.*' => 'required|string',
                'options' => 'required|array|size:3',
                'options.*' => 'required|string',
                'answers' => 'required|array|size:4',
                'answers.*' => 'required|integer',
                'audio' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }
        $audioPath = $data['audio'] ?? null;
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $uniqueName = md5(uniqid() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $audioPath = $file->storeAs('listening/part3/set' . ($data['reading_set_id'] ?? '0') . '/', $uniqueName, 'public');
        }
        $metadata = [
            'audio' => $audioPath,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'items' => $data['items'],
            'options' => $data['options'],
            'answers' => $data['answers'],
        ];
        $question = Question::create([
            'quiz_id' => $data['quiz_id'],
            'reading_set_id' => $data['reading_set_id'] ?? null,
            'stem' => $data['title'],
            'skill' => 'listening',
            'part' => 3,
            'type' => 'multi_matching',
            'metadata' => $metadata,
        ]);
        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi Listening Part 3 thành công!');
    }

    public function editListeningPart3(Question $question)
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();
        return view('admin.questions.listening.part3_form', [
            'question' => $question,
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function updateListeningPart3(Request $request, Question $question)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string',
                'description' => 'nullable|string',
                'items' => 'required|array|size:4',
                'items.*' => 'required|string',
                'options' => 'required|array|size:3',
                'options.*' => 'required|string',
                'answers' => 'required|array|size:4',
                'answers.*' => 'required|integer',
                'audio' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }
        $audioPath = $data['audio'] ?? $question->metadata['audio'] ?? null;
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $uniqueName = md5(uniqid() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $audioPath = $file->storeAs('listening/part3/set' . ($question->reading_set_id ?? '0') . '/', $uniqueName, 'public');
        }
        $metadata = [
            'audio' => $audioPath,
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'items' => $data['items'],
            'options' => $data['options'],
            'answers' => $data['answers'],
        ];
        $question->update([
            'stem' => $data['title'],
            'metadata' => $metadata,
        ]);
        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi Listening Part 3 thành công!');
    }

    public function destroyListeningPart3(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Đã xóa câu hỏi Listening Part 3!');
    }

    // Listening Part 4
    public function createListeningPart4()
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();
        $defaultQuizId = null;
        $readingSetId = request('reading_set_id');
        if ($readingSetId && !old('quiz_id')) {
            $setObj = $sets->where('id', $readingSetId)->first();
            if ($setObj) {
                $defaultQuizId = $setObj->quiz_id;
                request()->merge(['quiz_id' => $defaultQuizId]);
            }
        }
        return view('admin.questions.listening.part4_form', [
            'question' => new Question(['skill' => 'listening', 'part' => 4]),
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function storeListeningPart4(Request $request)
    {
        try {
            $data = $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'reading_set_id' => 'nullable|exists:sets,id',
                'audio' => 'nullable|string',
                'questions' => 'required|array|min:1',
                'questions.*.stem' => 'required|string',
                'questions.*.text' => 'nullable|string',
                'questions.*.options' => 'required|array|min:1',
                'questions.*.options.*' => 'required|string',
                'questions.*.correct_index' => 'required|integer',
                'questions.*.order' => 'nullable|integer',
                'questions.*.sub' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }
        $audioPath = $data['audio'] ?? null;
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $uniqueName = md5(uniqid() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $audioPath = $file->storeAs('listening/part4/set' . ($data['reading_set_id'] ?? '0') . '/', $uniqueName, 'public');
        }
        $metadata = [
            'stem' => $request->input('title', ''),
            'audio' => $audioPath,
            'questions' => $data['questions'],
        ];
        $question = Question::create([
            'quiz_id' => $data['quiz_id'],
            'reading_set_id' => $data['reading_set_id'] ?? null,
            'stem' => $request->input('title', ''),
            'skill' => 'listening',
            'part' => 4,
            'type' => 'single_choice',
            'metadata' => $metadata,
        ]);
        return redirect()->route('admin.quizzes.questions')->with('success', 'Tạo câu hỏi Listening Part 4 thành công!');
    }

    public function editListeningPart4(Question $question)
    {
        $quizzes = Quiz::where('skill', 'listening')->orderBy('title')->get();
        $sets = ReadingSet::where('skill', 'listening')->orderBy('title')->get();
        return view('admin.questions.listening.part4_form', [
            'question' => $question,
            'quizzes' => $quizzes,
            'sets' => $sets,
        ]);
    }

    public function updateListeningPart4(Request $request, Question $question)
    {
        try {
            $data = $request->validate([
                'audio' => 'nullable|string',
                'questions' => 'required|array|min:1',
                'questions.*.stem' => 'required|string',
                'questions.*.text' => 'nullable|string',
                'questions.*.options' => 'required|array|min:1',
                'questions.*.options.*' => 'required|string',
                'questions.*.correct_index' => 'required|integer',
                'questions.*.order' => 'nullable|integer',
                'questions.*.sub' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }
        $audioPath = $data['audio'] ?? $question->metadata['audio'] ?? null;
        if ($request->hasFile('audio_file')) {
            $file = $request->file('audio_file');
            $uniqueName = md5(uniqid() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $audioPath = $file->storeAs('listening/part4/set' . ($question->reading_set_id ?? '0') . '/', $uniqueName, 'public');
        }
        $metadata = [
            'stem' => $request->input('title', $question->metadata['title'] ?? ''),
            'audio' => $audioPath,
            'questions' => $data['questions'],
        ];
        $question->update([
            'type' => 'single_choice',
            'metadata' => $metadata,
            'stem' => $request->input('title', $question->metadata['stem'] ?? ''),
        ]);
        return redirect()->route('admin.quizzes.questions')->with('success', 'Cập nhật câu hỏi Listening Part 4 thành công!');
    }

    public function destroyListeningPart4(Question $question)
    {
        $question->delete();
        return redirect()->route('admin.quizzes.questions')->with('success', 'Đã xóa câu hỏi Listening Part 4!');
    }
}
