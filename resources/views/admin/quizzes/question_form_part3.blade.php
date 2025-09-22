@extends('layouts.app')

@section('title', isset($question->id) ? 'Sửa Reading Part 3' : 'Thêm Reading Part 3')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <h2 class="text-xl font-bold mb-4">Reading Part 3 — Matching</h2>

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

    $setId  = old('reading_set_id', $question->reading_set_id ?? request('reading_set_id'));
    $setObj = $sets->where('id', $setId)->first();
    $quizId = $setObj ? $setObj->quiz_id : (old('quiz_id', $question->quiz_id ?? request('quiz_id')));
    // quizTitle, setTitle đã được truyền từ controller

        $labels = ['A','B','C','D'];

        // Items (A–D)
        $metaItems = old('items', $question->metadata['items'] ?? []);
        $itemsByLabel = [];
        foreach ($labels as $i => $L) {
            $itemsByLabel[$L] = $metaItems[$i]['text']
                ?? (optional(collect($question->metadata['items'] ?? [])->firstWhere('label',$L))['text'] ?? '');
        }

        // Options (7)
        $metaOptions = old('options', $question->metadata['options'] ?? []);
        $options = [];
        for ($i=0; $i<7; $i++) {
            $options[$i] = old("options.$i", $metaOptions[$i] ?? '');
        }

        // Answers
        $metaAnswers = old('answers', $question->metadata['answers'] ?? []);
        $answers = [];
        foreach ($labels as $L) {
            $answers[$L] = array_map('intval', (array)($metaAnswers[$L] ?? []));
        }
    @endphp

    <form method="POST" action="{{ isset($question->id) ? route('admin.questions.part3.update', $question) : route('admin.questions.part3.store') }}">
        @csrf
        @if(isset($question->id)) @method('PUT') @endif

        {{-- Khóa ngữ cảnh Quiz/Set --}}
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

        {{-- Stem --}}
        <div class="mb-6">
            <label class="block font-medium mb-1">Tiêu đề (stem)</label>
            <input type="text" name="stem" class="form-input w-full border rounded p-2"
                   value="{{ old('stem', $question->stem ?? '') }}">
            @error('stem')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        {{-- (ẩn) skill/type cho hệ thống --}}
        <input type="hidden" name="skill" value="{{ old('skill', $question->skill ?? 'reading') }}">
        <input type="hidden" name="type"  value="{{ old('type',  $question->type  ?? 'reading_matching') }}">

        {{-- ITEMS: CỐ ĐỊNH 4 (A–D) --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Các đoạn văn (items) — 4 đoạn (A–D)</label>
            @foreach($labels as $i => $L)
                <div class="mb-3">
                    <div class="mb-1 font-semibold">Đoạn {{ $L }}</div>
                    <textarea name="items[{{ $i }}][text]" rows="3" class="w-full border rounded p-2 item-text"
                              placeholder="Nhập đoạn văn">{{ $itemsByLabel[$L] }}</textarea>
                    <input type="hidden" name="items[{{ $i }}][label]" value="{{ $L }}">
                </div>
            @endforeach
            @error('items')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        {{-- OPTIONS: CỐ ĐỊNH 7 --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Các lựa chọn (options) — 7 lựa chọn</label>
            @for($i=0; $i<7; $i++)
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-6 text-right text-gray-500">{{ $i+1 }}.</div>
                    <input type="text" name="options[{{ $i }}]" class="form-input flex-1 border rounded p-2 option-text"
                           placeholder="Option #{{ $i+1 }}" value="{{ $options[$i] }}">
                </div>
            @endfor
            @error('options')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        {{-- ANSWERS: Mapping checkbox cho A–D × 7 --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Mapping đáp án (answers)</label>
            @foreach($labels as $L)
                <div class="p-3 border rounded mb-3">
                    <div class="font-semibold mb-2">{{ $L }}</div>
                    <div class="flex flex-wrap gap-3">
                        @for($oidx=0; $oidx<7; $oidx++)
                            @php
                                $checked = in_array($oidx, $answers[$L] ?? [], true) ? 'checked' : '';
                                $optText = $options[$oidx] ?? '';
                            @endphp
                            <label class="inline-flex items-center gap-2">
                                <input type="checkbox" name="answers[{{ $L }}][]" value="{{ $oidx }}" class="ans-cb" {{ $checked }}>
                                <span class="text-sm option-label" data-idx="{{ $oidx }}">{{ $optText !== '' ? $optText : "Option #".($oidx+1) }}</span>
                            </label>
                        @endfor
                    </div>
                </div>
            @endforeach
            @error('answers')<div class="text-red-500 text-xs">{{ $message }}</div>@enderror
        </div>

        {{-- PREVIEW UI (không JSON) --}}
        <div class="mb-8">
            <label class="block font-medium mb-2">Preview</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="preview-ui">
                {{-- Bên trái (Items) --}}
                <div class="space-y-3" id="preview-items-col">
                    <div class="text-sm font-semibold text-gray-700">Texts</div>
                    <!-- items cards render bằng JS -->
                </div>
                {{-- Bên phải (Options / Questions) --}}
                <div class="space-y-3" id="preview-questions-col">
                    <div class="text-sm font-semibold text-gray-700">Questions</div>
                    <!-- questions render bằng JS -->
                </div>
            </div>
        </div>

        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Lưu</button>
    </form>
</div>

{{-- JS chỉ để render UI preview (không có JSON) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const labels = ['A','B','C','D'];

    function escapeHtml(s){
        return String(s ?? '').replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
        }[m]));
    }

    function getItems(){
        const arr = [];
        document.querySelectorAll('textarea.item-text').forEach((ta, i) => {
            arr.push({ label: labels[i] || ('X'+(i+1)), text: ta.value || '' });
        });
        return arr;
    }
    function getOptions(){
        const arr = [];
        document.querySelectorAll('input.option-text').forEach(inp => arr.push(inp.value || ''));
        return arr;
    }
    function getAnswers(){
        const obj = {A:[],B:[],C:[],D:[]};
        document.querySelectorAll('input.ans-cb:checked').forEach(cb=>{
            const L = cb.name.match(/answers\[(.+?)\]/)[1];
            obj[L].push(parseInt(cb.value,10));
        });
        Object.keys(obj).forEach(L => {
            obj[L] = Array.from(new Set(obj[L])).sort((a,b)=>a-b);
        });
        return obj;
    }

    function renderPreviewUI(){
        const items   = getItems();
        const options = getOptions();
        const answers = getAnswers();

        // map ngược optionIndex -> label (nếu duy nhất)
        const optionToLabel = {};
        options.forEach((_, idx) => {
            const belong = labels.filter(L => (answers[L]||[]).includes(idx));
            optionToLabel[idx] = belong.length === 1 ? belong[0] : (belong.length > 1 ? belong.join('/') : null);
        });

        // Left: items
        const itemsCol = document.getElementById('preview-items-col');
        itemsCol.querySelectorAll('.preview-item-card').forEach(el => el.remove());
        items.forEach(it=>{
            const card = document.createElement('div');
            card.className = 'preview-item-card rounded-xl border p-3 bg-white shadow-sm flex gap-3';
            card.innerHTML = `
                <div class="w-9 h-9 flex items-center justify-center rounded-full bg-blue-100 text-blue-700 font-bold">
                    ${it.label}
                </div>
                <div class="text-sm leading-relaxed">${escapeHtml(it.text)}</div>
            `;
            itemsCol.appendChild(card);
        });

        // Right: questions
        const qsCol = document.getElementById('preview-questions-col');
        qsCol.querySelectorAll('.preview-q-row').forEach(el => el.remove());
        options.forEach((opt, i)=>{
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
                <div class="w-7 text-right pt-0.5 text-gray-500">${i+1}.</div>
                <div class="flex-1">
                    <div class="text-sm">${escapeHtml(opt)}</div>
                    <div class="mt-2 flex items-center gap-2">
                        <select disabled class="border rounded px-2 py-1 bg-gray-50">
                            <option>- Select person -</option>
                            ${items.map(it=>`<option ${mapped===it.label?'selected':''}>${it.label}</option>`).join('')}
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
            span.textContent = options[idx] || ('Option #'+(idx+1));
        });
    }

    // Bind sự kiện gọn
    document.addEventListener('input', function(e){
        if (e.target.matches('.item-text, .option-text')) renderPreviewUI();
    });
    document.addEventListener('change', function(e){
        if (e.target.matches('.ans-cb')) renderPreviewUI();
    });

    // First render
    renderPreviewUI();
});
</script>
@endsection
