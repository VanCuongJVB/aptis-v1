@extends('layouts.app')

@section('title', $set->title ?? 'Bộ đề Reading')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">{{ $set->title }}</h1>
    <p class="text-gray-700 mb-4">{{ $set->description }}</p>

    <div class="space-x-2">
        <a href="{{ route('reading.practice.start', ['quiz' => $set->quiz_id, 'set_id' => $set->id, 'mode' => 'learning']) }}" class="btn btn-primary">Luyện (Learning)</a>
        <a href="{{ route('reading.practice.start', ['quiz' => $set->quiz_id, 'set_id' => $set->id, 'mode' => 'exam']) }}" class="btn">Thi (Exam)</a>
    </div>

    <hr class="my-6">

    <h3 class="font-semibold mb-2">Câu hỏi trong bộ đề ({{ $set->questions->count() }})</h3>
    <ol class="list-decimal ml-6">
        @foreach($set->questions as $q)
            <li class="mb-1">{{ $q->title ?? 'Câu hỏi' }} <span class="text-sm text-gray-500">(Part {{ $q->part }})</span></li>
        @endforeach
    </ol>
</div>
@endsection
