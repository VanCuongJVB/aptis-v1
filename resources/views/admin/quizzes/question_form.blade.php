@extends('layouts.app')

@section('title', $question->exists ? 'Edit Question' : 'Create Question')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">{{ $question->exists ? 'Edit Question' : 'Create Question' }}</h1>

        <form method="POST" action="{{ $question->exists ? route('admin.questions.update', $question) : route('admin.questions.store') }}">
            @csrf
            @if($question->exists) @method('PUT') @endif

            <div class="mb-4">
                <label class="block text-sm">Quiz (optional)</label>
                <select name="quiz_id" class="w-full border p-2 rounded">
                    <option value="">-- None --</option>
                    @foreach($quizzes as $q)
                        <option value="{{ $q->id }}" {{ $q->id == old('quiz_id', $question->quiz_id) ? 'selected' : '' }}>{{ $q->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm">Set (optional)</label>
                <select name="reading_set_id" class="w-full border p-2 rounded">
                    <option value="">-- None --</option>
                    @foreach($sets as $s)
                        <option value="{{ $s->id }}" {{ $s->id == old('reading_set_id', $question->reading_set_id) ? 'selected' : '' }}>{{ $s->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm">Stem / Title</label>
                <textarea name="stem" rows="4" class="w-full border p-2 rounded">{{ old('stem', $question->stem ?? $question->title) }}</textarea>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm">Type</label>
                    <input name="type" value="{{ old('type', $question->type) }}" class="w-full border p-2 rounded" />
                </div>
                <div>
                    <label class="block text-sm">Order</label>
                    <input type="number" name="order" value="{{ old('order', $question->order) }}" class="w-full border p-2 rounded" />
                </div>
            </div>

            <div class="flex items-center justify-end">
                <a href="{{ route('admin.quizzes.questions') }}" class="mr-2 px-4 py-2 border rounded">Cancel</a>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
