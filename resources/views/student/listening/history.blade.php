@extends('layouts.app')

@section('title', 'Lịch sử Listening')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Lịch sử luyện tập Listening</h2>
        <div class="space-y-3">
            @foreach($attempts as $attempt)
                <div class="p-3 border rounded flex justify-between items-center">
                    <div>
                        <div class="font-semibold">{{ $attempt->quiz->title }} — Part {{ $attempt->quiz->part }}</div>
                        <div class="text-sm text-gray-500">{{ $attempt->submitted_at ? $attempt->submitted_at->format('H:i d/m/Y') : '' }}</div>
                    </div>
                    <div>
                        <a href="{{ route('listening.practice.result', $attempt) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Xem</a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">{{ $attempts->links() }}</div>
    </div>
</div>
@endsection
