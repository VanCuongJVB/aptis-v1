@extends('layouts.app')

@section('title', $quiz->exists ? 'Sửa Quiz' : 'Tạo Quiz mới')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white/80 backdrop-blur-md shadow-xl rounded-2xl border border-gray-100">
            <div class="p-8 text-gray-900">
                <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ $quiz->exists ? 'Sửa Quiz' : 'Tạo Quiz mới' }}
                </h1>
                <div class="text-sm text-gray-500 mb-6">Các trường có dấu <span class="text-red-500 font-bold">*</span> là bắt buộc.</div>
                <form method="POST" action="{{ $quiz->exists ? route('admin.quizzes.update', $quiz) : route('admin.quizzes.store') }}" class="space-y-6">
                    @csrf
                    @if($quiz->exists) @method('PUT') @endif

                    <div class="relative">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Tên Quiz <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title', $quiz->title) }}" required placeholder="Nhập tên quiz..."
                            class="peer w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400 bg-white" />
                        @error('title') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Mô tả</label>
                        <textarea name="description" rows="3" placeholder="Mô tả ngắn về quiz (không bắt buộc)"
                            class="peer w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400 bg-white">{{ old('description', $quiz->description) }}</textarea>
                        @error('description') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Kỹ năng <span class="text-red-500">*</span></label>
                            <select name="skill" required
                                class="peer w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                                <option value="">-- Chọn kỹ năng --</option>
                                <option value="reading" {{ old('skill', $quiz->skill) == 'reading' ? 'selected' : '' }}>Đọc hiểu</option>
                                <option value="listening" {{ old('skill', $quiz->skill) == 'listening' ? 'selected' : '' }}>Nghe hiểu</option>
                            </select>
                            @error('skill') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="relative">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Phần (Part) <span class="text-red-500">*</span></label>
                            <select name="part" required
                                class="peer w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white">
                                <option value="">-- Chọn phần --</option>
                                @for($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}" {{ old('part', $quiz->part) == $i ? 'selected' : '' }}>Phần {{ $i }}</option>
                                @endfor
                            </select>
                            @error('part') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="relative">
                            <label class="block text-sm font-semibold mb-2 text-gray-700">Thời lượng (phút)</label>
                            <input type="number" name="duration_minutes" min="1" value="{{ old('duration_minutes', $quiz->duration_minutes) }}"
                                class="peer w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white" placeholder="Thời gian làm bài..." />
                            @error('duration_minutes') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="flex items-center mt-7">
                        <input type="checkbox" name="is_published" value="1" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }} class="accent-blue-600 h-5 w-5 mr-2" />
                        <label class="text-sm font-semibold text-gray-700">Hiển thị quizz cho học viên</label>
                    </div>

                    <div class="flex items-center justify-between mt-8 gap-4">
                        <a href="{{ route('admin.quizzes.index') }}"
                            class="inline-flex items-center px-5 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 shadow hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                            Huỷ
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            Lưu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
