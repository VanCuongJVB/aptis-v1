@extends('layouts.app')

@section('title', isset($set) ? $set->title : 'Set questions')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold">{{ $set->title }}</h2>
                <div class="text-sm text-gray-500">{{ $set->description }}</div>
            </div>
            <div>
                <a href="{{ route('listening.quiz.start', ['quiz' => $set->quiz_id, 'set_id' => $set->id]) }}" class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 shadow">Start set</a>
            </div>
        </div>

        <table class="min-w-full table-auto">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Question</th>
                    <th class="px-4 py-2 text-left">Type</th>
                    <th class="px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($set->questions as $question)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $question->order }}</td>
                        <td class="px-4 py-2">{{ 
                            Str::limit(strip_tags($question->stem ?? $question->title ?? ''), 120) }}</td>
                        <td class="px-4 py-2">{{ $question->type }}</td>
                            <td class="px-4 py-2">
                            <a href="{{ route('listening.sets.show', $set->id) }}" class="text-blue-600 hover:underline">Xem</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
