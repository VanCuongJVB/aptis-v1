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
        $student->access_starts_at = $data['access_starts_at'] ?? null;
        $student->access_ends_at = $data['access_ends_at'] ?? null;
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
        $student->fill([
            'name' => $data['name'] ?? null,
            'email' => strtolower($data['email']),
            'is_active' => $request->boolean('is_active', true),
            'access_starts_at' => $data['access_starts_at'] ?? null,
            'access_ends_at' => $data['access_ends_at'] ?? null,
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

    public function importForm()
    {
        return view('admin.students.import');
    }

    public function importStore(Request $request)
    {
        $request->validate(['file' => ['required', 'file', 'mimes:csv,txt,xlsx']]);
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $rows = [];

        if ($ext === 'csv' || $ext === 'txt') {
            if (($handle = fopen($file->getRealPath(), "r")) !== false) {
                $header = null;
                while (($data = fgetcsv($handle)) !== false) {
                    if ($header === null) {
                        $header = $data;
                        continue;
                    }
                    $row = @array_combine($header, $data);
                    if ($row) $rows[] = $row;
                }
                fclose($handle);
            }
        } elseif ($ext === 'xlsx') {
            if (class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                $sheets = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
                $sheet = $sheets[0] ?? [];
                $header = array_map('strval', $sheet[0] ?? []);
                foreach (array_slice($sheet, 1) as $line) {
                    $row = @array_combine($header, $line);
                    if ($row) $rows[] = $row;
                }
            } else {
                return back()->withErrors(['file' => 'Chưa cài maatwebsite/excel. Dùng CSV hoặc cài: composer require maatwebsite/excel']);
            }
        } else {
            return back()->withErrors(['file' => 'Định dạng không hỗ trợ.']);
        }

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
                $name = $r['name'] ?? null;
                $is_active = isset($r['is_active']) ? (bool)intval($r['is_active']) : true;
                $start = trim($r['access_starts_at'] ?? '') ?: null;
                $end = trim($r['access_ends_at'] ?? '') ?: null;

                $user = User::where('email', $email)->first();
                if (!$user) {
                    $user = new User();
                    $user->email = $email;
                    $user->name = $name;
                    $user->role = 'student';
                    $user->is_active = $is_active;
                    $user->access_starts_at = $start ? \Carbon\Carbon::parse($start) : null;
                    $user->access_ends_at = $end ? \Carbon\Carbon::parse($end) : null;
                    $user->password = \Illuminate\Support\Facades\Hash::make('123456');
                    $user->email_verified_at = now();
                    $user->save();
                    $created++;
                } else {
                    $user->name = $name ?: $user->name;
                    $user->is_active = $is_active;
                    $user->access_starts_at = $start ? \Carbon\Carbon::parse($start) : $user->access_starts_at;
                    $user->access_ends_at = $end ? \Carbon\Carbon::parse($end) : $user->access_ends_at;
                    $user->save();
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        AccessLog::log(auth()->id(), 'students_imported', ['created' => $created, 'updated' => $updated, 'errors' => $errors]);
        return redirect()->route('admin.students.index')->with('ok', "Import xong: tạo {$created}, cập nhật {$updated}, lỗi {$errors}");
    }
}
