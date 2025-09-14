@extends('layouts.app')

@section('title', 'Tiến độ luyện Reading')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Tiến độ luyện Reading</h1>
            <p class="text-sm text-slate-500">Tổng quan tiến độ và điểm từng phần.</p>
        </div>
        <div>
            <a href="{{ route('student.reading.history') }}" class="inline-flex items-center px-3 py-2 bg-white border border-gray-200 rounded-md text-sm text-slate-700 hover:bg-gray-50">Lịch sử làm bài</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="col-span-1 lg:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium">Tổng quan</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-3 bg-slate-50 rounded">
                    <div class="text-xs text-slate-500">Bộ đề</div>
                    <div class="text-xl font-bold">{{ $overall['total_quizzes'] }}</div>
                </div>
                <div class="p-3 bg-slate-50 rounded">
                    <div class="text-xs text-slate-500">Lượt làm</div>
                    <div class="text-xl font-bold">{{ $overall['total_attempts'] }}</div>
                </div>
                <div class="p-3 bg-slate-50 rounded">
                    <div class="text-xs text-slate-500">Đã nộp</div>
                    <div class="text-xl font-bold">{{ $overall['completed_attempts'] }}</div>
                </div>
                <div class="p-3 bg-slate-50 rounded">
                    <div class="text-xs text-slate-500">Điểm TB</div>
                    <div class="text-xl font-bold">{{ $overall['avg_score'] ? $overall['avg_score'] . '%' : '-' }}</div>
                </div>
            </div>

            <div class="mt-6">
                <h3 class="text-sm font-medium text-slate-700">Tiến độ tổng thể</h3>
                @php
                    $completed = $overall['completed_attempts'] ?? 0;
                    $total = $overall['total_quizzes'] > 0 ? $overall['total_quizzes'] : 0;
                    $pct = $total > 0 ? min(100, round(($completed / $total) * 100)) : 0;
                @endphp
                <div class="w-full bg-slate-100 h-3 rounded mt-2">
                    <div class="h-3 bg-emerald-500 rounded" style="width: {{ $pct }}%"></div>
                </div>
                <div class="text-xs text-slate-500 mt-2">Hoàn thành {{ $pct }}% ({{ $completed }} / {{ $total }})</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium">Mẹo nhanh</h3>
            <ul class="mt-3 text-sm text-slate-600 space-y-2">
                <li>• Làm thử mỗi phần ít nhất một bộ đề mỗi ngày.</li>
                <li>• Kiểm tra kỹ phần trả lời sai để cải thiện.</li>
                <li>• Sử dụng lịch sử để xem tiến độ và so sánh.</li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($parts as $part => $data)
            @php
                $totalQ = $data['total_quizzes'] ?? 0;
                $completed = $data['completed_attempts'] ?? 0;
                $progressPct = $totalQ > 0 ? min(100, round(($completed / $totalQ) * 100)) : 0;
            @endphp
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-semibold">Phần {{ $part }}</h4>
                        <div class="text-xs text-slate-500">Bộ đề: {{ $totalQ }} • Làm: {{ $data['total_attempts'] }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold">{{ $data['avg_score'] ? $data['avg_score'] . '%' : '-' }}</div>
                        <div class="text-xs text-slate-400">Điểm TB</div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="w-full bg-slate-100 h-2 rounded">
                        <div class="h-2 bg-indigo-500 rounded" style="width: {{ $progressPct }}%"></div>
                    </div>
                    <div class="text-xs text-slate-500 mt-2">Hoàn thành {{ $progressPct }}% ({{ $completed }} / {{ $totalQ }})</div>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <div class="text-xs text-slate-500">Lần làm gần nhất: {{ $data['last_attempt_at'] ? $data['last_attempt_at']->format('Y-m-d') : '-' }}</div>
                    <a href="{{ route('student.reading.sets.index') }}?part={{ $part }}" class="text-xs text-indigo-600 hover:underline">Xem bộ đề</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
