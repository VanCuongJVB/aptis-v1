@extends('layouts.app')

@section('title', 'Chọn loại câu hỏi')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-lg mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Chọn loại câu hỏi</h1>
        <form method="GET" action="">
            <div class="mb-4">
                <label class="block text-sm mb-2">Loại câu hỏi</label>
                <select name="type" class="w-full border p-2 rounded" required>
                    <option value="">-- Chọn loại --</option>
                    <option value="reading_gap_filling">Reading - Part 1 (Gap Filling)</option>
                    <option value="reading_notice_matching">Reading - Part 2 (Ordering)</option>
                    <option value="reading_matching">Reading - Part 3 (Matching)</option>
                    <option value="reading_long_text">Reading - Part 4 (Long Text)</option>
                    <!-- Thêm các loại khác nếu cần -->
                </select>
            </div>
            <div class="flex items-center justify-end">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Tiếp tục</button>
            </div>
        </form>
    </div>
</div>
@endsection
