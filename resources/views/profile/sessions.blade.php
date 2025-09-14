<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Các thiết bị đăng nhập') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Quản lý phiên đăng nhập của bạn') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Xem và quản lý tất cả các thiết bị đang đăng nhập vào tài khoản của bạn.') }}
                            </p>
                        </header>

                        @if (session('status'))
                            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="mt-6">
                            <form method="POST" action="{{ route('profile.sessions.logout-others') }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500" onclick="return confirm('Bạn có chắc muốn đăng xuất khỏi tất cả các thiết bị khác?')">
                                    {{ __('Đăng xuất khỏi các thiết bị khác') }}
                                </button>
                            </form>
                        </div>

                        <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            {{ __('Thiết bị') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            {{ __('Địa chỉ IP') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            {{ __('Hoạt động lần cuối') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            {{ __('Thời gian đăng nhập') }}
                                        </th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            {{ __('Thao tác') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @forelse ($sessions as $session)
                                        <tr class="{{ $session->device_fingerprint === $currentSessionId ? 'bg-green-50' : '' }}">
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                                <div class="font-medium">{{ $session->device_name }}</div>
                                                <div class="text-gray-500 text-xs">
                                                    {{ $session->device_fingerprint === $currentSessionId ? '(Thiết bị hiện tại)' : '' }}
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $session->ip_address }}
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{ $session->last_active_at ? $session->last_active_at->diffForHumans() : '—' }}
                                                <div class="text-xs">
                                                    {{ $session->last_active_at ? $session->last_active_at->format('d/m/Y H:i:s') : '—' }}
                                                </div>
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                {{-- created_at is when the session record was created (login time) --}}
                                                {{ $session->created_at ? $session->created_at->diffForHumans() : '—' }}
                                                <div class="text-xs">
                                                    {{ $session->created_at ? $session->created_at->format('d/m/Y H:i:s') : '—' }}
                                                </div>
                                                @if($session->revoked_at)
                                                    <div class="text-xs text-red-600">(Đã bị thu hồi: {{ $session->revoked_at->diffForHumans() }})</div>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <form method="POST" action="{{ route('profile.sessions.destroy', $session) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900" {{ $session->device_fingerprint === $currentSessionId ? 'onclick="return confirm(\'Bạn đang đăng xuất khỏi thiết bị hiện tại. Bạn có chắc chắn không?\')"' : '' }}>
                                                        {{ __('Đăng xuất') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">
                                                {{ __('Không có phiên đăng nhập nào.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-sm text-gray-600">
                            <p>{{ __('Lưu ý: Bạn chỉ được phép đăng nhập tối đa trên 2 thiết bị cùng lúc.') }}</p>
                            <p class="mt-2">{{ __('Nếu bạn đăng nhập trên thiết bị thứ 3, phiên đăng nhập cũ nhất sẽ tự động bị đăng xuất.') }}</p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
