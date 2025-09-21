@extends('layouts.app')

@section('title', 'Danh sách câu hỏi của Set: ' . ($set->title ?? ''))

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Danh sách câu hỏi của Set: {{ $set->title }}</h1>
    <a href="{{ route('admin.questions.part1.create', ['reading_set_id' => $set->id]) }}" class="px-3 py-2 bg-green-600 text-white rounded">Tạo Question mới</a>
    </div>
    <div class="mb-4 text-gray-600">Quiz: <b>{{ optional($set->quiz)->title ?? '-' }}</b></div>
    <div class="bg-white rounded shadow p-4">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($set->questions as $q)
                    <tr class="even:bg-gray-50">
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ Str::limit($q->stem ?? $q->title ?? '-', 140) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $q->type ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                            <a href="{{ route('admin.questions.part1.edit', $q) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Edit</a>
                            <form action="{{ route('admin.questions.part1.destroy', $q) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex items-center px-3 py-1 ml-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100" onclick="return confirm('Delete this question?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">Không có câu hỏi nào</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        <a href="{{ route('admin.quizzes.sets') }}" class="text-blue-600 underline">&larr; Quay lại danh sách Sets</a>
    </div>
</div>
@endsection
