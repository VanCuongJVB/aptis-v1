
@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 3' : 'Thêm Reading Part 3')

@section('content')
    <div class="container mx-auto px-2 py-8">
        <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-xl border border-slate-200 p-6 md:p-10">
            <h1 class="text-3xl font-extrabold mb-6 flex items-center gap-3 text-blue-800 tracking-tight">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                {{ isset($question->id) ? 'Sửa' : 'Tạo' }} <span class="text-blue-600">Reading Part 3</span> <span class="text-slate-500 text-lg font-normal">— Matching</span>
            </h1>

            <p class="text-base text-slate-600 mb-5">Form quản trị <span class="font-semibold text-blue-700">Aptis Reading Part 3</span> (Matching). Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</p>

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

                $setId = old('reading_set_id', $question->reading_set_id ?? request('reading_set_id'));
                $setObj = $sets->where('id', $setId)->first();
                $quizId = $setObj ? $setObj->quiz_id : (old('quiz_id', $question->quiz_id ?? request('quiz_id')));
                // quizTitle, setTitle đã được truyền từ controller

                $labels = ['A', 'B', 'C', 'D'];

                // Items (A–D)
                $metaItems = old('items', $question->metadata['items'] ?? []);
                $itemsByLabel = [];
                foreach ($labels as $i => $L) {
                    $itemsByLabel[$L] = $metaItems[$i]['text']
                        ?? (optional(collect($question->metadata['items'] ?? [])->firstWhere('label', $L))['text'] ?? '');
                }

                // Options (7)
                $metaOptions = old('options', $question->metadata['options'] ?? []);
                $options = [];
                for ($i = 0; $i < 7; $i++) {
                    $options[$i] = old("options.$i", $metaOptions[$i] ?? '');
                }

                // Answers
                $metaAnswers = old('answers', $question->metadata['answers'] ?? []);
                $answers = [];
                foreach ($labels as $L) {
                    $answers[$L] = array_map('intval', (array) ($metaAnswers[$L] ?? []));
                }
            @endphp


            <form method="POST"
                action="{{ isset($question->id) ? route('admin.questions.part3.update', $question) : route('admin.questions.part3.store') }}">
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
                    <label class="block text-base font-semibold text-slate-700 mb-1">Tiêu đề (stem) <span class="text-red-500">*</span></label>
                    <input type="text" name="stem"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition"
                        value="{{ old('stem', $question->stem ?? '') }}">
                    @error('stem')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- (ẩn) skill/type cho hệ thống --}}
                <input type="hidden" name="skill" value="{{ old('skill', $question->skill ?? 'reading') }}">
                <input type="hidden" name="type" value="{{ old('type', $question->type ?? 'reading_matching') }}">


                <div class="mb-10">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Các đoạn văn (items) — 4 đoạn (A–D) <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($labels as $i => $L)
                            <div class="mb-3 bg-slate-50 rounded-2xl border border-slate-200 p-4">
                                <div class="mb-1 font-semibold text-blue-700">Đoạn {{ $L }}</div>
                                <textarea name="items[{{ $i }}][text]" rows="8"
                                    class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition item-text"
                                    placeholder="Nhập đoạn văn">{{ $itemsByLabel[$L] }}</textarea>
                                <input type="hidden" name="items[{{ $i }}][label]" value="{{ $L }}">
                            </div>
                        @endforeach
                    </div>
                    @error('items')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>


                <div class="mb-10">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Các lựa chọn (options) — 7 lựa chọn <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @for($i = 0; $i < 7; $i++)
                            <div class="flex items-center gap-2 mb-2 bg-slate-50 rounded-2xl border border-slate-200 p-3">
                                <div class="w-7 text-right text-gray-500 font-semibold">{{ $i + 1 }}.</div>
                                <input type="text" name="options[{{ $i }}]"
                                    class="flex-1 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-800 shadow focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 transition option-text"
                                    placeholder="Option #{{ $i + 1 }}" value="{{ $options[$i] }}">
                            </div>
                        @endfor
                    </div>
                    @error('options')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>


                <div class="mb-10">
                    <label class="block text-base font-semibold text-slate-700 mb-2">Mapping đáp án (answers) <span class="text-red-500">*</span></label>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-slate-200 rounded-2xl bg-gradient-to-br from-blue-50 to-emerald-50 shadow">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-slate-700">Lựa chọn</th>
                                    @foreach($labels as $L)
                                        <th class="px-2 py-2 text-center text-sm font-semibold text-blue-700">Đoạn {{ $L }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @for($oidx = 0; $oidx < 7; $oidx++)
                                    @php
                                        $optText = $options[$oidx] ?? '';
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-2">
                                                <div class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold">
                                                    {{ $oidx + 1 }}
                                                </div>
                                                <span class="text-sm option-label" data-idx="{{ $oidx }}">{{ $optText !== '' ? $optText : "Option #" . ($oidx + 1) }}</span>
                                            </div>
                                        </td>
                                        @foreach($labels as $L)
                                            @php
                                                $checked = in_array($oidx, $answers[$L] ?? [], true) ? 'checked' : '';
                                            @endphp
                                            <td class="px-2 py-2 text-center">
                                                <input type="checkbox" name="answers[{{ $L }}][]" value="{{ $oidx }}" class="ans-cb accent-blue-600" {{ $checked }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                    @error('answers')<div class="text-red-500 text-xs mt-1">{{ $message }}</div>@enderror
                </div>


                <div class="mb-10">
                    <label class="block text-base font-semibold mb-2">Preview mapping đoạn văn → lựa chọn</label>
                    <div class="text-xs text-gray-500 mb-2">Bảng preview hiển thị các đoạn văn và mapping đáp án đã chọn.</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="preview-ui">
                        <div class="space-y-3" id="preview-items-col">
                            <div class="text-sm font-semibold text-gray-700">Texts</div>
                        </div>
                        <div class="space-y-3" id="preview-questions-col">
                            <div class="text-sm font-semibold text-gray-700">Questions</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-8 gap-4">
                    <a href="{{ route('admin.quizzes.questions', ['part' => 3]) }}"
                        class="inline-flex items-center px-8 py-3 bg-white border border-slate-300 rounded-2xl font-semibold text-base text-blue-700 shadow hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <span class="px-2">Quay lại</span>
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-8 py-3 bg-blue-600 border border-transparent rounded-2xl font-semibold text-base text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/60 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span class="px-2">{{ isset($question->id) ? 'Cập nhật' : 'Tạo mới' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>

    {{-- JS chỉ để render UI preview (không có JSON) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const labels = ['A', 'B', 'C', 'D'];

            function escapeHtml(s) {
                return String(s ?? '').replace(/[&<>"']/g, m => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                }[m]));
            }

            function getItems() {
                const arr = [];
                document.querySelectorAll('textarea.item-text').forEach((ta, i) => {
                    arr.push({ label: labels[i] || ('X' + (i + 1)), text: ta.value || '' });
                });
                return arr;
            }
            function getOptions() {
                const arr = [];
                document.querySelectorAll('input.option-text').forEach(inp => arr.push(inp.value || ''));
                return arr;
            }
            function getAnswers() {
                const obj = { A: [], B: [], C: [], D: [] };
                document.querySelectorAll('input.ans-cb:checked').forEach(cb => {
                    const L = cb.name.match(/answers\[(.+?)\]/)[1];
                    obj[L].push(parseInt(cb.value, 10));
                });
                Object.keys(obj).forEach(L => {
                    obj[L] = Array.from(new Set(obj[L])).sort((a, b) => a - b);
                });
                return obj;
            }

            function renderPreviewUI() {
                const items = getItems();
                const options = getOptions();
                const answers = getAnswers();

                // map ngược optionIndex -> label (nếu duy nhất)
                const optionToLabel = {};
                options.forEach((_, idx) => {
                    const belong = labels.filter(L => (answers[L] || []).includes(idx));
                    optionToLabel[idx] = belong.length === 1 ? belong[0] : (belong.length > 1 ? belong.join('/') : null);
                });

                // Left: items
                const itemsCol = document.getElementById('preview-items-col');
                itemsCol.querySelectorAll('.preview-item-card').forEach(el => el.remove());
                items.forEach(it => {
                    const card = document.createElement('div');
                    card.className = 'preview-item-card rounded-xl border p-3 bg-white shadow-sm flex gap-3';
                    card.innerHTML = `
                        <div class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold" style="width: 100%;">
                            ${it.label}
                        </div>
                        <div class="text-sm leading-relaxed">${escapeHtml(it.text)}</div>
                    `;
                    itemsCol.appendChild(card);
                });

                // Right: questions
                const qsCol = document.getElementById('preview-questions-col');
                qsCol.querySelectorAll('.preview-q-row').forEach(el => el.remove());
                options.forEach((opt, i) => {
                    const row = document.createElement('div');
                    row.className = 'preview-q-row rounded-xl border p-3 bg-white shadow-sm flex items-start gap-3';

                    const mapped = optionToLabel[i];
                    let badge;
                    if (!mapped) {
                        badge = `<span class="inline-block text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">—</span>`;
                    } else if (mapped.includes('/')) {
                        badge = `<span class="inline-block text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">→ ${mapped}</span>`;
                    } else {
                        badge = `<span class="inline-block text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">→ ${mapped}</span>`;
                    }

                    row.innerHTML = `
                        <div class="w-7 text-right pt-0.5 text-gray-500">${i + 1}.</div>
                        <div class="flex-1">
                            <div class="text-sm">${escapeHtml(opt)}</div>
                            <div class="mt-2 flex items-center gap-2">
                                <select disabled class="border rounded px-2 py-1 bg-gray-50">
                                    <option>- Select person -</option>
                                    ${items.map(it => `<option ${mapped === it.label ? 'selected' : ''}>${it.label}</option>`).join('')}
                                </select>
                                ${badge}
                            </div>
                        </div>
                    `;
                    qsCol.appendChild(row);
                });

                // Đồng bộ nhãn option cạnh checkbox nếu người dùng đang sửa option
                document.querySelectorAll('.option-label').forEach(span => {
                    const idx = parseInt(span.dataset.idx, 10);
                    span.textContent = options[idx] || ('Option #' + (idx + 1));
                });
            }

            // Bind sự kiện gọn
            document.addEventListener('input', function (e) {
                if (e.target.matches('.item-text, .option-text')) renderPreviewUI();
            });
            document.addEventListener('change', function (e) {
                if (e.target.matches('.ans-cb')) renderPreviewUI();
            });

            // First render
            renderPreviewUI();
        });
    </script>
@endsection