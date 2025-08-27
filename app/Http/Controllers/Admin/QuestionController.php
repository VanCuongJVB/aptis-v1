<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Quiz, Question, Option};

class QuestionController extends Controller
{
    public function create(Quiz $quiz){ return view('admin.questions.create', compact('quiz')); }

    public function store(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'stem'=>'required|string','type'=>'required|in:single,multi','order'=>'integer|min:1',
            'audio_path'=>'nullable|string|max:255','options'=>'required|array|min:2',
            'options.*.label'=>'required|string','options.*.is_correct'=>'nullable|boolean',
        ]);
        $question = $quiz->questions()->create([
            'stem'=>$data['stem'],'type'=>$data['type'],'order'=>$data['order'] ?? 1,'audio_path'=>$data['audio_path'] ?? null,
        ]);
        foreach ($data['options'] as $i=>$opt) {
            $question->options()->create(['label'=>$opt['label'],'is_correct'=>isset($opt['is_correct']),'order'=>$i+1]);
        }
        return redirect()->route('admin.quizzes.edit', $quiz)->with('ok','Đã thêm câu hỏi.');
    }

    public function edit(Question $question){ $question->load('quiz','options'); return view('admin.questions.edit', compact('question')); }

    public function update(Request $request, Question $question)
    {
        $data = $request->validate([
            'stem'=>'required|string','type'=>'required|in:single,multi','order'=>'integer|min:1',
            'audio_path'=>'nullable|string|max:255','options'=>'required|array|min:2',
            'options.*.id'=>'nullable|integer|exists:options,id','options.*.label'=>'required|string','options.*.is_correct'=>'nullable|boolean',
        ]);
        $question->update([
            'stem'=>$data['stem'],'type'=>$data['type'],'order'=>$data['order'] ?? 1,'audio_path'=>$data['audio_path'] ?? null,
        ]);
        $existing = $question->options()->pluck('id')->toArray();
        $kept=[]; $order=1;
        foreach ($data['options'] as $opt) {
            if (!empty($opt['id']) && in_array((int)$opt['id'], $existing)) {
                $question->options()->where('id',$opt['id'])->update(['label'=>$opt['label'],'is_correct'=>isset($opt['is_correct']),'order'=>$order++]);
                $kept[]=(int)$opt['id'];
            } else {
                $new = $question->options()->create(['label'=>$opt['label'],'is_correct'=>isset($opt['is_correct']),'order'=>$order++]);
                $kept[]=$new->id;
            }
        }
        $toDel = array_diff($existing, $kept);
        if (!empty($toDel)) $question->options()->whereIn('id',$toDel)->delete();
        return redirect()->route('admin.quizzes.edit', $question->quiz)->with('ok','Đã lưu câu hỏi.');
    }

    public function destroy(Question $question){ $quiz=$question->quiz; $question->delete(); return redirect()->route('admin.quizzes.edit', $quiz)->with('ok','Đã xoá câu hỏi.'); }
}
