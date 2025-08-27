@extends('layouts.app')
@section('title','Đăng nhập')
@section('content')
  <div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-4">Đăng nhập</h1>
    <form method="POST" action="{{ route('login.post') }}" class="space-y-3">
      @csrf
      <label class="block">
        <span class="text-sm">Email</span>
        <input type="email" name="email" class="w-full border rounded px-3 py-2" required value="{{ old('email') }}">
      </label>
      <label class="block">
        <span class="text-sm">Mật khẩu</span>
        <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
      </label>
      <button class="px-4 py-2 rounded bg-red-600 text-white">Đăng nhập</button>
    </form>
    <div class="mt-4 text-sm text-slate-600">
      Demo: <code>admin@example.com</code> / <code>123456</code> • <code>student@example.com</code> / <code>123456</code>
    </div>
  </div>
@endsection
