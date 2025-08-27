@extends('layouts.app')
@section('title','Tài khoản tạm khoá')
@section('content')
  <div class="bg-white p-6 rounded shadow">
    <h1 class="text-xl font-bold mb-3">Không thể truy cập</h1>
    <p>{{ session('reason') ?? 'Vui lòng liên hệ quản trị viên.' }}</p>
  </div>
@endsection
