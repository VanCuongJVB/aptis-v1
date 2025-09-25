@extends('layouts.app')

@section('title', $question->exists ? 'Edit Listening Part 1' : 'Create Listening Part 1')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
        {{-- Header --}}
        <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <h1 class="text-2xl font-bold flex items-center gap-3">
                {{ $question->exists ? 'Edit' : 'Create' }} Listening Part 1 — Multiple Choice
                <span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium bg-blue-50 border-blue-200 text-blue-700">
                    Listening
                </span>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700 border border-slate-200">
                    Part 1
                </span>
            </h1>
            @if(session('success'))
                <div class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-emerald-700 border border-emerald-200">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
        </div>

        <div class="text-sm text-gray-500 mb-4">
            Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800">
                <div class="font-semibold mb-1">Vui lòng kiểm tra lại:</div>
                <ul class="list-disc pl-5 space-y-1">
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
                // Giữ lại selections cũ
                $options = old('options', $question->metadata['options'] ?? []);
                $correct = (int) old('correct_index', $question->metadata['correct_index'] ?? 0);
            @endphp

            {{-- Quiz / Set --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Quiz <span class="text-red-500">*</span></label>
                    @if(!$question->exists)
                        <select name="quiz_id" id="quiz_id_select"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition
                                       focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" required>
                            <option value="">-- Chọn quiz --</option>
                            @foreach($quizzes as $quiz)
                                <option value="{{ $quiz->id }}" {{ old('quiz_id', request('quiz_id')) == $quiz->id ? 'selected' : '' }}>
                                    {{ $quiz->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('quiz_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                    @else
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm">
                            {{ optional($question->quiz)->title }}
                        </div>
                        <input type="hidden" name="quiz_id" value="{{ $question->quiz_id }}" />
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Set <span class="text-red-500">*</span></label>
                    @if(!$question->exists)
                        <select name="reading_set_id" id="set_id_select"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition
                                       focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" required>
                            <option value="">-- Chọn set --</option>
                            @foreach($sets as $set)
                                <option value="{{ $set->id }}" {{ old('reading_set_id', request('reading_set_id')) == $set->id ? 'selected' : '' }}>
                                    {{ $set->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('reading_set_id')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                    @else
                        <div class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm">
                            {{ optional($question->readingSet)->title }}
                        </div>
                        <input type="hidden" name="reading_set_id" value="{{ $question->reading_set_id }}" />
                    @endif
                </div>
            </div>

            {{-- Stem --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nội dung câu hỏi (stem) <span class="text-red-500">*</span></label>
                <textarea name="stem" rows="4"
                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition
                                 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40"
                          placeholder="Nhập câu hỏi/đề bài…"
                          required>{{ old('stem', $question->stem) }}</textarea>
                @error('stem')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Description (audio transcript/notes) --}}
            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Mô tả / ghi chú audio</label>
                <textarea name="description" rows="3"
                          class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition
                                 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40"
                          placeholder="Ghi chú ngắn về audio (không bắt buộc)">{{ old('description', $question->metadata['description'] ?? '') }}</textarea>
            </div>

            {{-- Audio upload + preview --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Audio (mp3/wav)</label>
                <input type="file" name="audio" accept="audio/mp3,audio/mpeg,audio/wav"
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition
                              file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-sm file:font-medium
                              hover:file:bg-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" />
                @error('audio')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror

                @if($question->audio_path)
                    <audio controls class="mt-3 w-full">
                        <source src="{{ asset('storage/' . $question->audio_path) }}" type="audio/mpeg">
                        Trình duyệt không hỗ trợ audio.
                    </audio>
                    <div class="mt-1 text-xs text-slate-500">Hiện tại: {{ $question->audio_path }}</div>
                @endif
            </div>

            {{-- Options --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-2">Các lựa chọn đáp án <span class="text-red-500">*</span></label>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @for($i = 0; $i < 3; $i++)
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-500">Đáp án {{ $i + 1 }}</label>
                            <input type="text" name="options[]" value="{{ $options[$i] ?? '' }}"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition
                                          focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 option-input"
                                   placeholder="Nhập đáp án {{ $i + 1 }}" required />
                        </div>
                    @endfor
                </div>
                @error('options')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Correct answer --}}
            <div class="mb-8">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Đáp án đúng <span class="text-red-500">*</span></label>
                <select name="correct_index" id="correct_index_select"
                        class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" required>
                    @for($i = 0; $i < 3; $i++)
                        <option value="{{ $i }}" {{ (int)$correct === $i ? 'selected' : '' }}>
                            Đáp án {{ $i + 1 }}{{ isset($options[$i]) && $options[$i] !== '' ? ' — ' . $options[$i] : '' }}
                        </option>
                    @endfor
                </select>
                @error('correct_index')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
            </div>

            {{-- Hidden system fields (đảm bảo backend nhận dạng đúng) --}}
            <input type="hidden" name="skill" value="{{ old('skill', $question->skill ?? 'listening') }}">
            <input type="hidden" name="type"  value="{{ old('type',  $question->type  ?? 'listening_part1') }}">

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.quizzes.questions') }}"
                   class="inline-flex items-center px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm font-semibold text-blue-700
                          shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60">
                    Quay lại
                </a>
                <button id="saveBtn" type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow
                               hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/60">
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
