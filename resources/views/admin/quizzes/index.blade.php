
@extends('layouts.app')

@section('title', 'Quizzes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Quizzes</h1>
        <div class="flex space-x-2">
            <a href="{{ route('admin.quizzes.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">Tạo Quiz mới</a>
            <button type="button" onclick="openImportModal()" class="bg-indigo-600 text-white px-4 py-2 rounded flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Import Quiz (JSON)
            </button>
        </div>
    </div>

    <!-- Tổng quan số lượng -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Quizzes</p>
            <p class="text-3xl font-bold">{{ $quizzes_count ?? ($quizzes->total() ?? '—') }}</p>
            <p class="text-sm text-gray-600 mt-2">Tổng số quiz trong hệ thống</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Sets</p>
            <p class="text-3xl font-bold">{{ $sets_count ?? '—' }}</p>
            <p class="text-sm text-gray-600 mt-2">Số bộ đề (sets) thuộc các quiz</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Questions</p>
            <p class="text-3xl font-bold">{{ $questions_count ?? '—' }}</p>
            <p class="text-sm text-gray-600 mt-2">Tổng số câu hỏi</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded shadow p-4">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skill</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Published</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($quizzes as $quiz)
                        <tr class="even:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">{{ $quiz->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $quiz->title }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ ucfirst($quiz->skill) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $quiz->part }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $quiz->is_published ? 'Yes' : 'No' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Edit</a>
                                <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="inline-flex items-center px-3 py-1 ml-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100" onclick="return confirm('Delete this quiz?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">Không có quiz nào</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 w-100">
            <div class="flex justify-between">
                <style>nav { width: 100% !important; }</style>
                {{ $quizzes->links() }}
            </div>
        </div>
    </div>
</div>

@include('admin.quizzes._import_modal')

<script>
    function openImportModal() {
        document.getElementById('importModal').classList.remove('hidden');
    }
    function closeImportModal() {
        document.getElementById('importModal').classList.add('hidden');
    }
</script>

@endsection
