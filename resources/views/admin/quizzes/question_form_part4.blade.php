@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 4' : 'Thêm Reading Part 4')

@section('content')
    <div class="container mx-auto px-2 py-8">
        <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
            <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ isset($question->id) ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Reading Part 4</span> <span class="text-slate-500 text-lg font-normal">— Heading Matching</span>
            </h1>

            <p class="text-base text-slate-600 mb-5">Form quản trị <span class="font-semibold text-blue-700">Aptis Reading Part 4</span> (Heading matching). Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</p>

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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Quiz</label>
                        <div class="w-full rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-base text-blue-800 font-bold shadow-sm">{{ $quizTitle ?? '---' }}</div>
                        <input type="hidden" name="quiz_id" value="{{ $quizId }}">
                    </div>
                    <div>
                        <label class="block text-base font-semibold text-slate-700 mb-1">Set</label>
                        <div class="w-full rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800 font-bold shadow-sm">{{ $setTitle ?? '---' }}</div>
                        <input type="hidden" name="reading_set_id" value="{{ $setId }}">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-base font-semibold text-slate-700 mb-1">Tiêu đề câu hỏi (stem) <span class="text-red-500">*</span></label>
                    @php
                        // Ưu tiên old('stem'), sau đó đến $question->stem, cuối cùng mới đến $question->metadata['stem']
                        $stemValue = old('stem');
                        if ($stemValue === null) {
                            $stemValue = $question->stem ?? null;
                        }
                        if ($stemValue === null) {
                            if (is_array($question->metadata)) {
                                $stemValue = $question->metadata['stem'] ?? '';
                            } elseif (is_object($question->metadata)) {
                                $stemValue = $question->metadata->stem ?? '';
                            } else {
                                $stemValue = '';
                            }
                        }
                        if (is_array($stemValue)) $stemValue = reset($stemValue) ?: '';
                    @endphp
                    <input type="text" name="stem"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                        value="{{ $stemValue }}"
                        placeholder="Ví dụ: Match each paragraph to the most suitable heading.">
                    <div class="text-xs text-gray-500 mt-1">Hiển thị ở trên cùng khi người thi làm bài.</div>
                    @error('stem')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                <input type="hidden" name="skill" value="{{ old('skill', $question->skill ?? 'reading') }}">
                <input type="hidden" name="type" value="{{ old('type', $question->type ?? 'reading_long_text') }}">

                {{-- COMPACT ROWS: for each of 7 items show: # - Đoạn văn - Tiêu đề - Đáp án --}}
                <div class="mb-10">
                    <label class="block text-base font-semibold text-slate-700 mb-3">Các mục (7 đoạn văn, heading, đáp án) <span class="text-red-500">*</span></label>
                    <div class="text-xs text-gray-500 mb-4">Mỗi hàng: <span class="font-semibold">Đoạn văn</span> — <span class="font-semibold">Heading</span> — <span class="font-semibold">Chọn đáp án</span>. Heading sẽ xuất hiện trong danh sách chọn cho mọi hàng.</div>
                    <div class="space-y-6">
                        @for($i = 0; $i < 7; $i++)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-start bg-slate-50 rounded-2xl p-4 border border-slate-200">
                                <div class="md:col-span-1 flex items-center justify-center mb-2 md:mb-0">
                                    <div class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold text-lg shadow">{{ $i + 1 }}</div>
                                </div>
                                <div class="md:col-span-5">
                                    <label for="paragraph-{{ $i }}" class="block text-sm font-semibold text-slate-700 mb-1">Đoạn văn <span class="text-red-500">*</span></label>
                                    <textarea id="paragraph-{{ $i }}" name="paragraphs[{{ $i }}]" rows="4"
                                        class="paragraph-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                                        placeholder="Nhập đoạn văn cho câu {{ $i + 1 }}">{{ $metaParagraphs[$i] ?? '' }}</textarea>
                                    <div class="text-xs text-gray-400 mt-1">Nên giữ độ dài trung bình (1–3 câu).</div>
                                </div>
                                <div class="md:col-span-4">
                                    <label for="heading-{{ $i }}" class="block text-sm font-semibold text-slate-700 mb-1">Heading <span class="text-red-500">*</span></label>
                                    <input id="heading-{{ $i }}" type="text" name="options[{{ $i }}]"
                                        class="heading-input w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                                        placeholder="Heading #{{ $i + 1 }} (ví dụ: Activity for visitors)" value="{{ $metaOptions[$i] ?? '' }}"
                                        maxlength="150" aria-describedby="heading-help-{{ $i }}">
                                    <div id="heading-help-{{ $i }}" class="text-xs text-gray-400 mt-1">Tối đa 150 ký tự.</div>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="correct-{{ $i }}" class="block text-sm font-semibold text-slate-700 mb-1">Đáp án <span class="text-red-500">*</span></label>
                                    <select id="correct-{{ $i }}" name="correct[{{ $i }}]"
                                        class="correct-select w-full rounded-2xl border border-slate-300 bg-white px-3 py-2 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                                        aria-label="Chọn heading cho đoạn {{ $i + 1 }}">
                                        <option value="">-- Chọn heading --</option>
                                        @for($j = 0; $j < 7; $j++)
                                            <option value="{{ $j }}" {{ (string) ($metaCorrect[$i] ?? '') === (string) $j ? 'selected' : '' }}>
                                                {{ $metaOptions[$j] ?? ("Heading #" . ($j + 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                    <div class="text-xs text-gray-400 mt-1">Chọn một trong 7 heading.</div>
                                </div>
                            </div>
                        @endfor
                    </div>
                    @error('paragraphs')<div class="text-red-500 text-xs mt-2">{{ $message }}</div>@enderror
                    @error('options')<div class="text-red-500 text-xs mt-2">{{ $message }}</div>@enderror
                    @error('correct')<div class="text-red-500 text-xs mt-2">{{ $message }}</div>@enderror
                </div>

                {{-- PREVIEW UI --}}
                <div class="mb-10">
                    <label class="block text-base font-semibold mb-2">Preview mapping đoạn văn → heading</label>
                    <div class="text-xs text-gray-500 mb-2">Bảng preview hiển thị bản rút gọn đoạn văn (tối đa 300 ký tự) và heading đã chọn.</div>
                    <div id="ui-preview" class="bg-gradient-to-br from-blue-50 to-emerald-50 rounded-2xl p-6 border border-slate-200 text-base shadow">
                        <!-- Preview table sẽ được JS render lại -->
                        <table class="min-w-full w-full border text-base rounded-xl overflow-hidden">
                            <thead>
                                <tr class="bg-blue-100 text-blue-800">
                                    <th class="px-4 py-3 border">#</th>
                                    <th class="px-4 py-3 border">Đoạn văn</th>
                                    <th class="px-4 py-3 border">Heading đã chọn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < 7; $i++)
                                    <tr>
                                        <td class="px-4 py-3 border text-center font-bold">{{ $i + 1 }}</td>
                                        <td class="px-4 py-3 border">{{ \Illuminate\Support\Str::limit($metaParagraphs[$i] ?? '', 300) }}</td>
                                        <td class="px-4 py-3 border text-emerald-700 font-semibold">
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

                <div class="flex justify-end mt-8 gap-4">
                    <a href="{{ route('admin.quizzes.questions', ['part' => 4]) }}"
                        class="inline-flex items-center px-2 py-3 bg-white border border-slate-300 rounded-2xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <span class="px-2">Quay lại</span>
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-2 py-3 bg-blue-600 border border-transparent rounded-2xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="px-2">Lưu</span>
                    </button>
                </div>
            </form>
        </div>

        <script>
            // Helpers
            function escapeHtml(unsafe) {
                if (!unsafe) return '';
                return unsafe
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            function getFormMetadata() {
                const paragraphs = Array.from(document.querySelectorAll('textarea[name^="paragraphs["]'))
                    .map(e => e.value.trim());
                const options = Array.from(document.querySelectorAll('input[name^="options["]'))
                    .map(e => e.value.trim());
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
                    const paraPreview = para.length > 300 ? para.slice(0, 300) + '…' : para;
                    html += `<tr><td class='px-2 py-1 border text-center'>${i + 1}</td>` +
                            `<td class='px-2 py-1 border'>${escapeHtml(paraPreview)}</td>` +
                            `<td class='px-2 py-1 border'>${escapeHtml(heading)}</td></tr>`;
                }
                html += '</tbody></table>';
                document.getElementById('ui-preview').innerHTML = html;
            }

            // rebuild select options from headings (keeps placeholder at index 0)
            function refreshSelectOptions() {
                const headings = Array.from(document.querySelectorAll('input[name^="options["]'))
                    .map(e => e.value || '');
                document.querySelectorAll('select[name^="correct["]').forEach(select => {
                    const selVal = select.value;
                    // remove all but the first placeholder option
                    while (select.options.length > 1) select.remove(1);
                    headings.forEach((h, idx) => {
                        const opt = document.createElement('option');
                        opt.value = idx;
                        opt.text = h || `Heading #${idx + 1}`;
                        select.appendChild(opt);
                    });
                    // restore previous selection if available
                    select.value = selVal;
                });
            }

            function autoResizeTextarea(t) {
                t.style.height = 'auto';
                t.style.height = (t.scrollHeight) + 'px';
            }

            // Basic live validation hint (non-blocking): mark empty required fields
            function doLiveValidationHints() {
                document.querySelectorAll('.paragraph-input').forEach((ta, idx) => {
                    const fieldWrap = ta.closest('div');
                    if (!ta.value.trim()) {
                        ta.classList.add('border-red-300');
                        ta.setAttribute('aria-invalid', 'true');
                    } else {
                        ta.classList.remove('border-red-300');
                        ta.removeAttribute('aria-invalid');
                    }
                });

                document.querySelectorAll('.heading-input').forEach((inp) => {
                    if (!inp.value.trim()) {
                        inp.classList.add('border-red-300');
                        inp.setAttribute('aria-invalid', 'true');
                    } else {
                        inp.classList.remove('border-red-300');
                        inp.removeAttribute('aria-invalid');
                    }
                });

                document.querySelectorAll('.correct-select').forEach((sel) => {
                    if (sel.value === '') {
                        sel.classList.add('border-red-300');
                    } else {
                        sel.classList.remove('border-red-300');
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                // wire inputs
                const rowInputs = document.querySelectorAll('.paragraph-input, .heading-input, .correct-select');
                rowInputs.forEach(el => {
                    el.addEventListener('input', () => {
                        if (el.classList.contains('heading-input')) {
                            refreshSelectOptions();
                        }
                        if (el.classList.contains('paragraph-input')) autoResizeTextarea(el);
                        updateUIPreview();
                        doLiveValidationHints();
                    });
                    el.addEventListener('change', () => {
                        updateUIPreview();
                        doLiveValidationHints();
                    });
                });

                // initial auto-resize and select refresh
                document.querySelectorAll('.paragraph-input').forEach(t => autoResizeTextarea(t));
                refreshSelectOptions();
                updateUIPreview();
                doLiveValidationHints();
            });
        </script>
    </div>
@endsection
