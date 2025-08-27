@extends('layouts.app')
@section('title','Thêm học sinh')
@section('content')
  <div class="bg-white p-6 rounded shadow max-w-xl">
    <h1 class="text-xl font-bold mb-4">Thêm học sinh</h1>
    <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-3">
      @csrf
      @include('admin.students.partials.form', ['student' => null])
      <div class="text-sm text-slate-500">Mật khẩu mặc định: <code>123456</code> (không có UI đổi).</div>
      <button class="px-4 py-2 rounded bg-red-600 text-white mt-2">Lưu</button>
    </form>
  </div>
@endsection
