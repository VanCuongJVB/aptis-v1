@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 4' : 'Thêm Reading Part 4')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
            <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">{{ isset($question->id) ? 'Sửa' : 'Tạo' }} Reading Part 4 — Heading Matching</h1>

            <div class="text-sm text-gray-500 mb-3">Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</div>

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $quizId = old('quiz_id', $question->quiz_id ?? request('quiz_id'));
                $setId = old('reading_set_id', $question->reading_set_id ?? request('reading_set_id'));
                $setObj = $sets->where('id', $setId)->first();
                $quizId = $setObj ? $setObj->quiz_id : $quizId;
                // quizTitle, setTitle đã được truyền từ controller

                $metaParagraphs = old('paragraphs', $question->metadata['paragraphs'] ?? []);
                $metaOptions = old('options', $question->metadata['options'] ?? []);
                $metaCorrect = old('correct', $question->metadata['correct'] ?? []);
            @endphp

            <form method="POST"
                action="{{ isset($question->id) ? route('admin.questions.part4.update', $question) : route('admin.questions.part4.store') }}">
                @csrf
                @if(isset($question->id)) @method('PUT') @endif

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Quiz</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-blue-700 font-semibold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                            {{ $quizTitle ?? '---' }}</div>
                        <input type="hidden" name="quiz_id" value="{{ $quizId }}">
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Set</label>
                        <div class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-emerald-700 font-semibold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                            {{ $setTitle ?? '---' }}</div>
                        <input type="hidden" name="reading_set_id" value="{{ $setId }}">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Tiêu đề (stem) <span class="text-red-500">*</span></label>
                    <input type="text" name="stem"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40"
                        value="{{ old('stem', $question->stem ?? '') }}">
                    @error('stem')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <input type="hidden" name="skill" value="{{ old('skill', $question->skill ?? 'reading') }}">
                <input type="hidden" name="type" value="{{ old('type', $question->type ?? 'reading_long_text') }}">

                {{-- PARAGRAPHS: 7 đoạn văn --}}
                <div class="mb-8">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Các đoạn văn <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @for($i = 0; $i < 7; $i++)
                            <div class="mb-3">
                                <div class="mb-1 font-semibold text-blue-700">Đoạn {{ $i + 1 }}</div>
                                <textarea name="paragraphs[{{ $i }}]" rows="8"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 item-text"
                                    placeholder="Nhập đoạn văn">{{ $metaParagraphs[$i] ?? '' }}</textarea>
                            </div>
                        @endfor
                    </div>
                    @error('paragraphs')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- OPTIONS: 7 heading --}}
                <div class="mb-8">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Các tiêu đề (headings) — 7 heading <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @for($i = 0; $i < 7; $i++)
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-6 text-right text-gray-500">{{ $i + 1 }}.</div>
                                <input type="text" name="options[{{ $i }}]"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40"
                                    placeholder="Heading #{{ $i + 1 }}" value="{{ $metaOptions[$i] ?? '' }}">
                            </div>
                        @endfor
                    </div>
                    @error('options')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- CORRECT: mapping heading cho từng đoạn --}}
                <div class="mb-8">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Mapping đáp án (chọn heading đúng cho từng đoạn) <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @for($i = 0; $i < 7; $i++)
                            <div class="mb-2">
                                <span class="block text-blue-700 font-semibold mb-1">Đoạn {{ $i + 1 }}</span>
                                <select name="correct[{{ $i }}]"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                                    <option value="">-- Chọn heading --</option>
                                    @for($j = 0; $j < 7; $j++)
                                        <option value="{{ $j }}" {{ (string) ($metaCorrect[$i] ?? '') === (string) $j ? 'selected' : '' }}>
                                            {{ $metaOptions[$j] ?? ("Heading #" . ($j + 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        @endfor
                    </div>
                    @error('correct')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- PREVIEW UI --}}
                <div class="mb-8">
                    <label class="block text-base font-semibold mb-2">Preview mapping đoạn văn &rarr; heading</label>
                    <div id="ui-preview" class="bg-slate-50 rounded-xl p-6 border border-slate-200 text-base">
                        <table class="min-w-full w-full border text-base">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-4 py-3 border">#</th>
                                    <th class="px-4 py-3 border">Đoạn văn</th>
                                    <th class="px-4 py-3 border">Heading đã chọn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 7; $i++)
                                    <tr>
                                        <td class="px-4 py-3 border text-center">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 border">{{ $metaParagraphs[$i] ?? '' }}</td>
                                        <td class="px-4 py-3 border">
                                            @php
                                                $sel = $metaCorrect[$i] ?? null;
                                                $heading = ($sel !== null && isset($metaOptions[$sel])) ? $metaOptions[$sel] : '';
                                            @endphp
                                            <span>{{ $heading }}</span>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end mt-6 gap-4">
                    <a href="{{ route('admin.quizzes.questions') }}"
                        class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        Quay lại
                    </a>
                    <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        Lưu
                    </button>
                </div>
            </form>
        </div>

        <script>
            function getFormMetadata() {
                const paragraphs = Array.from(document.querySelectorAll('textarea[name^="paragraphs["]'))
                    .map(e => e.value);
                const options = Array.from(document.querySelectorAll('input[name^="options["]'))
                    .map(e => e.value);
                const correct = Array.from(document.querySelectorAll('select[name^="correct["]'))
                    .map(e => {
                        const v = e.value;
                        return v === '' ? null : parseInt(v, 10);
                    });
                return { paragraphs, options, correct };
            }

            function updateUIPreview() {
                const meta = getFormMetadata();
                let html = '<table class="min-w-full border text-sm">';
                html += '<thead><tr class="bg-gray-100">' +
                    '<th class="px-2 py-1 border">#</th>' +
                    '<th class="px-2 py-1 border">Đoạn văn</th>' +
                    '<th class="px-2 py-1 border">Heading đã chọn</th>' +
                    '</tr></thead><tbody>';
                for (let i = 0; i < 7; i++) {
                    const para = meta.paragraphs[i] || '';
                    const sel = meta.correct[i];
                    let heading = '';
                    if (sel !== null && typeof meta.options[sel] !== 'undefined') heading = meta.options[sel];
                    html += `<tr><td class='px-2 py-1 border text-center'>${i + 1}</td><td class='px-2 py-1 border'>${para}</td><td class='px-2 py-1 border'>${heading}</td></tr>`;
                }
                html += '</tbody></table>';
                document.getElementById('ui-preview').innerHTML = html;
            }

            document.addEventListener('DOMContentLoaded', function () {
                const inputs = document.querySelectorAll('textarea[name^="paragraphs["], input[name^="options["], select[name^="correct["]');
                inputs.forEach(el => {
                    el.addEventListener('input', updateUIPreview);
                    el.addEventListener('change', updateUIPreview);
                });
                updateUIPreview();
            });
        </script>
@endsection