<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'APTIS Lite')</title>
  {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800">
  <nav class="bg-white border-b">
    <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
      <a href="/" class="font-bold">APTIS Lite</a>
      <div class="space-x-2">
        @auth
          @if(auth()->user()->is_admin)
            <a class="px-3 py-1 rounded border hover:bg-slate-100" href="{{ route('admin.home') }}">Admin</a>
          @endif
          <span class="text-sm hidden md:inline">Hi, {{ auth()->user()->name ?? auth()->user()->email }}</span>
          <form class="inline" method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="px-3 py-1 bg-slate-800 text-white rounded">Logout</button>
          </form>
        @endauth
        @guest
          <a class="px-3 py-1 bg-slate-800 text-white rounded" href="{{ route('login') }}">Login</a>
        @endguest
      </div>
    </div>

    @if (request()->is('admin*'))
      <div class="bg-slate-100 border-t">
        <div class="max-w-6xl mx-auto px-4 py-2 flex gap-2 text-sm">
          <a href="{{ route('admin.quizzes.index') }}"
            class="px-3 py-1 rounded {{ request()->routeIs('admin.quizzes.*') ? 'bg-white border' : 'hover:bg-white/60' }}">Quizzes</a>
          <a href="{{ route('admin.students.index') }}"
            class="px-3 py-1 rounded {{ request()->routeIs('admin.students.*') && !request()->routeIs('admin.students.import.*') ? 'bg-white border' : 'hover:bg-white/60' }}">Students</a>
          <a href="{{ route('admin.students.import.form') }}"
            class="px-3 py-1 rounded {{ request()->routeIs('admin.students.import.*') ? 'bg-white border' : 'hover:bg-white/60' }}">Import</a>
        </div>
      </div>
    @endif
  </nav>

  <main class="max-w-6xl mx-auto p-4">
    @if ($errors->any())
      <div class="mb-3 p-3 rounded bg-red-100 border border-red-300 text-sm">
        @foreach ($errors->all() as $e)
          <div>{{ $e }}</div>
        @endforeach
      </div>
    @endif
    @if(session('ok'))
      <div class="mb-3 p-3 rounded bg-green-100 border border-green-300">{{ session('ok') }}</div>
    @endif
    @yield('content')
  </main>
</body>

</html>