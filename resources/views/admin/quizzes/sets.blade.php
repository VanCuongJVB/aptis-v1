@extends('layouts.app')

@section('title', 'Sets Management')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Sets — Quản lý</h1>
            <a href="{{ route('admin.sets.create') }}" class="px-3 py-2 bg-green-600 text-white rounded">Tạo Set mới</a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded shadow p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">STT
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quiz
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Questions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($sets as $set)
                            <tr class="even:bg-gray-50">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $sets->firstItem() + $loop->index }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $set->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ optional($set->quiz)->title ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $set->quiz ? $set->quiz->part : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
                                    {{ $set->questions()->count() }}
                                    <a href="{{ route('admin.questions.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                    <a href="{{ route('admin.sets.questions', $set->id) }}" class="ml-2 px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Xem câu hỏi</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <a href="{{ route('admin.sets.edit', $set) }}"
                                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-indigo-700 bg-indigo-50 hover:bg-indigo-100">Edit</a>
                                    <form action="{{ route('admin.sets.destroy', $set) }}" method="POST" style="display:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="inline-flex items-center px-3 py-1 ml-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100"
                                            onclick="return confirm('Delete this set?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">Không có set nào</td>
                            </tr>
                        @endforelse

                        {{-- Không còn accordion, chuyển sang page mới --}}
                    </tbody>
                </table>

            {{-- Không còn JS accordion --}}

            <div class="mt-4 w-100">
                <div class="flex justify-between">
                    <style>
                        nav {
                            width: 100% !important;
                        }
                    </style>
                    {{ $sets->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection