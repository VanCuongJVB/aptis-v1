@extends('layouts.app')

@section('title', 'Quizzes Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Quizzes — Quản lý</h1>
            <p class="text-sm text-gray-600">Xem tổng quan Quizz, Sets và Questions. (UI tạm, cần wiring route/controller)</p>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="openImportModal()" class="bg-indigo-600 text-white px-4 py-2 rounded shadow">Import Questions</button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Quizzes</p>
            <p class="text-3xl font-bold">{{ $quizzes_count ?? '—' }}</p>
            <p class="text-sm text-gray-600 mt-2">Tổng số quiz trong hệ thống</p>
            <div class="mt-4">
                <a href="{{ route('admin.quizzes.index') }}" class="text-indigo-600 underline text-sm">Quản lý Quizzes</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Sets</p>
            <p class="text-3xl font-bold">{{ $sets_count ?? '—' }}</p>
            <p class="text-sm text-gray-600 mt-2">Số bộ đề (sets) thuộc các quiz</p>
            <div class="mt-4">
                <a href="{{ route('admin.quizzes.sets') }}" class="text-indigo-600 underline text-sm">Quản lý Sets</a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Questions</p>
            <p class="text-3xl font-bold">{{ $questions_count ?? '—' }}</p>
            <p class="text-sm text-gray-600 mt-2">Tổng số câu hỏi</p>
            <div class="mt-4">
                <a href="{{ route('admin.quizzes.questions') }}" class="text-indigo-600 underline text-sm">Quản lý Questions</a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Quick actions</h2>
        <div class="flex flex-wrap gap-3">
            <button onclick="openImportModal()" class="bg-green-600 text-white px-4 py-2 rounded">Import Questions</button>
            <a href="#" class="px-4 py-2 border rounded">Export template</a>
        </div>
    </div>

    @include('admin.quizzes._import_modal')
</div>

@push('scripts')
<script>
function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}
function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}
// Close on ESC
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeImportModal(); });
</script>
@endpush

@endsection
