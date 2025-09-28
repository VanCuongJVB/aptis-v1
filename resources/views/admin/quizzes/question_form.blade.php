@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 1' : 'Thêm Reading Part 1')

@section('content')
    <div class="container mx-auto px-2 py-8">
        <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
            <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ isset($question->id) ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Reading Part 1</span> <span class="text-slate-500 text-lg font-normal">— Gap Filling</span>
            </h1>

            <p class="text-base text-slate-600 mb-5">Form quản trị <span class="font-semibold text-blue-700">Aptis Reading Part 1</span>. Token dạng <code class="bg-slate-100 px-1 rounded">[BLANK1]</code>. Các trường <span class="text-red-500">*</span> là bắt buộc.</p>

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

            @php
                $readingSetId = request('reading_set_id') ?? old('reading_set_id', $question->reading_set_id);
                $setObj = $sets->firstWhere('id', $readingSetId);
                $quizId = $setObj->quiz_id ?? (request('quiz_id') ?? old('quiz_id', $question->quiz_id));
                $quizObj = $quizzes->firstWhere('id', $quizId);

                $paragraphs = old('paragraphs', $question->metadata['paragraphs'] ?? ['', '', '', '', '']);
                $choices = old('choices', $question->metadata['choices'] ?? array_fill(0,5, array_fill(0,3, '')));
                $correct_answers = old('correct_answers', $question->metadata['correct_answers'] ?? array_fill(0,5, ''));
            @endphp

            <form method="POST" id="part1Form"
                action="{{ $question->exists ? route('admin.questions.part1.update', $question) : route('admin.questions.part1.store') }}">
                @csrf
                @if($question->exists) @method('PUT') @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Quiz</label>
                        <div class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm">{{ $quizObj->title ?? '—' }}</div>
                        <input type="hidden" name="quiz_id" value="{{ $quizObj->id ?? '' }}">
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Set</label>
                        <div class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800 font-bold shadow-sm">{{ $setObj->title ?? '—' }}</div>
                        <input type="hidden" name="reading_set_id" value="{{ $setObj->id ?? '' }}">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Stem / Title <span class="text-red-500">*</span></label>
                    <textarea name="stem" rows="3" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                        placeholder="Ví dụ: Complete the text with the correct words.">{{ old('stem', $question->stem ?? ($question->title ?? '')) }}</textarea>
                </div>

                {{-- Items: fixed 5, but nicer layout and inline badge --}}
                <div class="mb-8 space-y-4">
                    @for($i = 0; $i < 5; $i++)
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div class="grid grid-cols-12 gap-3 items-start md:items-center">
                                <div class="col-span-1 flex items-center justify-center">
                                    <div class="w-9 h-9 inline-flex items-center justify-center rounded-full bg-blue-50 text-blue-700 font-semibold border border-blue-100">C{{ $i + 1 }}</div>
                                </div>

                                <div class="col-span-11 grid grid-cols-1 md:grid-cols-8 gap-3">
                                    <div class="md:col-span-5">
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Đoạn văn (Item {{ $i+1 }}) <span class="text-red-500">*</span></label>
                                        <textarea name="paragraphs[]" rows="3" class="para-input w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-800" placeholder="Nhập câu/chữ chứa token [BLANK1]" data-index="{{ $i }}">{{ $paragraphs[$i] ?? '' }}</textarea>
                                        <div class="text-xs text-gray-400 mt-1">Sử dụng token <code class="bg-slate-100 px-1 rounded">[BLANK1]</code> để đánh dấu chỗ trống.</div>
                                    </div>

                                    <div class="md:col-span-3">
                                        <label class="block text-sm font-medium text-slate-700 mb-2">Choices & Answer <span class="text-red-500">*</span></label>
                                        <div class="space-y-2">
                                            @for($j = 0; $j < 3; $j++)
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" name="correct_answers[{{ $i }}]" class="correct-radio accent-blue-600" value="{{ $choices[$i][$j] ?? '' }}" id="r-{{ $i }}-{{ $j }}" {{ (isset($correct_answers[$i]) && $correct_answers[$i] === ($choices[$i][$j] ?? '')) ? 'checked' : '' }}>
                                                    <input type="text" name="choices[{{ $i }}][{{ $j }}]" data-item="{{ $i }}" data-choice="{{ $j }}" class="choice-input w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm" placeholder="Choice {{ $j+1 }}" value="{{ $choices[$i][$j] ?? '' }}" required>
                                                </div>
                                            @endfor
                                        </div>
                                        <div class="text-xs text-gray-400 mt-2">Nhập text choice — radio chọn đáp án. Radio value sẽ tự cập nhật theo text.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Preview --}}
                <div class="mb-8">
                    <label class="block text-base font-semibold mb-2">Preview</label>
                    <div id="livePreview" class="bg-gradient-to-br from-blue-50 to-emerald-50 rounded-2xl p-6 border border-slate-200 text-base min-h-[120px]"></div>
                </div>

                <div class="flex justify-end mt-8 gap-4">
                    <a href="{{ route('admin.quizzes.questions', ['part' => 1]) . '?quiz_id=' . ($quizObj->id ?? '') . '&reading_set_id=' . ($setObj->id ?? '') . '&q=' }}" class="inline-flex items-center px-8 py-3 bg-white border border-slate-300 rounded-2xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 transition">Quay lại</a>
                    <button type="submit" class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 border border-transparent rounded-2xl font-semibold text-base text-white shadow hover:bg-blue-700 transition">Lưu</button>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // helpers
    const escapeHtml = s => (s||'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'", '&#039;');

    // auto-resize textareas
    document.querySelectorAll('.para-input').forEach(tx => {
        const resize = () => { tx.style.height = 'auto'; tx.style.height = (tx.scrollHeight) + 'px'; };
        tx.addEventListener('input', () => { resize(); updatePreview(); });
        resize();
    });

    // sync radio values with corresponding choice text inputs
    document.querySelectorAll('.choice-input').forEach(inp => {
        inp.addEventListener('input', (e) => {
            const item = inp.dataset.item;
            const choice = inp.dataset.choice;
            // set the radio value if exists
            const radio = document.querySelector(`#r-${item}-${choice}`);
            if (radio) radio.value = inp.value;
            updatePreview();
        });
    });

    // when radio changed -> update preview
    document.querySelectorAll('.correct-radio').forEach(r => r.addEventListener('change', updatePreview));

    function getData() {
        const paragraphs = Array.from(document.querySelectorAll('textarea[name="paragraphs[]"]')).map(t => t.value || '');
        const choices = []; // choices[item][j]
        const answers = [];
        for (let i = 0; i < 5; i++) {
            choices[i] = [];
            for (let j = 0; j < 3; j++) {
                const inp = document.querySelector(`input[name="choices[${i}][${j}]"]`);
                choices[i][j] = inp ? inp.value : '';
            }
            const checked = document.querySelector(`input[name="correct_answers[${i}]"]:checked`);
            answers[i] = checked ? checked.value : '';
        }
        return { paragraphs, choices, answers };
    }

    function updatePreview() {
        const { paragraphs, answers } = getData();
        const container = document.getElementById('livePreview');
        let html = '';
        paragraphs.forEach((p, idx) => {
            if (!p) { html += `<div class="mb-2 text-gray-500"><strong>Item ${idx+1}:</strong> (empty)</div>`; return; }
            // Replace first occurrence of [BLANK1] or [BLANK] etc.
            // Use regex to find [BLANKn]
            let out = escapeHtml(p);
            const re = /\[BLANK(\d+)\]/g;
            let m;
            // Replace each occurrence sequentially by chosen answer (if any)
            while ((m = re.exec(p)) !== null) {
                const num = parseInt(m[1], 10);
                const answer = answers[idx] || '';
                const safe = answer ? `<span class="text-blue-600 font-semibold">${escapeHtml(answer)}</span>` : `<span class="text-gray-400">[BLANK${num}]</span>`;
                // replace first occurrence in out (escaped), so need to construct token escaped
                out = out.replace(`\\[BLANK${num}\\]`, safe); // (won't match)
                // simpler: replace raw [BLANKn] in unescaped original and then escape remainder
            }
            // simpler approach: do replacement on original p
            let pOut = p;
            pOut = pOut.replace(/\[BLANK\d+\]/g, () => {
                const ans = answers[idx] || '';
                return ans ? `<<::${ans}::>>` : `[blank]`;
            });
            // escape then restore markers
            pOut = escapeHtml(pOut).replace(/&lt;&lt;::/g, '<<::').replace(/::&gt;&gt;/g, '::>>');
            pOut = pOut.replace(/<<::(.*?)::>>/g, (m, g1) => `<span class="text-blue-600 font-semibold">${escapeHtml(g1)}</span>`);
            pOut = pOut.replace(/\[blank\]/g, `<span class="text-gray-400">[blank]</span>`);
            html += `<div class="mb-2"><strong>Item ${idx+1}:</strong> ${pOut}</div>`;
        });
        container.innerHTML = html;
    }

    // initial preview
    updatePreview();

    // form validation on submit
    document.getElementById('part1Form').addEventListener('submit', function (e) {
        const errors = [];
        for (let i = 0; i < 5; i++) {
            // ensure 3 choices non-empty
            const vals = [];
            for (let j = 0; j < 3; j++) {
                const inp = document.querySelector(`input[name="choices[${i}][${j}]"]`);
                vals.push(inp && inp.value.trim());
            }
            const filled = vals.filter(v => v && v.length > 0);
            if (filled.length < 3) errors.push(`Item ${i+1}: cần 3 lựa chọn không rỗng.`);
            const checked = document.querySelector(`input[name="correct_answers[${i}]"]:checked`);
            if (!checked || !checked.value.trim()) errors.push(`Item ${i+1}: chưa chọn đáp án hợp lệ.`);
        }
        if (errors.length) {
            e.preventDefault();
            alert('Vui lòng sửa trước khi lưu:\n' + errors.join('\n'));
            // scroll to top of form
            window.scrollTo({top: document.getElementById('part1Form').offsetTop - 60, behavior: 'smooth'});
        }
    });
});
</script>
@endsection
