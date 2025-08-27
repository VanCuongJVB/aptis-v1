@extends('layouts.app')
@section('title','Sửa học sinh')
@section('content')
  <div class="bg-white p-6 rounded shadow max-w-xl">
    <h1 class="text-xl font-bold mb-4">Sửa học sinh</h1>
    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-3">
      @csrf @method('PUT')
      @include('admin.students.partials.form', ['student' => $student])
      <div class="text-sm text-slate-500">Mật khẩu cố định: <code>123456</code></div>
      <div class="flex gap-2 mt-2">
        <form method="POST" action="{{ route('admin.students.extend', $student) }}?days=30">@csrf
          <button class="px-3 py-2 rounded bg-emerald-600 text-white">+30d</button>
        </form>
        <form method="POST" action="{{ route('admin.students.extend', $student) }}?days=90">@csrf
          <button class="px-3 py-2 rounded bg-emerald-700 text-white">+90d</button>
        </form>
      </div>
      <a href="{{ route('admin.students.index') }}" class="inline-block mt-2 text-red-600">← Quay lại danh sách</a>
    </form>
  </div>
@endsection
