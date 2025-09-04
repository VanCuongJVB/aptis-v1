<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý học sinh') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
                        <div>
                            <h1 class="text-xl font-bold">Danh sách học sinh</h1>
                            <p class="text-sm text-slate-600">Quản lý học sinh, cửa sổ truy cập và gia hạn nhanh.</p>
                        </div>
                        <div class="flex gap-2">
                            <a class="px-3 py-2 rounded bg-slate-200 hover:bg-slate-300 transition-colors" href="{{ route('admin.students.import.form') }}">Nhập từ file</a>
                            <a class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition-colors" href="{{ route('admin.students.create') }}">+ Thêm học sinh</a>
                        </div>
                    </div>

                    <form method="GET" class="flex flex-col md:flex-row gap-2 md:items-center mb-3">
                        <input type="text" name="s" value="{{ request('s') }}" placeholder="Tìm email hoặc tên..."
                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full md:w-72">
                        <div class="flex gap-1">
                            @php $st = $status ?? request('status'); @endphp
                            @php
                                $tab = function ($key, $label) use ($st) {
                                $url = request()->fullUrlWithQuery(['status' => $key ?: null]);
                                $active = ($st === $key) || (!$st && $key === null);
                                return '<a href="' . $url . '" class="px-3 py-1 rounded ' . ($active ? 'bg-slate-800 text-white' : 'bg-white border hover:bg-slate-50') . '">' . $label . '</a>';
                                };
                            @endphp
                            {!! $tab(null, 'Tất cả') !!}
                            {!! $tab('active', 'Đang kích hoạt') !!}
                            {!! $tab('inactive', 'Bị khoá') !!}
                            {!! $tab('expiring', 'Sắp hết hạn ≤7d') !!}
                            {!! $tab('expired', 'Đã hết hạn') !!}
                        </div>
                        <button class="px-3 py-2 rounded bg-slate-200 hover:bg-slate-300 transition-colors">Lọc</button>
                    </form>

                    <div class="bg-white rounded shadow overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-left">
                                <tr>
                                    <th class="px-3 py-2">Email</th>
                                    <th class="px-3 py-2">Tên</th>
                                    <th class="px-3 py-2">Trạng thái</th>
                                    <th class="px-3 py-2">Thời gian truy cập</th>
                                    <th class="px-3 py-2">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @php $now = \Carbon\Carbon::now(); @endphp
                                @foreach($students as $st)
                                @php
                                    $expired = $st->access_ends_at && $st->access_ends_at->lt($now);
                                    $expiring = $st->access_ends_at && $st->access_ends_at->between($now, (clone $now)->addDays(7));
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 font-medium">{{ $st->email }}</td>
                                    <td class="px-3 py-2">{{ $st->name }}</td>
                                    <td class="px-3 py-2">
                                    @if($st->is_active)
                                        <span class="px-2 py-0.5 rounded bg-green-100 border border-green-300">Kích hoạt</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded bg-slate-200 border">Bị khoá</span>
                                    @endif

                                    @if($expired)
                                        <span class="ml-2 px-2 py-0.5 rounded bg-red-100 border border-red-300">Hết hạn</span>
                                    @elseif($expiring)
                                        <span class="ml-2 px-2 py-0.5 rounded bg-amber-100 border border-amber-300">Sắp hết hạn</span>
                                    @endif
                                    </td>
                                    <td class="px-3 py-2 text-slate-600">
                                    <div class="text-xs">
                                        @if($st->access_starts_at) Từ: {{ $st->access_starts_at->format('Y-m-d H:i') }} @endif
                                        @if($st->access_ends_at) • Đến: {{ $st->access_ends_at->format('Y-m-d H:i') }} @endif
                                    </div>
                                    </td>
                                    <td class="px-3 py-2">
                                    <div class="flex flex-wrap gap-2">
                                        <a class="px-2 py-1 rounded bg-slate-200 hover:bg-slate-300 transition-colors" href="{{ route('admin.students.edit', $st) }}">Sửa</a>
                                        <form class="inline" method="POST" action="{{ route('admin.students.extend', $st) }}?days=30">
                                        @csrf
                                        <button class="px-2 py-1 rounded bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">+30 ngày</button>
                                        </form>
                                        <form class="inline" method="POST" action="{{ route('admin.students.extend', $st) }}?days=90">
                                        @csrf
                                        <button class="px-2 py-1 rounded bg-emerald-700 text-white hover:bg-emerald-800 transition-colors">+90 ngày</button>
                                        </form>
                                        <form class="inline" method="POST" action="{{ route('admin.students.destroy', $st) }}"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn xoá học sinh này?')">
                                        @csrf @method('DELETE')
                                        <button class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700 transition-colors">Xoá</button>
                                        </form>
                                    </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">{{ $students->links() }}</div>

                    <p class="mt-4 text-xs text-slate-500">
                        Gợi ý: mật khẩu mặc định của học sinh là <code>123456</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>