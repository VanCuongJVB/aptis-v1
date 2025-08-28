@php
    $items = isset($question) ? implode("\n", $question->meta['items'] ?? []) : ($old['items_text'] ?? "");
@endphp
<label class="block">
    <span class="text-sm">Các câu (mỗi câu 1 dòng, đúng thứ tự)</span>
    <textarea name="items_text" rows="6" class="w-full border rounded px-3 py-2"
        placeholder="Câu 1&#10;Câu 2&#10;Câu 3&#10;Câu 4&#10;Câu 5">{{ $items }}</textarea>
</label>
<small class="text-slate-500">Hệ thống lưu thứ tự đúng như bạn nhập. Khi hiển thị cho học sinh, frontend sẽ xáo
    trộn.</small>