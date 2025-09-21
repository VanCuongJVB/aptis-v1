<?php

namespace App\Http\Controllers\Admin;

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
            'blank_keys' => ['BLANK1','BLANK2','BLANK3','BLANK4','BLANK5'],
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
            'blank_keys' => ['BLANK1','BLANK2','BLANK3','BLANK4','BLANK5'],
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
}
