@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 1' : 'Thêm Reading Part 1')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg border border-slate-200 p-8">
            <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
                {{ isset($question->id) ? 'Sửa' : 'Tạo' }} Reading Part 1 — Gap Filling
                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 border border-blue-200 ml-2">
                    {{ isset($setObj) ? ucfirst($setObj->skill) : (isset($question->skill) ? ucfirst($question->skill) : '-') }}
                </span>
            </h1>

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

            <form method="POST"
                action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}"
                class="space-y-3 w-full">
                @csrf
                @if($question->exists) @method('PUT') @endif


                @php
                    $readingSetId = request('reading_set_id') ?? old('reading_set_id', $question->reading_set_id);
                    $setObj = $sets->firstWhere('id', $readingSetId);
                    $quizId = $setObj->quiz_id ?? (request('quiz_id') ?? old('quiz_id', $question->quiz_id));
                    $quizObj = $quizzes->firstWhere('id', $quizId);
                @endphp

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Quiz</label>
                        <input type="hidden" name="quiz_id" value="{{ $quizObj->id }}">
                        <div class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-blue-700 font-semibold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">{{ $quizObj->title }}</div>
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Set</label>
                        <input type="hidden" name="reading_set_id" value="{{ $setObj->id }}">
                        <div class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-emerald-700 font-semibold shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">{{ $setObj->title }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Stem / Title <span class="text-red-500">*</span></label>
                    <textarea name="stem" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">{{ old('stem', $question->stem ?? $question->title) }}</textarea>
                </div>

                <div class="mb-8">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Nội dung đoạn văn (5 câu, mỗi câu chứa <span class="text-blue-600">[BLANKx]</span>) <span class="text-red-500">*</span></label>
                    @php
                        $paragraphs = old('paragraphs', $question->metadata['paragraphs'] ?? []);
                        $paragraphPlaceholders = [
                            'Take the bus to the main [BLANK1].',
                            'The bus [BLANK2] are near my house.',
                            'My house is [BLANK3], you can easily recognize it by the color.',
                            'I cook eggs for a quick [BLANK4].',
                            'After dinner, we will watch [BLANK5] on TV.'
                        ];
                    @endphp
                    <div class="grid grid-cols-1 gap-2">
                        @for($i = 0; $i < 5; $i++)
                            <input type="text" name="paragraphs[]" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" placeholder="{{ $paragraphPlaceholders[$i] }}" value="{{ $paragraphs[$i] ?? '' }}" required />
                        @endfor
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-base font-semibold mb-2">Lựa chọn cho từng chỗ trống (3 lựa chọn/blank, chọn đáp án đúng) <span class="text-red-500">*</span></label>
                    <i class="block mb-2 text-xs text-gray-500">Chọn đáp án đúng cho từng chỗ trống</i>
                    @php
                        $choices = old('choices', $question->metadata['choices'] ?? []);
                        $correct_answers = old('correct_answers', $question->metadata['correct_answers'] ?? []);
                    @endphp
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-full w-full border rounded-xl overflow-hidden text-base">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="border px-4 py-3 font-semibold text-slate-700">Blank</th>
                                    <th class="border px-4 py-3 font-semibold text-slate-700">Lựa chọn 1</th>
                                    <th class="border px-4 py-3 font-semibold text-slate-700">Lựa chọn 2</th>
                                    <th class="border px-4 py-3 font-semibold text-slate-700">Lựa chọn 3</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 5; $i++)
                                    <tr class="even:bg-slate-50/60">
                                        <td class="border px-4 py-3 font-semibold text-blue-700">BLANK{{ $i + 1 }}</td>
                                        @for($j = 0; $j < 3; $j++)
                                            <td class="border px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <input
                                                        type="text"
                                                        name="choices[{{ $i }}][{{ $j }}]"
                                                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 choice-input"
                                                        data-blank="{{ $i }}"
                                                        data-choice="{{ $j }}"
                                                        value="{{ $choices[$i][$j] ?? '' }}"
                                                        required
                                                    />
                                                    <input
                                                        type="radio"
                                                        name="correct_answers[{{ $i }}]"
                                                        class="correct-radio accent-blue-600"
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
                    <label class="block text-base font-semibold mb-2">Preview</label>
                    <div id="livePreview" class="bg-slate-50 rounded-xl p-6 border border-slate-200 text-base"></div>
                </div>

                <div class="flex justify-end mt-6 gap-4">
                    <a href="{{ route('admin.quizzes.questions') }}" class="inline-flex items-center px-6 py-3 bg-white border border-slate-300 rounded-xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        Quay lại
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        Lưu
                    </button>
                </div>
            </form>
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
