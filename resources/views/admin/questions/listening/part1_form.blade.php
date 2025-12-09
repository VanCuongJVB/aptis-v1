@extends('layouts.app')

@section('title', $question->exists ? 'Edit Listening Part 1' : 'Create Listening Part 1')

@section('content')

<div class="container mx-auto px-2 py-8">
    <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
        <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            {{ $question->exists ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Listening Part 1</span> <span class="text-slate-500 text-lg font-normal">— Multiple Choice</span>
        </h1>

        <p class="text-base text-slate-600 mb-5">Form quản trị <span class="font-semibold text-blue-700">Aptis Listening Part 1</span> (Multiple Choice). Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</p>

        @if(session('success'))
            <div class="mb-4 p-3 bg-emerald-100 text-emerald-800 rounded-lg border border-emerald-200">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-4 p-3 bg-rose-100 text-rose-800 rounded-lg border border-rose-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data"
              action="{{ $question->exists ? route('admin.questions.listening.part1.update', $question) : route('admin.questions.listening.part1.store') }}"
              id="listenP1Form">
            @csrf
            @if($question->exists) @method('PUT') @endif

            @php
                $options = old('options', $question->metadata['options'] ?? []);
                $correct = (int) old('correct_index', $question->metadata['correct_index'] ?? 0);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-base font-semibold text-slate-700 mb-1">Quiz <span class="text-red-500">*</span></label>
                    @if(!$question->exists)
                        <select name="quiz_id" id="quiz_id_select"
                                class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" required>
                            <option value="">-- Chọn quiz --</option>
                            @foreach($quizzes as $quiz)
                                <option value="{{ $quiz->id }}" {{ old('quiz_id', request('quiz_id')) == $quiz->id ? 'selected' : '' }}>
                                    {{ $quiz->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('quiz_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                    @else
                        <div class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm">
                            {{ optional($question->quiz)->title }}
                        </div>
                        <input type="hidden" name="quiz_id" value="{{ $question->quiz_id }}" />
                    @endif
                </div>

                <div>
                    <label class="block text-base font-semibold text-slate-700 mb-1">Set <span class="text-red-500">*</span></label>
                    @if(!$question->exists)
                        <select name="reading_set_id" id="set_id_select"
                                class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800 font-bold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" required>
                            <option value="">-- Chọn set --</option>
                            @foreach($sets as $set)
                                <option value="{{ $set->id }}" {{ old('reading_set_id', request('reading_set_id')) == $set->id ? 'selected' : '' }}>
                                    {{ $set->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('reading_set_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                    @else
                        <div class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800 font-bold shadow-sm">
                            {{ optional($question->readingSet)->title }}
                        </div>
                        <input type="hidden" name="reading_set_id" value="{{ $question->reading_set_id }}" />
                    @endif
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-base font-semibold text-slate-700 mb-1">Nội dung câu hỏi (stem) <span class="text-red-500">*</span></label>
                <textarea name="stem" rows="6"
                          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                          placeholder="Nhập câu hỏi/đề bài…"
                          required>{{ old('stem', $question->stem) }}</textarea>
                @error('stem')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-base font-semibold text-slate-700 mb-1">Mô tả / ghi chú audio</label>
                <textarea name="description" rows="6"
                          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                          placeholder="Ghi chú ngắn về audio (không bắt buộc)">{{ old('description', $question->metadata['description'] ?? '') }}</textarea>
            </div>

            <div class="mb-6">
                <label class="block text-base font-semibold text-slate-700 mb-1">Audio (mp3/wav)</label>
                <input type="file" name="audio" accept="audio/mp3,audio/mpeg,audio/wav"
                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-base file:font-medium hover:file:bg-slate-200" />
                @error('audio')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror

                @if($question->audio_path)
                    <audio controls class="mt-3 w-full" crossorigin="anonymous" playsinline>
                        <source src="{{ asset('storage/' . $question->audio_path) }}" type="audio/mpeg">
                        Trình duyệt không hỗ trợ audio.
                    </audio>
                    <div class="mt-1 text-xs text-slate-500">Hiện tại: {{ $question->audio_path }}</div>
                @endif
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-2">Các lựa chọn đáp án <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @for($i = 0; $i < 3; $i++)
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-500">Đáp án {{ $i + 1 }}</label>
                            <input type="text" name="options[]" value="{{ $options[$i] ?? '' }}"
                                   class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition option-input"
                                   placeholder="Nhập đáp án {{ $i + 1 }}" required />
                        </div>
                    @endfor
                </div>
                @error('options')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-10">
                <label class="block text-base font-semibold text-slate-700 mb-1">Đáp án đúng <span class="text-red-500">*</span></label>
                <select name="correct_index" id="correct_index_select"
                        class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition" required>
                    @for($i = 0; $i < 3; $i++)
                        <option value="{{ $i }}" {{ (int)$correct === $i ? 'selected' : '' }}>
                            Đáp án {{ $i + 1 }}{{ isset($options[$i]) && $options[$i] !== '' ? ' — ' . $options[$i] : '' }}
                        </option>
                    @endfor
                </select>
                @error('correct_index')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            <input type="hidden" name="skill" value="{{ old('skill', $question->skill ?? 'listening') }}">
            <input type="hidden" name="type"  value="{{ old('type',  $question->type  ?? 'listening_part1') }}">

            <div class="flex justify-end mt-8 gap-4">
                <a href="{{ route('admin.quizzes.questions', ['part' => 1]) }}"
                    class="inline-flex items-center px-8 py-3 bg-white border border-slate-300 rounded-2xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                    <span class="px-2">Quay lại</span>
                </a>
                <button id="saveBtn" type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 border border-transparent rounded-2xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                    <svg id="saveSpinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z"/>
                    </svg>
                    <span>{{ $question->exists ? 'Cập nhật' : 'Tạo mới' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Small UX: spinner khi submit + đồng bộ label trong dropdown đáp án đúng --}}
<script>
(function(){
    const form = document.getElementById('listenP1Form');
    const saveBtn = document.getElementById('saveBtn');
    const saveSpinner = document.getElementById('saveSpinner');
    const correctSel = document.getElementById('correct_index_select');
    const optionInputs = document.querySelectorAll('.option-input');

    if (form && saveBtn && saveSpinner) {
        form.addEventListener('submit', function(){
            saveBtn.disabled = true;
            saveSpinner.classList.remove('hidden');
        }, { once: true });
    }

    function syncCorrectLabels(){
        if (!correctSel) return;
        optionInputs.forEach((inp, idx) => {
            const opt = correctSel.querySelector(`option[value="${idx}"]`);
            if (!opt) return;
            const val = (inp.value || '').trim();
            opt.textContent = `Đáp án ${idx+1}${val ? ' — ' + val : ''}`;
        });
    }

    optionInputs.forEach(inp => {
        inp.addEventListener('input', syncCorrectLabels);
        inp.addEventListener('change', syncCorrectLabels);
    });
    syncCorrectLabels();
})();
</script>
@endsection
