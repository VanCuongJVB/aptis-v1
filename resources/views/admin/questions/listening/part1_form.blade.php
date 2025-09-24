@extends('layouts.app')

@section('title', $question->exists ? 'Edit Listening Part 1' : 'Create Listening Part 1')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">{{ $question->exists ? 'Edit Listening Part 1' : 'Create Listening Part 1' }}</h1>
        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" enctype="multipart/form-data" action="{{ $question->exists ? route('admin.questions.listening.part1.update', $question) : route('admin.questions.listening.part1.store') }}">
            @csrf
            @if($question->exists) @method('PUT') @endif

            <div class="mb-4">
                <label class="block text-sm">Quiz</label>
                @if(!$question->exists)
                    <select name="quiz_id" id="quiz_id_select" class="w-full border p-2 rounded" required>
                        <option value="">-- Chọn quiz --</option>
                        @foreach($quizzes as $quiz)
                            <option value="{{ $quiz->id }}" {{ old('quiz_id', request('quiz_id')) == $quiz->id ? 'selected' : '' }}>{{ $quiz->title }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="w-full border p-2 rounded bg-gray-100" value="{{ optional($question->quiz)->title }}" readonly />
                    <input type="hidden" name="quiz_id" value="{{ $question->quiz_id }}" />
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm">Set</label>
                @if(!$question->exists)
                    <select name="reading_set_id" id="set_id_select" class="w-full border p-2 rounded">
                        <option value="">-- Chọn set --</option>
                        @foreach($sets as $set)
                            <option value="{{ $set->id }}" {{ old('reading_set_id', request('reading_set_id')) == $set->id ? 'selected' : '' }}>{{ $set->title }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="w-full border p-2 rounded bg-gray-100" value="{{ optional($question->readingSet)->title }}" readonly />
                    <input type="hidden" name="reading_set_id" value="{{ $question->reading_set_id }}" />
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm">Nội dung câu hỏi (stem)</label>
                <textarea name="stem" class="w-full border p-2 rounded" required>{{ old('stem', $question->stem) }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm">Mô tả (audio)</label>
                <textarea name="description" class="w-full border p-2 rounded">{{ old('description', $question->metadata['description'] ?? '') }}</textarea>
            </div>

            <div class="mb-4">
                <label class="block text-sm">Audio (mp3/wav)</label>
                <input type="file" name="audio" accept="audio/mp3,audio/wav" class="w-full border p-2 rounded" />
                @if($question->audio_path)
                    <audio controls class="mt-2">
                        <source src="{{ asset('storage/' . $question->audio_path) }}" type="audio/mpeg">
                        Trình duyệt không hỗ trợ audio.
                    </audio>
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm">Các lựa chọn đáp án</label>
                @php
                    $options = old('options', $question->metadata['options'] ?? []);
                @endphp
                @for($i = 0; $i < 3; $i++)
                    <input type="text" name="options[]" class="w-full border p-2 rounded mb-2" value="{{ $options[$i] ?? '' }}" placeholder="Đáp án {{ $i+1 }}" required />
                @endfor
            </div>

            <div class="mb-4">
                <label class="block text-sm">Đáp án đúng</label>
                @php
                    $correct = old('correct_index', $question->metadata['correct_index'] ?? 0);
                @endphp
                <select name="correct_index" class="w-full border p-2 rounded" required>
                    @for($i = 0; $i < 3; $i++)
                        <option value="{{ $i }}" {{ $correct == $i ? 'selected' : '' }}>Đáp án {{ $i+1 }}</option>
                    @endfor
                </select>
            </div>

            <div class="flex items-center justify-end">
                <a href="{{ route('admin.quizzes.questions') }}" class="mr-2 px-4 py-2 border rounded">Cancel</a>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
