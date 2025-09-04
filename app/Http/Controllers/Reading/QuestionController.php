<?php

namespace App\Http\Controllers\Reading;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    /**
     * Hiển thị form tạo câu hỏi mới cho một bộ đề
     */
    public function create(Quiz $quiz)
    {
        // Kiểm tra quiz có phải reading quiz không
        if ($quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Bộ đề không hợp lệ');
        }
        
        return view('admin.reading.questions.create', [
            'quiz' => $quiz,
            'nextOrder' => $quiz->questions()->count() + 1
        ]);
    }
    
    /**
     * Lưu câu hỏi mới
     */
    public function store(Request $request, Quiz $quiz)
    {
        // Kiểm tra quiz có phải reading quiz không
        if ($quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Bộ đề không hợp lệ');
        }
        
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|string|in:multiple_choice,true_false,fill_blank',
            'order' => 'required|integer|min:1',
            'passage' => 'nullable|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1|max:10',
            'time_limit' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'options' => 'required|array|min:2|max:6',
            'options.*.text' => 'required|string|max:500',
            'options.*.is_correct' => 'nullable|boolean',
        ]);
        
        DB::transaction(function () use ($validated, $request, $quiz) {
            // Handle file uploads
            $imagePath = null;
            
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('questions/images', 'public');
            }
            
            // Create question
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'order' => $validated['order'],
                'passage' => $validated['passage'],
                'explanation' => $validated['explanation'],
                'points' => $validated['points'],
                'time_limit' => $validated['time_limit'] ?? 0,
                'image_path' => $imagePath,
            ]);
            
            // Create options
            foreach ($validated['options'] as $index => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['text'],
                    'is_correct' => isset($optionData['is_correct']) ? (bool)$optionData['is_correct'] : false,
                    'order' => $index + 1,
                ]);
            }
        });
        
        return redirect()->route('admin.reading.sets.edit', $quiz)
            ->with('success', 'Đã thêm câu hỏi thành công');
    }
    
    /**
     * Hiển thị form chỉnh sửa câu hỏi
     */
    public function edit(Question $question)
    {
        // Kiểm tra question thuộc reading quiz
        if (!$question->quiz || $question->quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Câu hỏi không hợp lệ');
        }
        
        return view('admin.reading.questions.edit', [
            'question' => $question,
            'quiz' => $question->quiz
        ]);
    }
    
    /**
     * Cập nhật câu hỏi
     */
    public function update(Request $request, Question $question)
    {
        // Kiểm tra question thuộc reading quiz
        if (!$question->quiz || $question->quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Câu hỏi không hợp lệ');
        }
        
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|string|in:multiple_choice,true_false,fill_blank',
            'order' => 'required|integer|min:1',
            'passage' => 'nullable|string',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1|max:10',
            'time_limit' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'options' => 'required|array|min:2|max:6',
            'options.*.text' => 'required|string|max:500',
            'options.*.is_correct' => 'nullable|boolean',
            'remove_image' => 'nullable|boolean'
        ]);
        
        DB::transaction(function () use ($validated, $request, $question) {
            // Handle file removal
            if ($request->boolean('remove_image') && $question->image_path) {
                Storage::disk('public')->delete($question->image_path);
                $question->image_path = null;
            }
            
            // Handle new file upload
            if ($request->hasFile('image')) {
                if ($question->image_path) {
                    Storage::disk('public')->delete($question->image_path);
                }
                $question->image_path = $request->file('image')->store('questions/images', 'public');
            }
            
            // Update question
            $question->update([
                'question_text' => $validated['question_text'],
                'question_type' => $validated['question_type'],
                'order' => $validated['order'],
                'passage' => $validated['passage'],
                'explanation' => $validated['explanation'],
                'points' => $validated['points'],
                'time_limit' => $validated['time_limit'] ?? 0,
                'image_path' => $question->image_path,
            ]);
            
            // Update options
            $question->options()->delete();
            
            foreach ($validated['options'] as $index => $optionData) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $optionData['text'],
                    'is_correct' => isset($optionData['is_correct']) ? (bool)$optionData['is_correct'] : false,
                    'order' => $index + 1,
                ]);
            }
        });
        
        return redirect()->route('admin.reading.sets.edit', $question->quiz_id)
            ->with('success', 'Đã cập nhật câu hỏi thành công');
    }
    
    /**
     * Xóa câu hỏi
     */
    public function destroy(Question $question)
    {
        // Kiểm tra question thuộc reading quiz
        if (!$question->quiz || $question->quiz->skill !== 'reading') {
            return redirect()->route('admin.reading.index')
                ->with('error', 'Câu hỏi không hợp lệ');
        }
        
        DB::transaction(function () use ($question) {
            // Delete files if exist
            if ($question->image_path) {
                Storage::disk('public')->delete($question->image_path);
            }
            
            // Delete related records
            $question->options()->delete();
            
            // Delete question
            $question->delete();
            
            // Reorder remaining questions
            $remainingQuestions = Question::where('quiz_id', $question->quiz_id)
                ->orderBy('order')
                ->get();
                
            foreach ($remainingQuestions as $index => $q) {
                $q->update(['order' => $index + 1]);
            }
        });
        
        $quizId = $question->quiz_id;
        
        return redirect()->route('admin.reading.sets.edit', $quizId)
            ->with('success', 'Đã xóa câu hỏi thành công');
    }
}
