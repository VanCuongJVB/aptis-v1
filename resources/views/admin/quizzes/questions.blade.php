@extends('layouts.app')

@section('title', 'Questions Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Questions — Quản lý</h1>
        <div class="flex items-center space-x-2">
            {{-- <a href="{{ route('admin.questions.create') }}" class="px-3 py-2 bg-green-600 text-white rounded">Tạo Question</a> --}}
            <a href="{{ route('admin.quizzes.coming') }}" class="px-3 py-2 bg-green-600 text-white rounded">Tạo Question</a>
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
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz / Set</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($questions as $q)
                    <tr class="even:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">{{ $questions->firstItem() + $loop->index }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ Str::limit($q->stem ?? $q->title ?? '-', 140) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $q->type ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ optional($q->quiz)->title ?? (optional($q->readingSet)->title ?? '-') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <a href="{{ route('admin.questions.edit', $q) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Edit</a>
                            <form action="{{ route('admin.questions.destroy', $q) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center px-3 py-1 ml-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100" onclick="return confirm('Delete this question?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">Không có câu hỏi nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 w-100">
            <div class="flex justify-between">
                <style>
                    nav {
                        width: 100% !important;
                    }
                </style>
                {{ $questions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
