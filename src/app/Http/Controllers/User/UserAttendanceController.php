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
    // 勤怠画面の表示
    public function show()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 定数配列の値を使って初期状態セット
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['status' => Attendance::STATUS['OFF_DUTY']]
        );

        return view('user.attendance.create', compact('attendance'));
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
        if ($action === 'start_work' && $attendance->work_start_at) {
            return back()->withErrors(['action' => 'すでに出勤済みです。']);
        }

        switch ($action) {
            case 'start_work':
                $attendance->status = Attendance::STATUS['WORKING'];
                $attendance->work_start_at = now();
                break;

            case 'start_break':
                $attendance->status = Attendance::STATUS['ON_BREAK'];
                BreakTime::create([
                    'attendance_id' => $attendance->id,
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
                $attendance->work_end_at = now();
                session()->flash('message', 'お疲れ様でした。');
                break;
        }

        $attendance->save();

        return redirect()->route('attendance.show');
    }
}
