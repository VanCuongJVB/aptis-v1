@extends('layouts.app')

@section('title', $set->exists ? 'Edit Set' : 'Create Set')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-xl mx-auto bg-white rounded shadow p-6">
        <h1 class="text-xl font-semibold mb-4">{{ $set->exists ? 'Edit Set' : 'Create Set' }}</h1>

        {{-- <form method="POST" action="{{ $set->exists ? route('admin.sets.update', $set) : route('admin.sets.store') }}"> --}}
        <form method="" action="#">
            @csrf
            @if($set->exists) @method('PUT') @endif

            <div class="mb-4">
                <label class="block text-sm">Title</label>
                <input name="title" value="{{ old('title', $set->title) }}" class="w-full border p-2 rounded" />
            </div>

            <div class="mb-4">
                <label class="block text-sm">Quiz (optional)</label>
                <select name="quiz_id" class="w-full border p-2 rounded">
                    <option value="">-- None --</option>
                    @foreach($quizzes as $q)
                        <option value="{{ $q->id }}" {{ $q->id == old('quiz_id', $set->quiz_id) ? 'selected' : '' }}>{{ $q->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm">Skill</label>
                <input name="skill" value="{{ old('skill', $set->skill) }}" class="w-full border p-2 rounded" />
            </div>

            <div class="mb-4">
                <label class="block text-sm">Order</label>
                <input type="number" name="order" value="{{ old('order', $set->order) }}" class="w-full border p-2 rounded" />
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
