<?php

namespace App\Http\Controllers\User;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use App\Http\Requests\AttendanceUpdateRequest;

class UserAttendanceController extends Controller
{
    // 勤怠画面の表示
    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 曜日を日本語1文字で取得
    $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
    $weekdayJapanese = $weekDays[$today->dayOfWeek];

        // 定数配列の値を使って初期状態セット
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today->toDateString()],
            ['status' => Attendance::STATUS['OFF_DUTY']]
        );

        return view('user.attendance.create', compact('attendance', 'today', 'weekdayJapanese'));
    }

    // 勤怠ステータス変更
    public function updateStatus(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->firstOrFail();

        $action = $request->input('action');

        // すでに出勤していたらリダイレクト（重複防止）
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
                    'user_id' => Auth::id(),
                    'break_start_at' => now()
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
                // 休憩戻り時は「出勤中」に戻す
                $attendance->status = Attendance::STATUS['WORKING'];
                break;

            case 'end_work':
                $attendance->status = Attendance::STATUS['FINISHED'];
                $attendance->clock_out = now();
                $attendance->save();
                return redirect()->route('attendance.create');
                break;
        }

        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function index(Request $request)
{
    $user = Auth::user();

    // 表示対象の年月を取得（なければ今月）
    $month = $request->input('month', Carbon::now()->format('Y-m'));
    $parsedMonth = Carbon::createFromFormat('Y-m', $month);

    // 勤怠情報＋その休憩情報もまとめて取得
    $attendances = Attendance::with('breaks') // ← ここ
        ->where('user_id', $user->id)
        ->where('date', 'like', "$month%")
        ->orderBy('date', 'asc')
        ->get();

    return view('user.attendance.index', [
        'attendances' => $attendances,
        'currentMonth' => $parsedMonth,
    ]);
}
    // 勤怠画面の表示
    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);
        $breaks = $attendance->breaks;
        $user = $attendance->user;

        // 修正申請が「承認待ち」のものがあるか判定
    $isPendingApproval = $attendance->attendanceRequests()->where('status', 'pending')->exists();

    return view('user.attendance.show', compact('attendance','user','breaks','isPendingApproval'));
    }


public function requestUpdate(AttendanceUpdateRequest $request, $id)
{
    $attendance = Attendance::with('breaks')->findOrFail($id);

    if ($attendance->user_id !== Auth::id()) {
        abort(403);
    }

    // 日付と入力時刻を組み合わせて datetime に変換
    $date = $attendance->date->format('Y-m-d'); // モデルに date:cast があれば Carbon として使える

    $clockIn = $request->clock_in ? Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->clock_in) : null;
    $clockOut = $request->clock_out ? Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->clock_out) : null;

    $attendanceRequest = AttendanceRequest::create([
        'user_id' => Auth::id(),
        'attendance_id' => $attendance->id,
        'request_type' => 'edit',
        'requested_clock_in_time' => $clockIn,
        'requested_clock_out_time' => $clockOut,
        'reason' => $request->remarks,
        'status' => 'pending',
        'reviewed_by' => null,
        'reviewed_at' => null,
    ]);

    if (!empty($request->breaks)) {
        foreach ($request->breaks as $breakInput) {
            if (!empty($breakInput['id']) && (!empty($breakInput['break_start']) || !empty($breakInput['break_end']))) {
                AttendanceRequestBreak::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'break_id' => $breakInput['id'],
                    'requested_start_time' => !empty($breakInput['break_start'])
                        ? Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $breakInput['break_start'])
                        : null,
                    'requested_end_time' => !empty($breakInput['break_end'])
                        ? Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $breakInput['break_end'])
                        : null,
                ]);
            }
        }
    }

    return redirect()->route('user.attendance.show', $attendance->id)
        ->with('success', '修正申請を送信しました。');
}

}
