@extends('layouts.app')

@section('title', $question->exists ? 'Sửa câu hỏi' : 'Thêm câu hỏi mới')

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-xl font-bold mb-4">{{ $question->exists ? 'Sửa câu hỏi' : 'Thêm câu hỏi mới' }}</h1>
                    <div class="text-sm text-gray-500 mb-3">Các trường có dấu <span class="text-red-500">*</span> là bắt
                        buộc.</div>
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

                    <form method="POST"
                        action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}"
                        class="space-y-3 max-w-2xl">
                        @csrf
                        @if($question->exists) @method('PUT') @endif


                        @php
                            $readingSetId = request('reading_set_id') ?? old('reading_set_id', $question->reading_set_id);
                            $setObj = $sets->firstWhere('id', $readingSetId);
                            $quizId = $setObj->quiz_id ?? (request('quiz_id') ?? old('quiz_id', $question->quiz_id));
                            $quizObj = $quizzes->firstWhere('id', $quizId);
                        @endphp

                        @if($readingSetId && $setObj)
                            <div class="mb-4">
                                <label class="block text-sm">Quiz</label>
                                <input type="hidden" name="quiz_id" value="{{ $quizObj->id }}">
                                <div class="p-2 bg-gray-100 rounded border border-gray-200">{{ $quizObj->title }}</div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm">Set</label>
                                <input type="hidden" name="reading_set_id" value="{{ $setObj->id }}">
                                <div class="p-2 bg-gray-100 rounded border border-gray-200">{{ $setObj->title }}</div>
                            </div>
                        @else
                            <div class="mb-4">
                                <label class="block text-sm">Quiz</label>
                                <select name="quiz_id" id="quiz_id" class="w-full border p-2 rounded">
                                    <option value="">-- Chọn quiz --</option>
                                    @foreach($quizzes as $q)
                                        <option value="{{ $q->id }}" {{ $q->id == old('quiz_id', $question->quiz_id) ? 'selected' : '' }}>{{ $q->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm">Set</label>
                                <select name="reading_set_id" id="reading_set_id" class="w-full border p-2 rounded">
                                    <option value="">-- Chọn set --</option>
                                    @foreach($sets as $s)
                                        <option value="{{ $s->id }}" data-quiz="{{ $s->quiz_id }}" style="display:none;" {{ $s->id == old('reading_set_id', $question->reading_set_id) ? 'selected' : '' }}>
                                            {{ $s->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="block text-sm">Stem / Title</label>
                            <textarea name="stem" rows="4" class="w-full border p-2 rounded">{{ old('stem', $question->stem ?? $question->title) }}</textarea>
                        </div>

                        {{-- Loại câu hỏi và Thứ tự đã được loại bỏ theo yêu cầu --}}

                        <div class="mb-6">
                            <label class="block text-sm font-semibold mb-2">Nội dung đoạn văn (5 câu, mỗi câu chứa [BLANKx])</label>
                            @php
                                $paragraphs = old('paragraphs', $question->metadata['paragraphs'] ?? []);
                            @endphp
                            @php
                                $paragraphPlaceholders = [
                                    'Take the bus to the main [BLANK1].',
                                    'The bus [BLANK2] are near my house.',
                                    'My house is [BLANK3], you can easily recognize it by the color.',
                                    'I cook eggs for a quick [BLANK4].',
                                    'After dinner, we will watch [BLANK5] on TV.'
                                ];
                            @endphp
                            @for($i = 0; $i < 5; $i++)
                                <input type="text" name="paragraphs[]" class="w-full border p-2 rounded mb-2" placeholder="{{ $paragraphPlaceholders[$i] }}" value="{{ $paragraphs[$i] ?? '' }}" required />
                            @endfor
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold mb-2">Lựa chọn cho từng chỗ trống (3 lựa chọn/blank, chọn đáp án đúng)</label>
                            <i>Chọn đáp án đúng cho từng chỗ trống</i>
                            @php
                                $choices = old('choices', $question->metadata['choices'] ?? []);
                                $correct_answers = old('correct_answers', $question->metadata['correct_answers'] ?? []);
                            @endphp

                            <div class="overflow-x-auto">
                                <table class="min-w-full border">
                                    <thead>
                                        <tr>
                                            <th class="border px-2 py-1">Blank</th>
                                            <th class="border px-2 py-1">Lựa chọn 1</th>
                                            <th class="border px-2 py-1">Lựa chọn 2</th>
                                            <th class="border px-2 py-1">Lựa chọn 3</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($i = 0; $i < 5; $i++)
                                            <tr>
                                                <td class="border px-2 py-1 font-semibold">BLANK{{ $i + 1 }}</td>
                                                @for($j = 0; $j < 3; $j++)
                                                    <td class="border px-2 py-1">
                                                        <div class="flex items-center space-x-2">
                                                            <input
                                                                type="text"
                                                                name="choices[{{ $i }}][{{ $j }}]"
                                                                class="w-full border p-1 rounded choice-input"
                                                                data-blank="{{ $i }}"
                                                                data-choice="{{ $j }}"
                                                                value="{{ $choices[$i][$j] ?? '' }}"
                                                                required
                                                            />
                                                            <input
                                                                type="radio"
                                                                name="correct_answers[{{ $i }}]"
                                                                class="correct-radio"
                                                                data-blank="{{ $i }}"
                                                                data-choice="{{ $j }}"
                                                                value="{{ $choices[$i][$j] ?? '' }}"
                                                                @if(isset($correct_answers[$i]) && $correct_answers[$i] == ($choices[$i][$j] ?? '')) checked @endif
                                                                required
                                                            >
                                                        </div>
                                                    </td>
                                                @endfor
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-semibold mb-2">Preview</label>
                            <div id="livePreview" class="bg-gray-50 rounded p-4"></div>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            <a href="{{ route('admin.quizzes.questions') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Huỷ
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                Lưu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Scripts --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ----- Lọc Set theo Quiz -----
        const quizSelect = document.getElementById('quiz_id');
        const setSelect  = document.getElementById('reading_set_id');

        if (quizSelect && setSelect) {
            function filterSets() {
                const quizId = quizSelect.value;
                Array.from(setSelect.options).forEach(function (opt) {
                    if (!opt.value) return opt.style.display = '';
                    if (!quizId) {
                        opt.style.display = 'none';
                    } else if (opt.getAttribute('data-quiz') === quizId) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });
                // Nếu set đang chọn không thuộc quiz thì reset
                if (setSelect.selectedOptions.length && setSelect.selectedOptions[0].style.display === 'none') {
                    setSelect.value = '';
                }
            }
            quizSelect.addEventListener('change', filterSets);
            filterSets();
        }

        // ----- Preview -----
        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text || '').replace(/[&<>"']/g, m => map[m]);
        }

        function getAnswerText(blankIndex) {
            const checked = document.querySelector(`input[type="radio"][name="correct_answers[${blankIndex}]"]:checked`);
            if (!checked) return '';
            const b = checked.dataset.blank;
            const c = checked.dataset.choice;
            const input = document.querySelector(`input.choice-input[data-blank="${b}"][data-choice="${c}"]`);
            return (input && input.value) ? input.value : (checked.value || '');
        }

        function updatePreview() {
            const paras = Array.from(document.querySelectorAll('input[name="paragraphs[]"]')).map(i => i.value || '');
            let html = '';
            for (let i = 0; i < 5; i++) {
                const ans = getAnswerText(i);
                let safe = escapeHtml(paras[i]);
                const token = `[BLANK${i+1}]`;
                // thay đúng token BLANKi+1 duy nhất
                safe = safe.replace(token, `<span class="text-blue-600 font-bold">${escapeHtml(ans)}</span>`);
                html += `<div class="mb-2"><span class="font-semibold">Câu ${i+1}:</span> ${safe}</div>`;
            }
            document.getElementById('livePreview').innerHTML = html;
        }

        // Event delegation cho mọi input thay đổi
        document.addEventListener('input', function (e) {
            if (e.target.matches('.choice-input')) {
                const blank = e.target.dataset.blank;
                const choice = e.target.dataset.choice;
                const radio = document.querySelector(`input[type="radio"][name="correct_answers[${blank}]"][data-choice="${choice}"]`);
                if (radio) radio.value = e.target.value;
                updatePreview();
            } else if (e.target.matches('input[name="paragraphs[]"]')) {
                updatePreview();
            }
        });

        document.addEventListener('change', function (e) {
            if (e.target.matches('input[type="radio"][name^="correct_answers"]')) {
                updatePreview();
            }
        });

        // Validate trước khi submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            for (let i = 0; i < 5; i++) {
                const checked = document.querySelector(`input[type="radio"][name="correct_answers[${i}]"]:checked`);
                const ans = getAnswerText(i).trim();
                if (!checked || !ans) {
                    alert('Bạn phải chọn đáp án và điền text cho từng BLANK (1–5).');
                    e.preventDefault();
                    return;
                }
            }
        });

        // Khởi tạo preview ban đầu
        updatePreview();
    });
    </script>
@endsection
