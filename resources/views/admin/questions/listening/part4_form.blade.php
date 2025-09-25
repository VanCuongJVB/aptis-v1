@extends('layouts.app')

@section('content')

    <div class="container mx-auto py-6">
        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
            <form method="POST" enctype="multipart/form-data"
                action="{{ isset($question->id) ? route('admin.questions.listening.part4.update', $question->id) : route('admin.questions.listening.part4.store') }}">
                @csrf
                @if(isset($question->id)) @method('PUT') @endif
                <div class="mb-4">
                    <label class="block font-medium mb-1">Tiêu đề câu hỏi</label>
                    <input type="text" name="title" class="form-input w-full"
                        value="{{ old('stem', $question->metadata['stem'] ?? '') }}" required>
                </div>
                <div class="mb-4">
                    <label class="block font-medium mb-1">Quiz</label>
                    @php
                        $selectedSetId = old('reading_set_id', request('reading_set_id') ?? ($question->reading_set_id ?? ''));
                        $autoQuizId = '';
                        if ($selectedSetId && !$question->quiz_id && !old('quiz_id')) {
                            $setObj = $sets->where('id', $selectedSetId)->first();
                            if ($setObj)
                                $autoQuizId = $setObj->quiz_id;
                        }
                        $selectedQuizId = old('quiz_id', $question->quiz_id ?? $autoQuizId);
                    @endphp
                    <select name="quiz_id" class="form-select w-full">
                        <option value="">-- Chọn Quiz --</option>
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" {{ $selectedQuizId == $quiz->id ? 'selected' : '' }}>
                                {{ $quiz->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Set</label>
                    <select name="reading_set_id" class="form-select w-full">
                        <option value="">-- Không chọn --</option>
                        @foreach($sets as $set)
                            <option value="{{ $set->id }}" {{ $selectedSetId == $set->id ? 'selected' : '' }}>{{ $set->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Audio (mp3)</label>
                    <input type="file" name="audio_file" accept="audio/mp3" class="form-input w-full mb-1">
                    @php
                        $audio = old('audio', $question->metadata['audio'] ?? '');
                        $audioSrc = '';
                        if ($audio) {
                            if (Str::startsWith($audio, ['http://', 'https://'])) {
                                $audioSrc = $audio;
                            } elseif (Str::startsWith($audio, ['/'])) {
                                $audioSrc = asset(ltrim($audio, '/'));
                            } else {
                                $audioSrc = Storage::url($audio);
                            }
                        }
                    @endphp
                    @if($audioSrc)
                        <audio controls class="w-full mb-1">
                            <source src="{{ $audioSrc }}" type="audio/mpeg">
                            Trình duyệt của bạn không hỗ trợ phát audio.
                        </audio>
                    @endif
                    <input type="hidden" name="audio" value="{{ $audio }}">
                </div>

                <div class="mb-4">
                    <label class="block font-medium mb-1">Danh sách câu hỏi</label>
                    @php
                        $questions = old('questions', $question->metadata['questions'] ?? [
                            ['stem' => '', 'text' => '', 'options' => ['', '', ''], 'correct_index' => 0, 'order' => 1, 'sub' => 'A'],
                            ['stem' => '', 'text' => '', 'options' => ['', '', ''], 'correct_index' => 0, 'order' => 2, 'sub' => 'B'],
                        ]);
                    @endphp
                    @foreach($questions as $idx => $q)
                        <div class="mb-6 border rounded p-4 bg-gray-50">
                            <div class="mb-2 font-semibold text-blue-700">Câu hỏi {{ $idx + 1 }} <span
                                    class="text-gray-500">(sub: {{ $q['sub'] ?? chr(65 + $idx) }})</span></div>
                            <div class="mb-3">
                                <label class="block mb-1">Stem</label>
                                <input type="text" name="questions[{{ $idx }}][stem]" class="form-input w-full"
                                    value="{{ $q['stem'] ?? '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="block mb-1">Text (đoạn văn)</label>
                                <textarea name="questions[{{ $idx }}][text]" class="form-textarea w-full"
                                    rows="4">{{ $q['text'] ?? '' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block mb-1">Options</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @for($o = 0; $o < 3; $o++)
                                        <input type="text" name="questions[{{ $idx }}][options][{{ $o }}]"
                                            class="form-input w-full mb-1" value="{{ $q['options'][$o] ?? '' }}" required
                                            placeholder="Option {{ $o + 1 }}">
                                    @endfor
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="block mb-1">Đáp án đúng</label>
                                <select name="questions[{{ $idx }}][correct_index]" class="form-select w-full" required>
                                    @for($o = 0; $o < 3; $o++)
                                        <option value="{{ $o }}" {{ (isset($q['correct_index']) && $q['correct_index'] == $o) ? 'selected' : '' }}>Option {{ $o + 1 }}</option>
                                    @endfor
                                </select>
                            </div>
                            <input type="hidden" name="questions[{{ $idx }}][order]" value="{{ $q['order'] ?? ($idx + 1) }}">
                            <input type="hidden" name="questions[{{ $idx }}][sub]" value="{{ $q['sub'] ?? chr(65 + $idx) }}">
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 text-center">
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded shadow">
                        <i class="bi bi-save"></i> {{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection