@extends('layouts.app')

@section('title', 'Welcome')

@section('content')
<div class="py-20 text-center bg-gradient-to-r from-red-500 to-pink-500 text-white rounded">
    <h1 class="text-5xl font-bold mb-4">Welcome to APTIS Lite</h1>
    <p class="text-lg mb-8">Practice for your English test with a modern interface.</p>
    @guest
        <a href="{{ route('login') }}" class="px-6 py-3 bg-white text-red-600 font-semibold rounded shadow hover:bg-red-50">Get Started</a>
    @endguest
</div>
@endsection

