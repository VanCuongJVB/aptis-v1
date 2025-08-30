@extends('layouts.app')

@section('title', 'Reading Sets Management')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Reading Sets</h1>
        </div>

        <!-- Parts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach(range(1, 4) as $partNumber)
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-semibold">Part {{ $partNumber }}</h2>
                        <p class="text-sm text-gray-600">
                            {{ $sets[$partNumber]->count() ?? 0 }} sets
                        </p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('admin.reading.sets.part', $partNumber) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Manage Sets
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        @if(isset($sets['unassigned']) && $sets['unassigned']->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Unassigned Sets</h2>
            <div class="bg-white rounded-lg shadow">
                <div class="divide-y">
                    @foreach($sets['unassigned'] as $quiz)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <h3 class="font-medium">{{ $quiz->title }}</h3>
                            <p class="text-sm text-gray-600">{{ $quiz->questions_count }} questions</p>
                        </div>
                        <a href="{{ route('admin.quizzes.edit', $quiz) }}" 
                           class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">
                            Edit
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
