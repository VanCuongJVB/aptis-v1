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

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Trang chủ') }}
                    </x-nav-link>
                    
                    @if(Auth::user()->is_admin)
                        <!-- Quản lý học sinh -->
                        <x-nav-link :href="route('admin.students.index')" :active="request()->routeIs('admin.students.*')">
                            {{ __('Quản lý học sinh') }}
                        </x-nav-link>
                        
                        <!-- Quản lý người dùng -->
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Quản lý người dùng') }}
                        </x-nav-link>
                        
                        <!-- Quản lý Reading -->
                        <x-nav-link :href="route('admin.reading.index')" :active="request()->routeIs('admin.reading.*')">
                            {{ __('Quản lý Reading') }}
                        </x-nav-link>
                        
                        <!-- Quản lý Listening -->
                        <x-nav-link :href="route('admin.listening.index')" :active="request()->routeIs('admin.listening.*')">
                            {{ __('Quản lý Listening') }}
                        </x-nav-link>
                        
                        <!-- Quản lý APTIS -->
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium leading-4 rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('admin.aptis.*') ? 'border-indigo-400 text-gray-900 focus:border-indigo-700' : '' }}">
                                        <span>{{ __('Quản lý APTIS') }}</span>
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.reading.index')">
                                        {{ __('Đề thi Reading') }}
                                    </x-dropdown-link>
                                    
                                    <x-dropdown-link :href="route('admin.listening.index')">
                                        {{ __('Đề thi Listening') }}
                                    </x-dropdown-link>
                                    
                                    <x-dropdown-link :href="route('admin.students.index')">
                                        {{ __('Quản lý học sinh') }}
                                    </x-dropdown-link>
                                    
                                    <x-dropdown-link :href="route('admin.users.index')">
                                        {{ __('Quản lý người dùng') }}
                                    </x-dropdown-link>
                                    
                                    <x-dropdown-link :href="route('dashboard')">
                                        {{ __('Thống kê hệ thống') }}
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    @else
                        <!-- Dashboard học sinh -->
                        <x-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                            {{ __('Bảng điều khiển') }}
                        </x-nav-link>
                        
                        <!-- Reading Practice -->
                        <x-nav-link :href="route('reading.drill.part', 1)" :active="request()->routeIs('reading.drill.*')">
                            {{ __('Luyện tập Reading') }}
                        </x-nav-link>
                        
                        @if(Route::has('listening.index'))
                        <!-- Listening Practice (coming soon) -->
                        <x-nav-link :href="route('listening.index')" :active="request()->routeIs('listening.*')">
                            {{ __('Luyện tập Listening') }}
                        </x-nav-link>
                        @endif
                        
                        <!-- Progress Tracking -->
                        <x-nav-link :href="route('reading.progress')" :active="request()->routeIs('reading.progress')">
                            {{ __('Tiến độ học tập') }}
                        </x-nav-link>
                        
                        <!-- History -->
                        <x-nav-link :href="route('reading.progress.history')" :active="request()->routeIs('reading.progress.history')">
                            {{ __('Lịch sử luyện tập') }}
                        </x-nav-link>
                        
                        <!-- Statistics -->
                        <x-nav-link :href="route('reading.progress.stats')" :active="request()->routeIs('reading.progress.stats')">
                            {{ __('Thống kê chi tiết') }}
                        </x-nav-link>
                        
                        <!-- Attempts History -->
                        <x-nav-link :href="route('student.attempts.history')" :active="request()->routeIs('student.attempts.*')">
                            {{ __('Lịch sử làm bài') }}
                        </x-nav-link>
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
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Trang chủ') }}
            </x-responsive-nav-link>
            
            @if(Auth::user()->is_admin)
                <!-- Quản lý học sinh -->
                <x-responsive-nav-link :href="route('admin.students.index')" :active="request()->routeIs('admin.students.*')">
                    {{ __('Quản lý học sinh') }}
                </x-responsive-nav-link>
                
                <!-- Quản lý người dùng -->
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Quản lý người dùng') }}
                </x-responsive-nav-link>
                
                <!-- Quản lý Reading -->
                <x-responsive-nav-link :href="route('admin.reading.index')" :active="request()->routeIs('admin.reading.*')">
                    {{ __('Quản lý Reading') }}
                </x-responsive-nav-link>
                
                <!-- Quản lý Listening -->
                <x-responsive-nav-link :href="route('admin.listening.index')" :active="request()->routeIs('admin.listening.*')">
                    {{ __('Quản lý Listening') }}
                </x-responsive-nav-link>
                
                <!-- Quản lý APTIS -->
                <div class="pt-2 pb-3 px-4 font-medium text-base text-gray-800">
                    {{ __('Quản lý APTIS') }}
                </div>
                <x-responsive-nav-link :href="route('admin.reading.index')" class="pl-8">
                    {{ __('Đề thi Reading') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.listening.index')" class="pl-8">
                    {{ __('Đề thi Listening') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.students.index')" class="pl-8">
                    {{ __('Quản lý học sinh') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')" class="pl-8">
                    {{ __('Quản lý người dùng') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('dashboard')" class="pl-8">
                    {{ __('Thống kê hệ thống') }}
                </x-responsive-nav-link>
            @else
                <!-- Dashboard học sinh -->
                <x-responsive-nav-link :href="route('student.dashboard')" :active="request()->routeIs('student.dashboard')">
                    {{ __('Bảng điều khiển') }}
                </x-responsive-nav-link>
                
                <!-- Reading Practice -->
                <x-responsive-nav-link :href="route('reading.drill.part', 1)" :active="request()->routeIs('reading.drill.*')">
                    {{ __('Luyện tập Reading') }}
                </x-responsive-nav-link>
                
                @if(Route::has('listening.index'))
                <!-- Listening Practice (coming soon) -->
                <x-responsive-nav-link :href="route('listening.index')" :active="request()->routeIs('listening.*')">
                    {{ __('Luyện tập Listening') }}
                </x-responsive-nav-link>
                @endif
                
                <!-- Progress Tracking -->
                <x-responsive-nav-link :href="route('reading.progress')" :active="request()->routeIs('reading.progress')">
                    {{ __('Tiến độ học tập') }}
                </x-responsive-nav-link>
                
                <!-- History -->
                <x-responsive-nav-link :href="route('reading.progress.history')" :active="request()->routeIs('reading.progress.history')">
                    {{ __('Lịch sử luyện tập') }}
                </x-responsive-nav-link>
                
                <!-- Statistics -->
                <x-responsive-nav-link :href="route('reading.progress.stats')" :active="request()->routeIs('reading.progress.stats')">
                    {{ __('Thống kê chi tiết') }}
                </x-responsive-nav-link>
                
                <!-- Attempts History -->
                <x-responsive-nav-link :href="route('student.attempts.history')" :active="request()->routeIs('student.attempts.*')">
                    {{ __('Lịch sử làm bài') }}
                </x-responsive-nav-link>
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
