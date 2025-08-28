<?php

namespace App\Http\Controllers\User;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\BreakTime;

class UserAttendanceController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekdayJapanese = $weekDays[$today->dayOfWeek];

        $attendance = Attendance::where('user_id', $user->id)
    ->where('date', $today->toDateString())
    ->first();

if (!$attendance) {
    $attendance = Attendance::create([
        'user_id' => $user->id,
        'date' => $today->toDateString(),
        'status' => Attendance::STATUS['OFF_DUTY'],
    ]);
}


        return view('user.attendance.create', compact('attendance', 'today', 'weekdayJapanese'));
    }

    public function updateStatus(Request $request)
{
    $user = Auth::user();
    $today = Carbon::today()->toDateString();

    // 当日の勤怠レコードがあれば取得、なければ新規作成
    $attendance = Attendance::firstOrCreate(
        ['user_id' => $user->id, 'date' => $today],
        ['status' => Attendance::STATUS['OFF_DUTY']]
    );

    $action = $request->input('action');

    if ($action === 'start_work' && $attendance->clock_in) {
        return back()->withErrors(['action' => 'すでに出勤済みです。']);
    }

    switch ($action) {
        case 'start_work':
            $attendance->status = Attendance::STATUS['WORKING'];
            $attendance->clock_in = now();
            break;

        case 'start_break':
            $attendance->status = Attendance::STATUS['ON_BREAK'];
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => now(),
            ]);
            break;

        case 'end_break':
            $latestBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end_at')
                ->latest()
                ->first();
            if ($latestBreak) {
                $latestBreak->break_end_at = now();
                $latestBreak->save();
            }
            $attendance->status = Attendance::STATUS['WORKING'];
            break;

        case 'end_work':
            $attendance->status = Attendance::STATUS['FINISHED'];
            $attendance->clock_out = now();
            $attendance->save();
            return redirect()->route('attendance.create');
    }

    $attendance->save();

    return redirect()->route('attendance.create');
}

    public function index(Request $request)
    {
        $user = Auth::user();

        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $parsedMonth = Carbon::createFromFormat('Y-m', $month);

        $attendances = Attendance::with('breaks', 'attendanceRequests')
            ->where('user_id', $user->id)
            ->where('date', 'like', "$month%")
            ->orderBy('date', 'asc')
            ->get();

        $attendances = $attendances->map(function ($attendance) {
            $latestRequest = $attendance->attendanceRequests->sortByDesc('created_at')->first();
            $attendance->request_status = $latestRequest ? $latestRequest->status : null;
            return $attendance;
        });

        return view('user.attendance.index', [
            'attendances' => $attendances,
            'currentMonth' => $parsedMonth,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);
        $breaks = $attendance->breaks;
        $user = $attendance->user;

        $isPendingApproval = $attendance->attendanceRequests()->where('status', 'pending')->exists();

        if ($isPendingApproval) {
            return view('user.attendance.request_pending', compact('attendance', 'user', 'breaks'));
        } else {
            return view('user.attendance.show', compact('attendance', 'user', 'breaks'));
        }
    }
}
