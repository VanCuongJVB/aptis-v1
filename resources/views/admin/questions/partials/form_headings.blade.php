@php
    if (isset($question)) {
        $paragraphs = implode("\n---\n", $question->meta['paragraphs'] ?? []);
        $headings = implode("\n", $question->meta['headings'] ?? []);
        $pairs = [];
        foreach (($question->meta['answer'] ?? []) as $k => $v) {
            $pairs[] = "{$k}:{$v}";
        }
        $answer = implode(',', $pairs);
    } else {
        $paragraphs = $old['paragraphs_text'] ?? "";
        $headings = $old['headings_text'] ?? "";
        $answer = $old['answer_text'] ?? "1:A,2:B,3:C,4:D,5:E,6:F,7:G";
    }
@endphp
<label class="block">
    <span class="text-sm">Paragraphs (8 đoạn, ngăn nhau bằng dòng chỉ có <code>---</code>)</span>
    <textarea name="paragraphs_text" rows="10" class="w-full border rounded px-3 py-2"
        placeholder="Đoạn 1...&#10;---&#10;Đoạn 2...&#10;---&#10;...">{{ $paragraphs }}</textarea>
</label>
<label class="block">
    <span class="text-sm">Headings (7 dòng, A→G)</span>
    <textarea name="headings_text" rows="7" class="w-full border rounded px-3 py-2"
        placeholder="A ...&#10;B ...&#10;C ...&#10;D ...&#10;E ...&#10;F ...&#10;G ...">{{ $headings }}</textarea>
</label>
<label class="block">
    <span class="text-sm">Đáp án (map 1..7 → A..G, ví dụ: <code>1:A,2:B,...</code>)</span>
    <input type="text" name="answer_text" class="w-full border rounded px-3 py-2" value="{{ $answer }}">
</label>