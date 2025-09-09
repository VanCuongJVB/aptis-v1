@extends('layouts.app')

@section('title', 'Listening Sets')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-lg font-semibold mb-4">Listening Sets</h2>
        <div class="grid grid-cols-1 gap-4">
            @foreach($sets as $set)
                <div class="p-4 border rounded flex justify-between items-center">
                    <div>
                        <div class="font-semibold">{{ $set->title }}</div>
                        <div class="text-sm text-gray-500">{{ $set->description }}</div>
                    </div>
                    <div>
                        <a href="{{ route('listening.practice.start', ['quiz' => $set->quiz_id, 'set_id' => $set->id]) }}" class="btn">Bắt đầu</a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
