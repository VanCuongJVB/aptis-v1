@extends('layouts.app')

@section('title', $set->exists ? 'Edit Set' : 'Create Set')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">{{ $set->exists ? 'Edit Set' : 'Create Set' }}</h1>

    <form method="POST" action="{{ $set->exists ? route('admin.sets.update', $set) : route('admin.sets.store') }}">
            @csrf
            @if($set->exists) @method('PUT') @endif

            <div class="mb-4">
                <label class="block text-sm">Title</label>
                <input name="title" value="{{ old('title', $set->title) }}" class="w-full border p-2 rounded" />
            </div>

            <div class="mb-4">
                <label class="block text-sm">Quiz</label>
                <select name="quiz_id" id="quiz_id_select" class="w-full border p-2 rounded" @if($set->exists) disabled @endif required>
                    <option value="">-- Chọn quiz --</option>
                    @foreach($quizzes as $q)
                        <option value="{{ $q->id }}" data-skill="{{ $q->skill }}" {{ $q->id == old('quiz_id', $set->quiz_id) ? 'selected' : '' }}>{{ $q->title }}</option>
                    @endforeach
                </select>
                @if($set->exists)
                    <input type="hidden" name="quiz_id" value="{{ $set->quiz_id }}" />
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm">Kỹ năng</label>
                <input type="text" id="skill_display" class="w-full border p-2 rounded bg-gray-100 text-gray-400" value="{{ old('skill', $set->skill) == 'reading' ? 'Đọc hiểu' : (old('skill', $set->skill) == 'listening' ? 'Nghe hiểu' : '') }}" placeholder="Chọn quiz để tự động hiển thị kỹ năng" readonly />
                <input type="hidden" name="skill" id="skill_select" value="{{ old('skill', $set->skill) }}" />
            </div>

            <div class="flex items-center justify-end">
                {{-- <a href="{{ route('admin.quizzes.sets') }}" class="mr-2 px-4 py-2 border rounded">Cancel</a> --}}
                <a href="{{ route('admin.quizzes.sets') }}" class="mr-2 px-4 py-2 border rounded">Cancel</a>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
            </div>
        </form>
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
