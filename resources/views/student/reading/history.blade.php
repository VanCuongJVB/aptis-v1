@extends('layouts.app')

@section('title', 'Lịch sử luyện tập Reading')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Lịch sử luyện tập</h2>

        @if($attempts->isEmpty())
            <div class="text-gray-600">Bạn chưa có lượt làm bài nào.</div>
        @else
            <ul class="space-y-3">
                @foreach($attempts as $attempt)
                    <li class="p-3 border rounded flex justify-between items-center">
                        <div>
                            <div class="font-medium">{{ $attempt->quiz->title }}</div>
                            <div class="text-sm text-gray-600">Part {{ $attempt->quiz->part }} • {{ $attempt->submitted_at ? $attempt->submitted_at->format('d/m/Y H:i') : '-' }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-lg font-bold">{{ $attempt->score_percentage ?? 0 }}%</div>
                            <div class="text-sm"><a href="{{ route('reading.practice.result', $attempt) }}" class="text-blue-600">Xem</a></div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4">
                {{ $attempts->links() }}
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('student.reading.dashboard') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Quay lại</a>
        </div>
    </div>
</div>
@endsection
