<!doctype html>
<html lang="vi">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'APTIS Lite')</title>
  {{--
  <script src="https://cdn.tailwindcss.com"></script> --}}
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 text-slate-800">
  <nav
    class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-slate-200 dark:bg-gray-900 dark:border-gray-700">
    <div class="max-w-6xl mx-auto px-4 h-14 flex justify-between items-center">
      {{-- Logo --}}
      <a href="/" class="flex items-center gap-2 font-bold text-slate-800 dark:text-white">
        <span class="inline-block w-3 h-3 rounded-full bg-indigo-500"></span>
        <span>APTIS Lite</span>
      </a>

      {{-- User --}}
      <div class="flex items-center gap-2">
        @auth
          @if(auth()->user()->is_admin)
            <a href="{{ route('admin.home') }}"
              class="px-3 py-1.5 rounded-lg text-sm border border-slate-200 bg-white text-slate-700 hover:text-slate-900 hover:bg-slate-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:text-white">
              Quản lý của giáo viên
            </a>
          @endif
          <span class="hidden md:inline text-sm text-slate-600 dark:text-gray-400">
            Hi, {{ auth()->user()->name ?? auth()->user()->email }}
          </span>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
              class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-sm shadow-sm hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-500">
              Đăng xuất
            </button>
          </form>
        @endauth

        @guest
          <a href="{{ route('login') }}"
            class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-sm shadow-sm hover:bg-slate-700 dark:bg-indigo-600 dark:hover:bg-indigo-500">
            Login
          </a>
        @endguest
      </div>
    </div>

    {{-- Admin tabs --}}
    @if (request()->is('admin*'))
      <div class="bg-slate-50 border-t border-slate-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="max-w-6xl mx-auto px-4 flex gap-4 text-sm font-medium">
          <a href="{{ route('admin.quizzes.index') }}"
            class="px-3 py-2 border-b-2 transition-colors {{ request()->routeIs('admin.quizzes.*')
      ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 text-white'
      : 'border-transparent text-slate-700 hover:text-slate-900 hover:border-slate-300 dark:text-gray-400 dark:hover:text-white' }}">
            Quizzes
          </a>
          <a href="{{ route('admin.students.index') }}"
            class="px-3 py-2 border-b-2 transition-colors {{ (request()->routeIs('admin.students.*') && !request()->routeIs('admin.students.import.*'))
      ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 text-white'
      : 'border-transparent text-slate-700 hover:text-slate-900 hover:border-slate-300 dark:text-gray-400 dark:hover:text-white' }}">
            Students
          </a>
          <a href="{{ route('admin.students.import.form') }}"
            class="px-3 py-2 border-b-2 transition-colors {{ request()->routeIs('admin.students.import.*')
      ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 text-white'
      : 'border-transparent text-slate-700 hover:text-slate-900 hover:border-slate-300 dark:text-gray-400 dark:hover:text-white' }}">
            Import
          </a>
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