@php
    $opts = isset($question)
        ? $question->options->map(fn($o) => ['label' => $o->label, 'is_correct' => $o->is_correct])->toArray()
        : ($old['options'] ?? [['label' => ''], ['label' => ''], ['label' => '']]);
    $correctIdx = isset($question)
        ? $question->options->search(fn($o) => $o->is_correct) ?? 0
        : (int) ($old['correct_index'] ?? 0);
@endphp

<div class="border rounded p-3">
    <div class="font-semibold mb-2">Tuỳ chọn</div>
    <div id="mcq-list" class="space-y-2">
        @foreach($opts as $i => $opt)
            <div class="flex items-center gap-2">
                <input type="radio" name="correct_index" value="{{ $i }}" @checked($i === $correctIdx)>
                <input type="text" name="options[{{ $i }}][label]" class="flex-1 border rounded px-3 py-2"
                    value="{{ $opt['label'] }}">
                <button type="button" class="px-2 py-1 rounded bg-slate-100" onclick="mcqAddOption(this)">+ Thêm</button>
                <button type="button" class="px-2 py-1 rounded bg-slate-100" onclick="mcqRemoveOption(this)">Xoá</button>
            </div>
        @endforeach
    </div>
    <script>
        function mcqAddOption(btn) {
            const list = document.getElementById('mcq-list');
            const idx = list.children.length;
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 mt-2';
            row.innerHTML = `
        <input type="radio" name="correct_index" value="${idx}">
        <input type="text" name="options[${idx}][label]" class="flex-1 border rounded px-3 py-2" value="">
        <button type="button" class="px-2 py-1 rounded bg-slate-100" onclick="mcqAddOption(this)">+ Thêm</button>
        <button type="button" class="px-2 py-1 rounded bg-slate-100" onclick="mcqRemoveOption(this)">Xoá</button>
      `;
            list.appendChild(row);
        }
        function mcqRemoveOption(btn) {
            const row = btn.parentElement;
            const list = row.parentElement;
            list.removeChild(row);
            // NOTE: đơn giản ko reindex; submit vẫn OK vì name có chỉ số cũ
        }
    </script>
</div>