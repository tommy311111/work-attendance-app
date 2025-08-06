<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class AdminAttendanceController extends Controller
{
    public function index(Request $request)
{
    // 日付の取得（パラメータがない場合は今日）
    $date = $request->input('date', Carbon::today()->format('Y-m-d'));
    $currentDate = Carbon::createFromFormat('Y-m-d', $date);

    // 指定日の全ユーザー分の勤怠を取得（ユーザー情報も含む）
    $attendances = Attendance::with(['user', 'breaks', 'attendanceRequests'])
        ->whereDate('date', $date)
        ->orderBy('user_id')
        ->get();

    // request_status を追加（申請状態）
    $attendances = $attendances->map(function ($attendance) {
        $latestRequest = $attendance->attendanceRequests->sortByDesc('created_at')->first();
        $attendance->request_status = $latestRequest ? $latestRequest->status : null;
        return $attendance;
    });

    return view('admin.attendance.index', [
        'attendances' => $attendances,
        'currentDate' => $currentDate,
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

    if ($isPendingApproval) {
        return view('user.attendance.request_pending', compact('attendance', 'user', 'breaks'));
    } else {
        return view('admin.attendance.show', compact('attendance', 'user', 'breaks'));
    }
    }
}
