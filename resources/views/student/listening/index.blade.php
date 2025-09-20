@extends('layouts.app')

@section('title', 'Listening Sets')

@section('content')
<div class="container mx-auto py-6 px-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Chọn bộ đề Listening</h1>
        <p class="text-sm text-gray-500">{{ $sets->count() }} bộ đề công khai</p>
    </div>

    @if($sets->isEmpty())
        <div class="p-6 bg-white border rounded shadow-sm text-center text-gray-600">Hiện chưa có bộ đề công khai cho Listening.</div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($sets as $set)
                <div class="bg-white border rounded-lg shadow-sm overflow-hidden flex flex-col">
                    <div class="p-4 flex-1">
                        <h2 class="text-lg font-semibold">{{ $set->title ?? ("Bộ đề #{$set->id}") }}</h2>
                        @if($set->description)
                            <p class="mt-2 text-sm text-gray-600">{{ \Illuminate\Support\Str::limit($set->description, 140) }}</p>
                        @endif

                        <div class="mt-4 text-sm text-gray-500 space-y-1">
                            <div>Quiz: <strong class="text-gray-700">{{ optional($set->quiz)->title ?? '—' }}</strong></div>
                            <div>Số câu: <strong class="text-gray-700">{{ $set->questions->count() }}</strong></div>
                            <div>Trạng thái: <strong class="text-gray-700">{{ $set->is_public ? 'Công khai' : 'Riêng tư' }}</strong></div>
                        </div>
                    </div>

                    <div class="p-4 border-t bg-gray-50 flex items-center justify-between">
                        <a href="{{ route('listening.quiz.start', ['quiz' => $set->quiz_id, 'set_id' => $set->id]) }}" class="btn-base btn-primary px-4 py-2">Làm bài</a>
                        {{-- <a href="{{ route('listening.sets.show', $set->id) }}" class="text-sm text-gray-700 hover:underline">Xem bộ đề</a> --}}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
