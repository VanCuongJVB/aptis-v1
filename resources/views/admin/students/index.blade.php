<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý học sinh') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                        <div>
                            <h1 class="text-2xl font-extrabold text-gray-900">Danh sách học sinh</h1>
                            <p class="mt-1 text-sm text-slate-500">Quản lý học sinh, cửa sổ truy cập và gia hạn nhanh.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a class="btn-base bg-white border hover:bg-slate-50"
                                href="{{ route('admin.students.import.form') }}">Nhập từ file</a>
                            <a class="btn-base btn-primary" href="{{ route('admin.students.create') }}">+ Thêm học
                                sinh</a>
                        </div>
                    </div>
                    @php
                        $totalStudents = \App\Models\User::where('role', 'student')->count();
                        $totalAdmins = \App\Models\User::where('role', 'admin')->count();
                    @endphp
                    <div class="text-xs text-slate-600 ml-auto mb-2">
                        Tổng: <b>{{ $totalStudents }}</b> học sinh, <b>{{ $totalAdmins }}</b> admin
                    </div>
                    <form method="GET" class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-4">
                        {{-- Search --}}
                        <div class="flex-1">
                            <input type="text" name="s" value="{{ request('s') }}" placeholder="Tìm email hoặc tên..."
                                class="w-full md:w-60 border border-gray-300 rounded-lg px-3 py-2 shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        {{-- Status tabs --}}
                        <div class="flex items-center gap-2 overflow-x-auto md:overflow-visible">
                            @php $st = $status ?? request('status'); @endphp
                            @php
                                $tab = function ($key, $label) use ($st) {
                                    $url = request()->fullUrlWithQuery(['status' => $key ?: null]);
                                    $active = ($st === $key) || (!$st && $key === null);
                                    $base = 'px-3 py-1.5 rounded-full text-sm font-medium whitespace-nowrap transition-colors';
                                    $cls = $active
                                        ? 'bg-indigo-600 text-white shadow-sm'
                                        : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50';
                                    return '<a href="' . $url . '" class="' . $base . ' ' . $cls . '">' . $label . '</a>';
                                };
                            @endphp
                            {!! $tab(null, 'Tất cả') !!}
                            {!! $tab('active', 'Đang kích hoạt') !!}
                            {!! $tab('inactive', 'Bị khoá') !!}
                            {!! $tab('warned', 'Cảnh cáo') !!}
                            {!! $tab('expiring', 'Sắp hết hạn ≤7d') !!}
                            {!! $tab('expired', 'Đã hết hạn') !!}
                        </div>

                        {{-- Submit --}}<button
                            class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-400">
                            Lọc
                            </button>
                    </form>

                    <p class="mt-4 text-xs text-slate-500">
                        Gợi ý: mật khẩu mặc định của học sinh là <code>123456</code>
                    </p>

                    <div class="bg-white rounded shadow overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-slate-50 text-left">
                                <tr class="text-xs text-slate-500 uppercase">
                                    <th class="px-4 py-3">STT</th>
                                    <th class="px-4 py-3">Email</th>
                                    <th class="px-4 py-3">Tên</th>
                                    <th class="px-4 py-3">Trạng thái</th>
                                    <th class="px-4 py-3">Cảnh báo</th>
                                    <th class="px-4 py-3">Thời gian truy cập</th>
                                    <th class="px-4 py-3">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @php $now = \Carbon\Carbon::now(); @endphp
                                @php $stt = ($students->currentPage() - 1) * $students->perPage() + 1; @endphp
                                @foreach($students as $st)
                                    @php
                                        $expired = $st->access_ends_at && $st->access_ends_at->lt($now);
                                        $expiring = $st->access_ends_at && $st->access_ends_at->between($now, (clone $now)->addDays(7));
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-center">{{ $stt++ }}</td>
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $st->email }}</td>
                                        <td class="px-4 py-3">{{ $st->name }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col gap-1">
                                                @if($st->is_active)
                                                    <span class="px-2 py-0.5 rounded bg-emerald-100 border border-emerald-300 text-emerald-700">
                                                        Kích hoạt
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded bg-slate-200 border text-slate-700">
                                                        Bị khoá
                                                    </span>
                                                @endif

                                                @if($expired)
                                                    <span class="px-2 py-0.5 rounded bg-red-100 border border-red-300">
                                                        Hết hạn
                                                    </span>
                                                @elseif($expiring)
                                                    <span class="px-2 py-0.5 rounded bg-amber-100 border border-amber-300">
                                                        Sắp hết hạn
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Cảnh báo column --}}
                                        <td class="px-4 py-3">
                                            @if($st->device_warning)
                                                <span class="px-2 py-0.5 rounded bg-amber-50 border border-amber-300 text-amber-700">
                                                    Cảnh cáo
                                                </span>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <div class="text-xs">
                                                @if($st->access_starts_at) Từ:
                                                {{ $st->access_starts_at->format('Y-m-d H:i') }} @endif
                                                @if($st->access_ends_at) • Đến:
                                                {{ $st->access_ends_at->format('Y-m-d H:i') }} @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-2">
                                                {{-- Sửa --}}
                                                <a class="px-3 py-1.5 rounded-md bg-slate-100 text-slate-700 hover:bg-slate-200 text-sm font-medium transition"
                                                href="{{ route('admin.students.edit', $st) }}">
                                                    Sửa
                                                </a>

                                                {{-- Xoá --}}
                                                <form method="POST" action="{{ route('admin.students.destroy', $st) }}"
                                                    onsubmit="return confirm('Bạn có chắc chắn muốn xoá học sinh này?')" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button
                                                        style="background-color: oklch(59.3% 0.261 27.14);"
                                                        class="px-3 py-1.5 rounded-md text-white hover:bg-red-600 text-sm font-medium transition">
                                                        Xoá
                                                    </button>
                                                </form>

                                                {{-- Khoá / Mở khoá --}}
                                                <form method="POST" action="{{ route('admin.students.toggleActive', $st) }}" class="inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="px-3 py-1.5 rounded-md text-sm font-medium transition
                                                            {{ $st->is_active
                                                                ? 'bg-red-100 text-red-700 hover:bg-red-200'
                                                                : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                                        {{ $st->is_active ? 'Khoá' : 'Mở khoá' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 w-100">
                    <div class="flex justify-between items-center flex-wrap gap-2">
                        <style>
                            nav {
                                width: 100% !important;
                            }
                        </style>
                        {{ $students->links() }}
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>