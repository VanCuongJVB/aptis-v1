@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Edit Reading Question</h1>
            <p class="mt-1 text-sm text-gray-600">
                Quiz: {{ $quiz->title }} | Part {{ $question->part }}
            </p>
        </div>

        <form action="{{ route('admin.questions.update', $question) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <input type="hidden" name="part" value="{{ $question->part }}">
            <input type="hidden" name="type" value="{{ $question->type }}">

            <div class="bg-white shadow sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    @include("admin.reading.parts._part{$question->part}")
                </div>

                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <a href="{{ route('admin.reading.edit', $quiz) }}" 
                       class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
