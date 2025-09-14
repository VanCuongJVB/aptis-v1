<div class="space-y-4">
  <div>
    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
    <input type="email" id="email" name="email" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required
           value="{{ old('email', isset($student) ? $student->email : '') }}">
    @error('email')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
  </div>
  
  <div>
  <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Tên học sinh (tuỳ chọn)</label>
    <input type="text" id="name" name="name" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
           value="{{ old('name', isset($student) ? $student->name : '') }}">
    @error('name')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
  </div>
  
  <div class="flex items-center mt-2">
    <input type="checkbox" id="is_active" name="is_active" value="1" 
           {{ old('is_active', isset($student) ? $student->is_active : true) ? 'checked' : '' }}
           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
    <label for="is_active" class="ml-2 block text-sm text-gray-700">Kích hoạt tài khoản</label>
  </div>

  <div class="grid md:grid-cols-2 gap-4 mt-2">
    <div>
      <label for="access_starts_at" class="block text-sm font-medium text-gray-700 mb-1">Thời gian bắt đầu truy cập</label>
      <input type="datetime-local" id="access_starts_at" name="access_starts_at" 
             class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
             value="{{ old('access_starts_at', isset($student) && isset($student->access_starts_at) ? $student->access_starts_at->format('Y-m-d\TH:i') : '') }}">
      @error('access_starts_at')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
      @enderror
    </div>
    <div>
      <label for="access_ends_at" class="block text-sm font-medium text-gray-700 mb-1">Thời gian kết thúc truy cập</label>
      <input type="datetime-local" id="access_ends_at" name="access_ends_at" 
             class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
             value="{{ old('access_ends_at', isset($student) && isset($student->access_ends_at) ? $student->access_ends_at->format('Y-m-d\TH:i') : '') }}">
      @error('access_ends_at')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
      @enderror
    </div>
  </div>
</div>
