@extends('layouts.app')

@section('title', $set->exists ? 'Edit Set' : 'Create Set')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">{{ $set->exists ? 'Edit Set' : 'Create Set' }}</h1>

    <form method="POST" action="{{ $set->exists ? route('admin.sets.update', $set) : route('admin.sets.store') }}">
            @csrf
            @if($set->exists) @method('PUT') @endif

            <div class="mb-4">
                <label class="block text-sm">Title</label>
                <input name="title" value="{{ old('title', $set->title) }}" class="w-full border p-2 rounded" />
            </div>

            <div class="mb-4">
                <label class="block text-sm">Quiz</label>
                <select name="quiz_id" class="w-full border p-2 rounded" @if($set->exists) disabled @endif required>
                    <option value="">-- Chọn quiz --</option>
                    @foreach($quizzes as $q)
                        <option value="{{ $q->id }}" {{ $q->id == old('quiz_id', $set->quiz_id) ? 'selected' : '' }}>{{ $q->title }}</option>
                    @endforeach
                </select>
                @if($set->exists)
                    <input type="hidden" name="quiz_id" value="{{ $set->quiz_id }}" />
                @endif
            </div>

            <div class="mb-4">
                <label class="block text-sm">Kỹ năng</label>
                <select name="skill" class="w-full border p-2 rounded" @if($set->exists) disabled @endif required>
                    <option value="">-- Chọn kỹ năng --</option>
                    <option value="reading" {{ old('skill', $set->skill) == 'reading' ? 'selected' : '' }}>Đọc hiểu</option>
                    <option value="listening" {{ old('skill', $set->skill) == 'listening' ? 'selected' : '' }}>Nghe hiểu</option>
                </select>
                @if($set->exists)
                    <input type="hidden" name="skill" value="{{ $set->skill }}" />
                @endif
            </div>

            <div class="flex items-center justify-end">
                {{-- <a href="{{ route('admin.quizzes.sets') }}" class="mr-2 px-4 py-2 border rounded">Cancel</a> --}}
                <a href="{{ route('admin.quizzes.sets') }}" class="mr-2 px-4 py-2 border rounded">Cancel</a>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
