@extends('layouts.app')
@section('title','Import Students')
@section('content')
  <div class="bg-white p-6 rounded shadow max-w-xl">
    <h1 class="text-xl font-bold mb-4">Nhập học sinh số lượng nhiều (CSV hoặc XLSX)</h1>
    <form method="POST" action="{{ route('admin.students.import.store') }}" class="space-y-3" enctype="multipart/form-data">
      @csrf
      <input type="file" name="file" required accept=".csv,.txt,.xlsx" class="block">
      <button class="px-4 py-2 rounded bg-blue-600 text-white">Upload</button>
    </form>
    <div class="mt-4 text-sm text-slate-600">
      Mẫu CSV (header): <code>email,name,is_active,access_starts_at,access_ends_at</code>
    </div>
  </div>
@endsection
