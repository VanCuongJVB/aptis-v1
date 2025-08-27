@php $st = $student ?? null; @endphp
<div class="space-y-3">
  <label class="block">
    <span class="text-sm">Email</span>
    <input type="email" name="email" class="w-full border rounded px-3 py-2" required
           value="{{ old('email', $st->email ?? '') }}">
  </label>
  <label class="block">
    <span class="text-sm">Tên (tuỳ chọn)</span>
    <input type="text" name="name" class="w-full border rounded px-3 py-2"
           value="{{ old('name', $st->name ?? '') }}">
  </label>
  <label class="flex items-center gap-2">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $st->is_active ?? true))>
    <span>Kích hoạt</span>
  </label>

  <div class="grid md:grid-cols-2 gap-3">
    <label class="block">
      <span class="text-sm">Bắt đầu truy cập</span>
      <input type="datetime-local" name="access_starts_at" class="w-full border rounded px-3 py-2"
             value="{{ old('access_starts_at', optional($st->access_starts_at ?? null)->format('Y-m-d\TH:i')) }}">
    </label>
    <label class="block">
      <span class="text-sm">Hết hạn truy cập</span>
      <input type="datetime-local" name="access_ends_at" class="w-full border rounded px-3 py-2"
             value="{{ old('access_ends_at', optional($st->access_ends_at ?? null)->format('Y-m-d\TH:i')) }}">
    </label>
  </div>
</div>
