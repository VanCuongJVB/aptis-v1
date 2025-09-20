<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\AccessLog;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query()->where('role', 'student');
        $s = $request->get('s');
        $status = $request->get('status'); // all|active|inactive|expiring|expired

        if ($s) {
            $q->where(function ($qq) use ($s) {
                $qq->where('email', 'like', "%$s%")
                    ->orWhere('name', 'like', "%$s%");
            });
        }

        $now = Carbon::now();
        if ($status === 'active') {
            $q->where('is_active', true)
                ->where(function ($w) use ($now) {
                    $w->whereNull('access_ends_at')
                        ->orWhere('access_ends_at', '>=', $now);
                });
        } elseif ($status === 'inactive') {
            $q->where('is_active', false);
        } elseif ($status === 'expired') {
            $q->whereNotNull('access_ends_at')
                ->where('access_ends_at', '<', $now);
        } elseif ($status === 'warned') {
            $q->where('device_warning', true);
        } elseif ($status === 'expiring') {
            $q->whereNotNull('access_ends_at')
                ->whereBetween('access_ends_at', [$now, (clone $now)->addDays(7)]);
        }

        $students = $q->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'status', 's'));
    }


    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'is_active' => ['sometimes', 'boolean'],
            'access_starts_at' => ['nullable', 'date'],
            'access_ends_at' => ['nullable', 'date', 'after:access_starts_at'],
        ]);
        $student = new User();
        $student->name = $data['name'] ?? null;
        $student->email = strtolower($data['email']);
        $student->role = 'student';
        $student->is_active = $request->boolean('is_active', true);
        // normalize empty date inputs to null to avoid saving empty strings
        $start = trim($data['access_starts_at'] ?? '');
        $end = trim($data['access_ends_at'] ?? '');
        $student->access_starts_at = $start !== '' ? Carbon::parse($start) : null;
        $student->access_ends_at = $end !== '' ? Carbon::parse($end) : null;
        $student->password = Hash::make('123456');
        $student->email_verified_at = now();
        $student->save();
        AccessLog::log(auth()->id(), 'student_created', ['student_id' => $student->id]);
        return redirect()->route('admin.students.index')->with('ok', 'Đã tạo học sinh (mật khẩu: 123456).');
    }

    public function edit(User $student)
    {
        abort_if($student->role === 'admin', 403);
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, User $student)
    {
        abort_if($student->role === 'admin', 403);
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($student->id)],
            'is_active' => ['sometimes', 'boolean'],
            'access_starts_at' => ['nullable', 'date'],
            'access_ends_at' => ['nullable', 'date', 'after:access_starts_at'],
        ]);
        // normalize empty date inputs to null
        $start = trim($data['access_starts_at'] ?? '');
        $end = trim($data['access_ends_at'] ?? '');
        $student->fill([
            'name' => $data['name'] ?? null,
            'email' => strtolower($data['email']),
            'is_active' => $request->boolean('is_active', true),
            'access_starts_at' => $start !== '' ? Carbon::parse($start) : null,
            'access_ends_at' => $end !== '' ? Carbon::parse($end) : null,
        ])->save();
        AccessLog::log(auth()->id(), 'student_updated', ['student_id' => $student->id]);
        return redirect()->route('admin.students.index')->with('ok', 'Đã lưu học sinh.');
    }

    public function destroy(User $student)
    {
        abort_if($student->role === 'admin', 403);
        $student->delete();
        AccessLog::log(auth()->id(), 'student_deleted', ['student_id' => $student->id]);
        return redirect()->route('admin.students.index')->with('ok', 'Đã xoá học sinh.');
    }

    public function extend(Request $request, User $student)
    {
        abort_if($student->role === 'admin', 403);
        $days = (int)$request->query('days', 30);
        $now = now();
        $base = ($student->access_ends_at && $student->access_ends_at->gt($now)) ? $student->access_ends_at : $now;
        $student->access_ends_at = (clone $base)->addDays($days);
        if (!$student->access_starts_at) $student->access_starts_at = $now;
        $student->save();
        AccessLog::log(auth()->id(), 'student_extended', ['student_id' => $student->id, 'days' => $days]);
        return back()->with('ok', "Đã gia hạn +{$days} ngày (đến {$student->access_ends_at}).");
    }

    public function toggleActive(Request $request, User $student)
    {
        abort_if($student->role === 'admin', 403);
        $student->is_active = !$student->is_active;
        $student->save();
        AccessLog::log(auth()->id(), $student->is_active ? 'student_unlocked' : 'student_locked', ['student_id' => $student->id]);
        return back()->with('ok', $student->is_active ? 'Đã mở khoá học sinh.' : 'Đã khoá học sinh.');
    }

    public function importForm()
    {
        return view('admin.students.import');
    }

    public function importStore(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx']]);
        $file = $request->file('file');
        $rows = [];

        // Helper: strip BOM
        $stripBom = fn($s) => preg_replace('/^\x{FEFF}/u', '', (string)$s);

        // Helper: normalize header keys
        $normalizeKey = function (string $key) use ($stripBom): string {
            $key = strtolower($stripBom($key));
            $key = preg_replace('/[^a-z0-9]+/i', '_', trim($key));
            $key = trim($key, '_');
            $map = [
                'e_mail' => 'email', 'mail' => 'email',
                'full_name' => 'name', 'username' => 'name',
                'active' => 'is_active', 'status' => 'is_active',
                'access_start' => 'access_starts_at', 'access_start_at' => 'access_starts_at',
                'start' => 'access_starts_at', 'start_at' => 'access_starts_at',
                'access_end' => 'access_ends_at', 'access_end_at' => 'access_ends_at',
                'end' => 'access_ends_at', 'end_at' => 'access_ends_at',
            ];
            return $map[$key] ?? $key;
        };

        // Helper: parse boolean
        $boolish = function ($v): bool {
            if (is_bool($v)) return $v;
            $s = strtolower(trim((string)$v));
            if ($s === '') return true;
            if (is_numeric($s)) return ((int)$s) > 0;
            return in_array($s, ['1', 'y', 'yes', 'true', 'on'], true);
        };

        // Helper: parse date (Excel serial or string)
        $parseDate = function ($v) {
            if ($v === null) return null;
            if ($v instanceof \DateTimeInterface) return \Carbon\Carbon::instance(\Carbon\Carbon::parse($v));
            $s = is_string($v) ? trim($v) : $v;
            if (is_numeric($s)) {
                $base = \Carbon\Carbon::create(1899, 12, 30, 0, 0, 0, 'UTC');
                $days = (float)$s;
                $whole = (int)floor($days);
                $frac  = $days - $whole;
                $dt = $base->copy()->addDays($whole)->addSeconds((int)round($frac * 86400));
                return $dt;
            }
            if (!is_string($s) || $s === '') return null;
            try {
                $tryFormats = [
                    'Y-m-d H:i:s','Y-m-d H:i','Y-m-d','d/m/Y H:i','d/m/Y','m/d/Y H:i','m/d/Y',
                    'd-m-Y H:i','d-m-Y','m-d-Y H:i','m-d-Y','Y/m/d H:i','Y/m/d',
                ];
                foreach ($tryFormats as $fmt) {
                    $dt = \Carbon\Carbon::createFromFormat($fmt, $s);
                    if ($dt !== false) return $dt;
                }
                return \Carbon\Carbon::parse($s);
            } catch (\Throwable $e) { return null; }
        };

        // Only XLSX supported
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return back()->withErrors(['file' => 'Chưa cài maatwebsite/excel. Dùng CSV hoặc cài: composer require maatwebsite/excel']);
        }
        $sheets = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
        $sheet  = $sheets[0] ?? [];
        if (empty($sheet) || empty($sheet[0])) {
            return back()->withErrors(['file' => 'File không có dữ liệu.']);
        }
        $headerRaw = array_map(fn($v) => $stripBom(trim((string)$v)), $sheet[0]);
        $keys = [];
        $seen = [];
        foreach ($headerRaw as $h) {
            $k = $normalizeKey($h);
            if ($k === '') $k = 'col';
            $base = $k;
            $i = 2;
            while (isset($seen[$k])) {
                $k = $base . '_' . $i++;
            }
            $seen[$k] = true;
            $keys[] = $k;
        }
        if (!in_array('email', $keys, true)) {
            return back()->withErrors(['file' => 'Header file phải chứa cột "email".']);
        }
        foreach (array_slice($sheet, 1) as $line) {
            if ($line === null || (count($line) === 1 && trim((string)$line[0]) === '')) continue;
            if (isset($line[0]) && preg_match('/^\s*#/', (string)$line[0])) continue;
            $dCount = count($line);
            $hCount = count($keys);
            if ($dCount < $hCount) $line = array_pad($line, $hCount, null);
            elseif ($dCount > $hCount) $line = array_slice($line, 0, $hCount);
            $row = array_combine($keys, $line);
            if ($row) $rows[] = $row;
        }

        // --- Persist ---
        $created = 0;
        $updated = 0;
        $errors = 0;
        foreach ($rows as $r) {
            try {
                $email = strtolower(trim($r['email'] ?? ''));
                if (!$email) {
                    $errors++;
                    continue;
                }

                $name      = isset($r['name']) ? trim((string)$r['name']) : null;
                $is_active = isset($r['is_active']) ? $boolish($r['is_active']) : true;

                $startDt = $parseDate($r['access_starts_at'] ?? null);
                $endDt   = $parseDate($r['access_ends_at'] ?? null);

                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = new User();
                    $user->email = $email;
                    $user->name = $name;
                    $user->role = 'student';
                    $user->is_active = $is_active;
                    $user->access_starts_at = $startDt;
                    $user->access_ends_at = $endDt;
                    $user->password = \Illuminate\Support\Facades\Hash::make('123456');
                    $user->email_verified_at = now();
                    $user->save();
                    $created++;
                } else {
                    if ($name) $user->name = $name;
                    $user->is_active = $is_active;
                    if ($startDt) $user->access_starts_at = $startDt;
                    if ($endDt)   $user->access_ends_at   = $endDt;
                    $user->save();
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors++;
                // \Log::warning('Import row failed', ['row' => $r, 'ex' => $e->getMessage()]);
            }
        }

        AccessLog::log(auth()->id(), 'students_imported', ['created' => $created, 'updated' => $updated, 'errors' => $errors]);
        return redirect()->route('admin.students.index')->with('ok', "Import xong: tạo {$created}, cập nhật {$updated}, lỗi {$errors}");
    }


    public function show($id)
    {
        // Define later
    }
}
