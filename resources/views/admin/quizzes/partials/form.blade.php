@php $q = $quiz; @endphp
<div class="grid md:grid-cols-2 gap-3">
  <label class="block">
    <span class="text-sm">Tiêu đề</span>
    <input type="text" name="title" class="w-full border rounded px-3 py-2" value="{{ old('title', $q->title ?? '') }}" required>
  </label>
  <label class="block">
    <span class="text-sm">Skill</span>
    <select name="skill" class="w-full border rounded px-3 py-2" required>
      <option value="reading" @selected(old('skill', $q->skill ?? '')==='reading')>Reading</option>
      <option value="listening" @selected(old('skill', $q->skill ?? '')==='listening')>Listening</option>
    </select>
  </label>
  <label class="block md:col-span-2">
    <span class="text-sm">Mô tả</span>
    <textarea name="description" class="w-full border rounded px-3 py-2" rows="3">{{ old('description', $q->description ?? '') }}</textarea>
  </label>
  <label class="block">
    <span class="text-sm">Thời lượng (phút)</span>
    <input type="number" name="duration_minutes" class="w-full border rounded px-3 py-2" value="{{ old('duration_minutes', $q->duration_minutes ?? 45) }}" min="1" max="300">
  </label>
  <label class="flex items-center gap-2">
    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $q->is_published ?? false))>
    <span>Publish</span>
  </label>

  <div class="md:col-span-2 border-t pt-3">
    <h3 class="font-semibold mb-2">Listening</h3>
    <div class="grid md:grid-cols-2 gap-3">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="allow_seek" value="1" @checked(old('allow_seek', $q->allow_seek ?? false))>
        <span>Cho tua audio</span>
      </label>
      <label class="block">
        <span class="text-sm">Số lần nghe</span>
        <input type="number" name="listens_allowed" class="w-full border rounded px-3 py-2" value="{{ old('listens_allowed', $q->listens_allowed ?? 1) }}" min="1" max="10">
      </label>
    </div>
  </div>
</div>
