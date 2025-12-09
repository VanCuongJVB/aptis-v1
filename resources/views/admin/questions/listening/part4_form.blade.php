@extends('layouts.app')

@section('content')

    <div class="container mx-auto px-2 py-8">
        <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
            <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ isset($question->id) ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Listening Part 4</span> <span class="text-slate-500 text-lg font-normal">— Multiple Questions</span>
            </h1>

            <form method="POST" enctype="multipart/form-data"
                action="{{ isset($question->id) ? route('admin.questions.listening.part4.update', $question->id) : route('admin.questions.listening.part4.store') }}">
                @csrf
                @if(isset($question->id)) @method('PUT') @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Tiêu đề câu hỏi <span class="text-red-500">*</span></label>
                        @php
                            $stemValue = old('stem', $question->metadata['stem'] ?? $question->stem ?? '');
                            if (is_array($stemValue))
                                $stemValue = reset($stemValue) ?: '';
                        @endphp
                        <input type="text" name="title" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ $stemValue }}" required>
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Quiz <span class="text-red-500">*</span></label>
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
                        <select name="quiz_id" class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm">
                            <option value="">-- Chọn Quiz --</option>
                            @foreach($quizzes as $quiz)
                                <option value="{{ $quiz->id }}" {{ $selectedQuizId == $quiz->id ? 'selected' : '' }}>
                                    {{ $quiz->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Set <span class="text-red-500">*</span></label>
                    <select name="reading_set_id" class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800 font-bold shadow-sm">
                        <option value="">-- Không chọn --</option>
                        @foreach($sets as $set)
                            <option value="{{ $set->id }}" {{ $selectedSetId == $set->id ? 'selected' : '' }}>{{ $set->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Audio (mp3)</label>
                    <input type="file" name="audio_file" accept="audio/mp3" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition mb-1">
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
                        <audio controls class="w-full mb-1" crossorigin="anonymous" playsinline>
                            <source src="{{ $audioSrc }}" type="audio/mpeg">
                            Trình duyệt của bạn không hỗ trợ phát audio.
                        </audio>
                    @endif
                    <input type="hidden" name="audio" value="{{ $audio }}">
                </div>

                <div class="mb-10">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Danh sách câu hỏi <span class="text-red-500">*</span></label>
                    @php
                        $questions = old('questions', $question->metadata['questions'] ?? [
                            ['stem' => '', 'text' => '', 'options' => ['', '', ''], 'correct_index' => 0, 'order' => 1, 'sub' => 'A'],
                            ['stem' => '', 'text' => '', 'options' => ['', '', ''], 'correct_index' => 0, 'order' => 2, 'sub' => 'B'],
                        ]);
                    @endphp
                    @foreach($questions as $idx => $q)
                        <div class="mb-6 border border-slate-200 rounded-2xl p-4 bg-slate-50">
                            <div class="mb-2 font-semibold text-blue-700">Câu hỏi {{ $idx + 1 }} <span class="text-gray-500">(sub: {{ $q['sub'] ?? chr(65 + $idx) }})</span></div>
                            <div class="mb-4">
                                <label class="block text-base font-semibold mb-1">Tiêu đề câu hỏi</label>
                                <input type="text" name="questions[{{ $idx }}][stem]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" value="{{ $q['stem'] ?? '' }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="block text-base font-semibold mb-1">Text (đoạn văn)</label>
                                <textarea name="questions[{{ $idx }}][text]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" rows="8">{{ $q['text'] ?? '' }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="block text-base font-semibold mb-1">Options</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @for($o = 0; $o < 3; $o++)
                                        <input type="text" name="questions[{{ $idx }}][options][{{ $o }}]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition mb-1" value="{{ $q['options'][$o] ?? '' }}" required placeholder="Option {{ $o + 1 }}">
                                    @endfor
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="block text-base font-semibold mb-1">Đáp án đúng</label>
                                <select name="questions[{{ $idx }}][correct_index]" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" required>
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

                <div class="flex justify-end mt-8 gap-4">
                    <a href="{{ route('admin.quizzes.questions', ['part' => 4]) }}"
                        class="inline-flex items-center px-8 py-3 bg-white border border-slate-300 rounded-2xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <span class="px-2">Quay lại</span>
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 border border-transparent rounded-2xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <span class="px-2">{{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection