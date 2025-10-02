<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links: separate Student and Admin dropdowns -->
                <div class="hidden sm:flex sm:items-center sm:ms-10 space-x-3">
                    {{-- Student dropdown (visible to all authenticated users) --}}
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium leading-4 rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none transition ease-in-out duration-150">
                                <span>{{ __('Student') }}</span>
                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('student.dashboard')">
                                {{ __('Bảng điều khiển') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('student.reading.dashboard')">
                                {{ __('Luyện tập Reading') }}
                            </x-dropdown-link>
                            @if(Route::has('student.listening.dashboard'))
                                <x-dropdown-link :href="route('student.listening.dashboard')">
                                    {{ __('Luyện tập Listening') }}
                                </x-dropdown-link>
                            @endif
                            <x-dropdown-link :href="route('reading.progress')">
                                {{ __('Tiến độ học tập') }}
                            </x-dropdown-link>
                            {{-- <x-dropdown-link :href="route('student.attempts.history')">
                                {{ __('Lịch sử làm bài') }}
                            </x-dropdown-link> --}}
                        </x-slot>
                    </x-dropdown>

                    {{-- Admin dropdown (visible only to admins) --}}
                    @if(Auth::user()->is_admin)
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium leading-4 rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none transition ease-in-out duration-150">
                                    <span>{{ __('Admin') }}</span>
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('dashboard')">
                                    {{ __('Thống kê hệ thống') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.quizzes.index')">
                                    {{ __('Quản lý Quizzes') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.quizzes.sets')">
                                    {{ __('Quản lý Sets') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.students.index')">
                                    {{ __('Quản lý học sinh') }}
                                </x-dropdown-link>
                                <x-dropdown-link :href="route('admin.users.index')">
                                    {{ __('Quản lý người dùng') }}
                                </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Thông tin cá nhân') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Đăng xuất') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <div class="px-4 py-2 font-medium text-sm text-gray-700">{{ __('Student') }}</div>
            <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                {{ __('Bảng điều khiển') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('student.reading.dashboard')" :active="request()->routeIs('reading.dashboard')">
                {{ __('Bảng điều khiển Reading') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('student.reading.sets.index')" :active="request()->routeIs('student.reading.*')">
                {{ __('Luyện tập Reading') }}
            </x-responsive-nav-link>
            @if(Route::has('student.listening.dashboard'))
                <x-responsive-nav-link :href="route('student.listening.dashboard')" :active="request()->routeIs('student.listening.*')">
                    {{ __('Luyện tập Listening') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('reading.progress')" :active="request()->routeIs('reading.progress')">
                {{ __('Tiến độ học tập') }}
            </x-responsive-nav-link>
            {{-- <x-responsive-nav-link :href="route('student.attempts.history')" :active="request()->routeIs('student.attempts.*')">
                {{ __('Lịch sử làm bài') }}
            </x-responsive-nav-link> --}}

            <div class="mt-4 px-4 py-2 font-medium text-sm text-gray-700">{{ __('Admin') }}</div>
            @if(Auth::user()->is_admin)
                <!-- Admin Reading/Listening links removed -->
                <x-responsive-nav-link :href="route('admin.quizzes.index')" :active="request()->routeIs('admin.quizzes.*')">
                    {{ __('Quản lý Quizzes') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.students.index')" :active="request()->routeIs('admin.students.*')">
                    {{ __('Quản lý học sinh') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Quản lý người dùng') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Thống kê hệ thống') }}
                </x-responsive-nav-link>
            @else
                <div class="pl-4 text-sm text-gray-500">{{ __('Bạn không có quyền quản trị') }}</div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Thông tin cá nhân') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Đăng xuất') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
