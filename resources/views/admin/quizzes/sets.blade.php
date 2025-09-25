@extends('layouts.app')

@section('title', 'Sets Management')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold">Sets</h1>
            <a href="{{ route('admin.sets.create') }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-white shadow hover:bg-emerald-700">
                Tạo Set mới
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4 flex gap-2">
            @php $activeSkill = request('skill', 'reading'); @endphp
            <a href="?skill=reading" class="px-4 py-2 rounded-t bg-white border-b-2 {{ $activeSkill=='reading' ? 'border-blue-600 font-bold text-blue-700' : 'border-transparent text-gray-500' }}">Reading</a>
            <a href="?skill=listening" class="px-4 py-2 rounded-t bg-white border-b-2 {{ $activeSkill=='listening' ? 'border-blue-600 font-bold text-blue-700' : 'border-transparent text-gray-500' }}">Listening</a>
        </div>

        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-4">
            <form method="get" class="mb-6 rounded-2xl border border-slate-200 bg-white/70 p-4 shadow-sm backdrop-blur flex flex-wrap gap-4 items-end">
                <input type="hidden" name="skill" value="{{ $activeSkill }}">
                <div class="min-w-[180px] space-y-1">
                    <label class="block text-xs font-semibold tracking-wide text-slate-600">Tìm kiếm Set</label>
                    <div class="relative">
                        <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z" />
                        </svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Tên set..." class="w-full rounded-xl border border-slate-200 bg-white px-10 py-2.5 text-sm text-slate-800 placeholder-slate-400 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40" />
                    </div>
                </div>
                <div>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500/60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6h18M4 12h16M6 18h12" />
                        </svg>
                        <span>Lọc</span>
                    </button>
                    <a href="?skill={{ $activeSkill }}"
                        class="inline-flex items-center gap-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white shadow hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500/60 ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m-5 0a8.001 8.001 0 0015.356 2m0 0V15m0-4h-5" />
                        </svg>
                        Reset
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Quiz</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Part</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Questions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-100">
                        @php
                            $filteredSets = $sets->filter(function($set) use ($activeSkill) {
                                return optional($set->quiz)->skill === $activeSkill;
                            });
                            $q = trim(request('q'));
                            if ($q !== '') {
                                $filteredSets = $filteredSets->filter(function($set) use ($q) {
                                    return mb_stripos($set->title, $q) !== false;
                                });
                            }
                        @endphp
                        @forelse($filteredSets as $set)
                            <tr class="even:bg-slate-50/60">
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-slate-700">{{ $loop->iteration }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900"><span class="line-clamp-1">{{ $set->title }}</span></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ optional($set->quiz)->title ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">{{ $set->quiz ? $set->quiz->part : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700 text-right">{{ $set->questions()->count() }}
                                    @php
                                        $defaultPart = $set->quiz ? $set->quiz->part : 1;
                                        $defaultSkill = $set->quiz ? $set->quiz->skill : 'reading';
                                    @endphp
                                    @if($defaultSkill === 'reading')
                                        @if($defaultPart == 1)
                                            <a href="{{ route('admin.questions.part1.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @elseif($defaultPart == 2)
                                            <a href="{{ route('admin.questions.part2.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @elseif($defaultPart == 3)
                                            <a href="{{ route('admin.questions.part3.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @elseif($defaultPart == 4)
                                            <a href="{{ route('admin.questions.part4.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @else
                                            <a href="#" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs opacity-60 cursor-not-allowed" title="Không xác định part">Tạo Question</a>
                                        @endif
                                    @elseif($defaultSkill === 'listening')
                                        @if($defaultPart == 1)
                                            <a href="{{ route('admin.questions.listening.part1.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @elseif($defaultPart == 2)
                                            <a href="{{ route('admin.questions.listening.part2.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @elseif($defaultPart == 3)
                                            <a href="{{ route('admin.questions.listening.part3.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @elseif($defaultPart == 4)
                                            <a href="{{ route('admin.questions.listening.part4.create', ['reading_set_id' => $set->id]) }}" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs">Tạo Question</a>
                                        @else
                                            <a href="#" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs opacity-60 cursor-not-allowed" title="Không xác định part">Tạo Question</a>
                                        @endif
                                    @else
                                        <a href="#" class="ml-2 px-2 py-1 bg-blue-600 text-white rounded text-xs opacity-60 cursor-not-allowed" title="Chỉ hỗ trợ tạo câu hỏi cho Reading/Listening">Tạo Question</a>
                                    @endif
                                    <a href="{{ route('admin.sets.questions', [$set->id, 'part' => $set->quiz ? $set->quiz->part : 1]) }}" class="ml-2 px-2 py-1 bg-gray-200 text-gray-800 rounded text-xs">Xem câu hỏi</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    <a href="{{ route('admin.sets.edit', $set) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-700 hover:bg-indigo-100 border border-indigo-200">Edit</a>
                                    <form action="{{ route('admin.sets.destroy', $set) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-3 py-1.5 text-rose-700 hover:bg-rose-100 border border-rose-200 ml-2" onclick="return confirm('Delete this set?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">Không có set nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <style>
                    nav {
                        width: 100% !important;
                    }
                </style>
                {{ $sets->links() }}
            </div>
        </div>
    </div>
@endsection