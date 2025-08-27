@extends('layouts.app')
@section('title', 'Admin - Students')
@section('content')
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
    <div>
      <h1 class="text-xl font-bold">Students</h1>
      <p class="text-sm text-slate-600">Quản lý học sinh, cửa sổ truy cập và gia hạn nhanh.</p>
    </div>
    <div class="flex gap-2">
      <a class="px-3 py-2 rounded bg-slate-200" href="{{ route('admin.students.import.form') }}">Import</a>
      <a class="px-3 py-2 rounded bg-red-600 text-white" href="{{ route('admin.students.create') }}">+ Thêm học sinh</a>
    </div>
  </div>

  <form method="GET" class="flex flex-col md:flex-row gap-2 md:items-center mb-3">
    <input type="text" name="s" value="{{ request('s') }}" placeholder="Tìm email hoặc tên..."
      class="border rounded px-3 py-2 w-full md:w-72">
    <div class="flex gap-1">
      @php $st = $status ?? request('status'); @endphp
      @php
        $tab = function ($key, $label) use ($st) {
          $url = request()->fullUrlWithQuery(['status' => $key ?: null]);
          $active = ($st === $key) || (!$st && $key === null);
          return '<a href="' . $url . '" class="px-3 py-1 rounded ' . ($active ? 'bg-slate-800 text-white' : 'bg-white border hover:bg-slate-50') . '">' . $label . '</a>';
        };
      @endphp
      {!! $tab(null, 'All') !!}
      {!! $tab('active', 'Active') !!}
      {!! $tab('inactive', 'Inactive') !!}
      {!! $tab('expiring', 'Expiring ≤7d') !!}
      {!! $tab('expired', 'Expired') !!}
    </div>
    <button class="px-3 py-2 rounded bg-slate-200">Lọc</button>
  </form>

  <div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-left">
        <tr>
          <th class="px-3 py-2">Email</th>
          <th class="px-3 py-2">Tên</th>
          <th class="px-3 py-2">Trạng thái</th>
          <th class="px-3 py-2">Access window</th>
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
                <span class="px-2 py-0.5 rounded bg-green-100 border border-green-300">Active</span>
              @else
                <span class="px-2 py-0.5 rounded bg-slate-200 border">Inactive</span>
              @endif

              @if($expired)
                <span class="ml-2 px-2 py-0.5 rounded bg-red-100 border border-red-300">Expired</span>
              @elseif($expiring)
                <span class="ml-2 px-2 py-0.5 rounded bg-amber-100 border border-amber-300">Expiring</span>
              @endif
            </td>
            <td class="px-3 py-2 text-slate-600">
              <div class="text-xs">
                @if($st->access_starts_at) From: {{ $st->access_starts_at->format('Y-m-d H:i') }} @endif
                @if($st->access_ends_at) • To: {{ $st->access_ends_at->format('Y-m-d H:i') }} @endif
              </div>
            </td>
            <td class="px-3 py-2">
              <div class="flex flex-wrap gap-2">
                <a class="px-2 py-1 rounded bg-slate-200" href="{{ route('admin.students.edit', $st) }}">Sửa</a>
                <form class="inline" method="POST" action="{{ route('admin.students.extend', $st) }}?days=30">
                  @csrf
                  <button class="px-2 py-1 rounded bg-emerald-600 text-white">+30d</button>
                </form>
                <form class="inline" method="POST" action="{{ route('admin.students.extend', $st) }}?days=90">
                  @csrf
                  <button class="px-2 py-1 rounded bg-emerald-700 text-white">+90d</button>
                </form>
                <form class="inline" method="POST" action="{{ route('admin.students.destroy', $st) }}"
                  onsubmit="return confirm('Xoá học sinh này?')">
                  @csrf @method('DELETE')
                  <button class="px-2 py-1 rounded bg-red-600 text-white">Xoá</button>
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
    Gợi ý: mật khẩu mặc định của học sinh là <code>123456</code> (không có UI đổi).
  </p>
@endsection