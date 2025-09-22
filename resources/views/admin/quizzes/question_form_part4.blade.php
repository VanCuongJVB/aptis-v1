@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 4' : 'Thêm Reading Part 4')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <h2 class="text-xl font-bold mb-4">Reading Part 4 — Heading Matching</h2>

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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block font-medium mb-1">Quiz</label>
                <div class="p-2 bg-gray-50 rounded border">{{ $quizTitle ?? '---' }}</div>
                <input type="hidden" name="quiz_id" value="{{ $quizId }}">
            </div>
            <div>
                <label class="block font-medium mb-1">Set</label>
                <div class="p-2 bg-gray-50 rounded border">{{ $setTitle ?? '---' }}</div>
                <input type="hidden" name="reading_set_id" value="{{ $setId }}">
            </div>
        </div>

        <div class="mb-6">
            <label class="block font-medium mb-1">Tiêu đề (stem)</label>
            <input type="text" name="stem" class="form-input w-full border rounded p-2"
                value="{{ old('stem', $question->stem ?? '') }}">
            @error('stem')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        <input type="hidden" name="skill" value="{{ old('skill', $question->skill ?? 'reading') }}">
        <input type="hidden" name="type" value="{{ old('type', $question->type ?? 'reading_heading_matching') }}">

        {{-- PARAGRAPHS: 7 đoạn văn --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Các đoạn văn (paragraphs) — 7 đoạn</label>
            @for($i = 0; $i < 7; $i++)
                <div class="mb-3">
                    <div class="mb-1 font-semibold">Đoạn {{ $i + 1 }}</div>
                    <textarea name="paragraphs[{{ $i }}]" rows="3" class="w-full border rounded p-2"
                        placeholder="Nhập đoạn văn">{{ $metaParagraphs[$i] ?? '' }}</textarea>
                </div>
            @endfor
            @error('paragraphs')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        {{-- OPTIONS: 7 heading --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Các tiêu đề (headings) — 7 heading</label>
            @for($i = 0; $i < 7; $i++)
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 text-right text-gray-500">{{ $i + 1 }}.</div>
                    <input type="text" name="options[{{ $i }}]" class="form-input flex-1 border rounded p-2"
                        placeholder="Heading #{{ $i + 1 }}" value="{{ $metaOptions[$i] ?? '' }}">
                </div>
            @endfor
            @error('options')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        {{-- CORRECT: mapping heading cho từng đoạn --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Mapping đáp án (chọn heading đúng cho từng đoạn)</label>
            @for($i = 0; $i < 7; $i++)
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 text-right text-gray-500">Đoạn {{ $i + 1 }}</div>
                    <select name="correct[{{ $i }}]" class="form-select border rounded p-2">
                        <option value="">-- Chọn heading --</option>
                        @for($j = 0; $j < 7; $j++)
                            <option value="{{ $j }}" {{ (string) ($metaCorrect[$i] ?? '') === (string) $j ? 'selected' : '' }}>
                                {{ $metaOptions[$j] ?? ("Heading #" . ($j + 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
            @endfor
            @error('correct')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        {{-- PREVIEW UI --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Preview mapping đoạn văn &rarr; heading</label>
            <div id="ui-preview" class="bg-gray-50 rounded p-4 text-sm">
                <table class="min-w-full border text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-2 py-1 border">#</th>
                            <th class="px-2 py-1 border">Đoạn văn</th>
                            <th class="px-2 py-1 border">Heading đã chọn</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < 7; $i++)
                            <tr>
                                <td class="px-2 py-1 border text-center">{{ $i + 1 }}</td>
                                <td class="px-2 py-1 border">{{ $metaParagraphs[$i] ?? '' }}</td>
                                <td class="px-2 py-1 border">
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

        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Lưu</button>
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