@extends('layouts.app')

@section('title', 'Bộ đề Reading')

@section('content')
<div class="container mx-auto py-6">
    <h1 class="text-2xl font-bold mb-4">Chọn bộ đề Reading</h1>

    @if($sets->isEmpty())
        <p>Hiện chưa có bộ đề công khai cho Reading.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($sets as $set)
                <div class="p-4 border rounded shadow-sm">
                    <h2 class="font-semibold">{{ $set->title ?? ("Bộ đề #{$set->id}") }}</h2>
                    <p class="text-sm text-gray-600">{{ $set->description }}</p>
                    <div class="mt-3 flex items-center space-x-2">
                        <a href="{{ route('reading.practice.start', ['quiz' => $set->quiz_id, 'set_id' => $set->id, 'mode' => 'exam']) }}" class="btn-base btn-primary">Làm bài</a>
                        {{-- <a href="{{ route('student.reading.sets.show', $set->id) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Xem bộ đề</a> --}}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
