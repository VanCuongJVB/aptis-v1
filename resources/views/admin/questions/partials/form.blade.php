@php $qq = $question ?? null; @endphp
<div class="space-y-3">
  <label class="block">
    <span class="text-sm">Loại</span>
    <select name="type" class="w-full border rounded px-3 py-2">
      <option value="single" @selected(old('type', $qq->type ?? '')==='single')>Single</option>
      <option value="multi" @selected(old('type', $qq->type ?? '')==='multi')>Multi (all-or-nothing)</option>
    </select>
  </label>
  <label class="block">
    <span class="text-sm">Thứ tự</span>
    <input type="number" name="order" class="w-full border rounded px-3 py-2" value="{{ old('order', $qq->order ?? 1) }}">
  </label>
  <label class="block">
    <span class="text-sm">Nội dung câu hỏi</span>
    <textarea name="stem" class="w-full border rounded px-3 py-2" rows="4" required>{{ old('stem', $qq->stem ?? '') }}</textarea>
  </label>
  <label class="block">
    <span class="text-sm">Audio path (tuỳ chọn)</span>
    <input type="text" name="audio_path" class="w-full border rounded px-3 py-2" value="{{ old('audio_path', $qq->audio_path ?? '') }}">
    <div class="text-xs text-slate-500 mt-1">VD: storage/audio/q1.mp3</div>
  </label>

  <div id="options-wrap" class="space-y-2">
    <div class="font-semibold">Phương án</div>
    @php $opts = old('options', $qq?->options?->toArray() ?? [
      ['label' => 'A', 'is_correct' => false],
      ['label' => 'B', 'is_correct' => false],
      ['label' => 'C', 'is_correct' => false],
      ['label' => 'D', 'is_correct' => false],
    ]); @endphp
    @foreach($opts as $i => $opt)
      <div class="grid grid-cols-12 gap-2 items-center">
        <input type="hidden" name="options[{{ $i }}][id]" value="{{ $opt['id'] ?? '' }}">
        <input class="col-span-9 border rounded px-3 py-2" name="options[{{ $i }}][label]" value="{{ $opt['label'] ?? '' }}" placeholder="Nội dung">
        <label class="col-span-3 flex items-center gap-2">
          <input type="checkbox" name="options[{{ $i }}][is_correct]" value="1" @checked(($opt['is_correct'] ?? false)==true)>
          <span>Đáp án đúng</span>
        </label>
      </div>
    @endforeach
  </div>
  <button type="button" onclick="addOption()" class="px-2 py-1 bg-slate-200 rounded">+ Thêm phương án</button>
</div>

<script>
let optIndex = {{ count($opts) }};
function addOption() {
  const wrap = document.getElementById('options-wrap');
  const row = document.createElement('div');
  row.className = 'grid grid-cols-12 gap-2 items-center mt-2';
  row.innerHTML = `
    <input type="hidden" name="options[${optIndex}][id]" value="">
    <input class="col-span-9 border rounded px-3 py-2" name="options[${optIndex}][label]" placeholder="Nội dung">
    <label class="col-span-3 flex items-center gap-2">
      <input type="checkbox" name="options[${optIndex}][is_correct]" value="1">
      <span>Đáp án đúng</span>
    </label>`;
  wrap.appendChild(row);
  optIndex++;
}
</script>
