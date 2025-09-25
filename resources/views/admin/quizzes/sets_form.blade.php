@extends('layouts.app')

@section('title', $set->exists ? 'Edit Set' : 'Create Set')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white/80 backdrop-blur-md shadow-xl rounded-2xl border border-gray-100">
            <div class="p-8 text-gray-900">
                <h1 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    {{ $set->exists ? 'Sửa Set' : 'Tạo Set mới' }}
                </h1>
                <form method="POST" action="{{ $set->exists ? route('admin.sets.update', $set) : route('admin.sets.store') }}" class="space-y-6">
                    @csrf
                    @if($set->exists) @method('PUT') @endif

                    <div class="relative">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Tên Set <span class="text-red-500">*</span></label>
                        <input name="title" value="{{ old('title', $set->title) }}" required placeholder="Nhập tên set..."
                            class="peer w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400 bg-white" />
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Quiz <span class="text-red-500">*</span></label>
                        <select name="quiz_id" id="quiz_id_select" class="peer w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition bg-white" @if($set->exists) disabled @endif required>
                            <option value="">-- Chọn quiz --</option>
                            @foreach($quizzes as $q)
                                <option value="{{ $q->id }}" data-skill="{{ $q->skill }}" {{ $q->id == old('quiz_id', $set->quiz_id) ? 'selected' : '' }}>{{ $q->title }}</option>
                            @endforeach
                        </select>
                        @if($set->exists)
                            <input type="hidden" name="quiz_id" value="{{ $set->quiz_id }}" />
                        @endif
                    </div>

                    <div class="relative">
                        <label class="block text-sm font-semibold mb-2 text-gray-700">Kỹ năng</label>
                        <input type="text" id="skill_display" class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-100 text-gray-400" value="{{ old('skill', $set->skill) == 'reading' ? 'Đọc hiểu' : (old('skill', $set->skill) == 'listening' ? 'Nghe hiểu' : '') }}" placeholder="Chọn quiz để tự động hiển thị kỹ năng" readonly />
                        <input type="hidden" name="skill" id="skill_select" value="{{ old('skill', $set->skill) }}" />
                    </div>

                    <div class="flex items-center justify-between mt-8 gap-4">
                        <a href="{{ route('admin.quizzes.sets') }}"
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var quizSelect = document.getElementById('quiz_id_select');
    var skillInput = document.getElementById('skill_select');
    var skillDisplay = document.getElementById('skill_display');
    function skillLabel(skill) {
        if (skill === 'reading') return 'Đọc hiểu';
        if (skill === 'listening') return 'Nghe hiểu';
        return '';
    }
    if (quizSelect && skillInput && skillDisplay && !quizSelect.disabled) {
        quizSelect.addEventListener('change', function() {
            var selected = quizSelect.options[quizSelect.selectedIndex];
            var skill = selected.getAttribute('data-skill');
            skillInput.value = skill || '';
            skillDisplay.value = skillLabel(skill);
            if (!skill) {
                skillDisplay.classList.add('text-gray-400');
            } else {
                skillDisplay.classList.remove('text-gray-400');
            }
        });
        // Set initial style
        if (!skillInput.value) {
            skillDisplay.classList.add('text-gray-400');
        } else {
            skillDisplay.classList.remove('text-gray-400');
        }
    }
});
</script>
@endpush
