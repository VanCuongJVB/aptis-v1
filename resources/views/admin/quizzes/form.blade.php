@extends('layouts.app')

@section('title', $quiz->exists ? 'Sửa Quiz' : 'Tạo Quiz mới')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-xl font-bold mb-4">{{ $quiz->exists ? 'Sửa Quiz' : 'Tạo Quiz mới' }}</h1>
                <div class="text-sm text-gray-500 mb-3">Các trường có dấu <span class="text-red-500">*</span> là bắt buộc.</div>
                <form method="POST" action="{{ $quiz->exists ? route('admin.quizzes.update', $quiz) : route('admin.quizzes.store') }}" class="space-y-3 max-w-xl">
                    @csrf
                    @if($quiz->exists) @method('PUT') @endif

                    <div class="mb-4">
                        <label class="block text-sm">Tên Quiz <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title', $quiz->title) }}" class="w-full border p-2 rounded" required placeholder="Nhập tên quiz..." />
                        @error('title') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm">Mô tả</label>
                        <textarea name="description" class="w-full border p-2 rounded" placeholder="Mô tả ngắn về quiz (không bắt buộc)">{{ old('description', $quiz->description) }}</textarea>
                        @error('description') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm">Kỹ năng <span class="text-red-500">*</span></label>
                            <select name="skill" class="w-full border p-2 rounded" required>
                                <option value="">-- Chọn kỹ năng --</option>
                                <option value="reading" {{ old('skill', $quiz->skill) == 'reading' ? 'selected' : '' }}>Đọc hiểu</option>
                                <option value="listening" {{ old('skill', $quiz->skill) == 'listening' ? 'selected' : '' }}>Nghe hiểu</option>
                            </select>
                            @error('skill') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm">Phần (Part) <span class="text-red-500">*</span></label>
                            <select name="part" class="w-full border p-2 rounded" required>
                                <option value="">-- Chọn phần --</option>
                                @for($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}" {{ old('part', $quiz->part) == $i ? 'selected' : '' }}>Phần {{ $i }}</option>
                                @endfor
                            </select>
                            @error('part') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4 grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm">Thời lượng (phút) <span class="text-red-500">*</span></label>
                            <input type="number" name="duration_minutes" min="1" value="{{ old('duration_minutes', $quiz->duration_minutes) }}" class="w-full border p-2 rounded" required placeholder="Thời gian làm bài..." />
                            @error('duration_minutes') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="flex items-center mt-6">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }} class="mr-2" />
                            <label>Hiển thị cho học viên</label>
                        </div>
                    </div>

                    <div class="mb-4 flex items-center">
                        <input type="checkbox" name="show_explanation" value="1" {{ old('show_explanation', $quiz->show_explanation) ? 'checked' : '' }} class="mr-2" />
                        <label>Hiện giải thích đáp án</label>
                    </div>

                    <div class="flex items-center justify-between mt-4">
                        <a href="{{ route('admin.quizzes.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Huỷ
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
