@extends('layouts.app')

@section('title', 'Quizzes')

@section('content')
    <div class="container mx-auto px-4 py-8">
        {{-- Header actions --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Quizzes</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.quizzes.create') }}"
                    class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white shadow hover:bg-emerald-700">
                    Tạo Quiz mới
                </a>
                <button type="button" onclick="openImportModal()"
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Import Quiz (JSON)
                </button>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
                <p class="text-sm text-gray-500">Quizzes</p>
                <p class="text-3xl font-bold">{{ $data['quizzes_count'] ?? '—' }}</p>
                <p class="mt-2 text-sm text-gray-600">Tổng số quiz trong hệ thống</p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
                <p class="text-sm text-gray-500">Sets</p>
                <p class="text-3xl font-bold">{{ $data['sets_count'] ?? '—' }}</p>
                <p class="mt-2 text-sm text-gray-600">Tổng số bộ đề (sets) toàn hệ thống</p>
                <p class="mt-1 text-xs text-gray-500">Hiển thị trên trang này:
                    <b>{{ $data['current_sets_count'] ?? '0' }}</b></p>
            </div>
            <div class="rounded-xl bg-white p-4 shadow-sm border border-slate-200">
                <p class="text-sm text-gray-500">Questions</p>
                <p class="text-3xl font-bold">{{ $data['questions_count'] ?? '—' }}</p>
                <p class="mt-2 text-sm text-gray-600">Tổng số câu hỏi toàn hệ thống</p>
                <p class="mt-1 text-xs text-gray-500">Hiển thị trên trang này:
                    <b>{{ $data['current_questions_count'] ?? '0' }}</b></p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-4">
            {{-- Filter UI --}}
            @php
                $hasFilter = request()->filled('q') || request()->filled('skill') || request()->filled('part') || request()->filled('published');
            @endphp

            <form id="filterForm" method="get"
                class="mb-6 rounded-2xl border border-slate-200 bg-white/70 p-4 shadow-sm backdrop-blur">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
                    {{-- Tìm kiếm --}}
                    <div class="space-y-1">
                        <label for="q" class="block text-xs font-semibold tracking-wide text-slate-600">Tìm kiếm</label>
                        <div class="relative">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                            </svg>
                            <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Tên quiz..." class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" />
                        </div>
                    </div>

                    {{-- Kỹ năng --}}
                    <div class="space-y-1">
                        <label for="skill" class="block text-xs font-semibold tracking-wide text-slate-600">Kỹ năng</label>
                        <select id="skill" name="skill" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800
                                       shadow-sm outline-none transition
                                       focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                            <option value="">Tất cả</option>
                            <option value="reading" {{ request('skill') == 'reading' ? 'selected' : '' }}>Đọc hiểu</option>
                            <option value="listening" {{ request('skill') == 'listening' ? 'selected' : '' }}>Nghe hiểu
                            </option>
                        </select>
                    </div>

                    {{-- Phần --}}
                    <div class="space-y-1">
                        <label for="part" class="block text-xs font-semibold tracking-wide text-slate-600">Phần</label>
                        <select id="part" name="part" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800
                                       shadow-sm outline-none transition
                                       focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                            <option value="">Tất cả</option>
                            @for ($i = 1; $i <= 4; $i++)
                                <option value="{{ $i }}" {{ request('part') == $i ? 'selected' : '' }}>Phần {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- Trạng thái --}}
                    <div class="space-y-1">
                        <label for="published" class="block text-xs font-semibold tracking-wide text-slate-600">Trạng
                            thái</label>
                        <select id="published" name="published" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800
                                       shadow-sm outline-none transition
                                       focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40">
                            <option value="">Tất cả</option>
                            <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Đã xuất bản</option>
                            <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>Chưa xuất bản</option>
                        </select>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-3">
                        <button id="btnFilter" type="submit" class="inline-flex min-w-[110px] items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5
                                   text-sm font-semibold text-white shadow hover:bg-blue-700 active:bg-blue-800
                                   focus:outline-none focus:ring-2 focus:ring-blue-500/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M3 6h18M4 12h16M6 18h12" />
                            </svg>
                            <span>Lọc</span>
                            <svg id="btnFilterSpinner" class="hidden h-5 w-5 animate-spin" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 004 12z" />
                            </svg>
                        </button>

                        @if ($hasFilter)
                            <a href="{{ route('admin.quizzes.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5
                                          text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Xoá bộ lọc
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">#
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">
                                Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">
                                Skill</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Part
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">
                                Published</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @forelse($quizzes as $quiz)
                            <tr class="even:bg-slate-50/60">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ $quizzes->firstItem() + $loop->index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                    <span class="line-clamp-1">{{ $quiz->title }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ ucfirst($quiz->skill) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                    {{ $quiz->part }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($quiz->is_published)
                                        <span
                                            class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-emerald-700 border border-emerald-200 text-xs font-medium">Đã
                                            xuất bản</span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-slate-700 border border-slate-200 text-xs font-medium">Chưa</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}"
                                        class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2"
                                            onclick="return confirm('Delete this quiz?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">Không có quiz nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                <style>
                    nav {
                        width: 100% !important;
                    }
                </style>
                {{ $quizzes->withQueryString()->links() }}
            </div>
        </div>
    </div>

    @include('admin.quizzes._import_modal')

    <script>
        // Chặn double submit & hiện spinner cho nút Lọc
        (function () {
            const form = document.getElementById('filterForm');
            const btn = document.getElementById('btnFilter');
            const spinner = document.getElementById('btnFilterSpinner');

            if (form && btn && spinner) {
                form.addEventListener('submit', function () {
                    btn.disabled = true;
                    spinner.classList.remove('hidden');
                }, { once: true });
            }
        })();

        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }
        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }
    </script>
@endsection