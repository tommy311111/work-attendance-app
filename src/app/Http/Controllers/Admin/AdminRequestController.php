<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminRequestController extends Controller
{
    public function update(AttendanceUpdateRequest $request, $id)
    {
        // 管理者チェック（ポリシーなどでやっていれば不要）
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        // 勤怠データ取得
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 日付を取得
        $date = $attendance->date->format('Y-m-d');

        // 出勤・退勤を組み立てて更新
        $clockIn = $request->clock_in ? Carbon::createFromFormat('Y-m-d H:i', "$date {$request->clock_in}") : null;
        $clockOut = $request->clock_out ? Carbon::createFromFormat('Y-m-d H:i', "$date {$request->clock_out}") : null;

        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

        // 休憩時間の更新（breaks.*.idが指定されている前提）
        if (!empty($request->breaks)) {
            foreach ($request->breaks as $breakInput) {
                if (!empty($breakInput['id'])) {
                    $break = $attendance->breaks->firstWhere('id', $breakInput['id']);
                    if ($break) {
                        $break->update([
                            'break_start_at' => !empty($breakInput['break_start'])
                                ? Carbon::createFromFormat('Y-m-d H:i', "$date {$breakInput['break_start']}")
                                : null,
                            'break_end_at' => !empty($breakInput['break_end'])
                                ? Carbon::createFromFormat('Y-m-d H:i', "$date {$breakInput['break_end']}")
                                : null,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.attendance.index')
            ->with('success', '勤怠データを更新しました。');
    }

public function index()
{
    $status = request('status','pending'); // 'pending' or 'approved'

     $query = AttendanceRequest::query(); // 全件対象

    if ($status === 'pending') {
        $query->where('status', 'pending');
    } elseif ($status === 'approved') {
        $query->where('status', 'approved');
    }

    $requests = $query->latest()->get();

    return view('admin.request.index', compact('requests'));
}

    public function approveForm($id)
{
    $attendanceRequest = AttendanceRequest::with([
        'attendance.breaks',
        'attendance.user',
        'attendanceRequestBreaks',
    ])->findOrFail($id);

    $attendance = $attendanceRequest->attendance;
    $breaks = $attendanceRequest->attendanceRequestBreaks;
    $user = $attendance->user;
    $reason = $attendanceRequest->reason ?? '';

    return view('admin.request.approve', compact(
        'attendance',
        'user',
        'breaks',
        'reason',
        'attendanceRequest'
    ));
}


public function approve($id)
{
    DB::transaction(function () use ($id) {
        $attendanceRequest = AttendanceRequest::with([
            'attendance.breaks',
            'attendanceRequestBreaks', // 休憩修正申請
        ])->findOrFail($id);

        $attendance = $attendanceRequest->attendance;


        // 出勤・退勤の修正
        if (!empty($attendanceRequest->requested_clock_in_time)) {
            $attendance->clock_in = $attendanceRequest->requested_clock_in_time;
        }
        if (!empty($attendanceRequest->requested_clock_out_time)) {
            $attendance->clock_out = $attendanceRequest->requested_clock_out_time;
        }
        $attendance->save();

        // 休憩時間の修正（attendance_request_breaksテーブルの内容を反映）
if ($attendanceRequest->attendanceRequestBreaks && $attendanceRequest->attendanceRequestBreaks->count()) {
    foreach ($attendanceRequest->attendanceRequestBreaks as $breakRequest) {
        $break = $attendance->breaks->firstWhere('id', $breakRequest->break_id);

        if ($break) {
    // 既存休憩を更新
    if (!empty($breakRequest->requested_start_time)) {
        $break->break_start_at = $breakRequest->requested_start_time;
    }
    if (!empty($breakRequest->requested_end_time)) {
        $break->break_end_at = $breakRequest->requested_end_time;
    }
    $break->save();
} else {
    // 新規休憩を作成
    $attendance->breaks()->create([
        'break_start_at' => $breakRequest->requested_start_time,
        'break_end_at'   => $breakRequest->requested_end_time,
    ]);
}

    }
}


        // 承認情報の更新
        $attendanceRequest->status = 'approved';
        $attendanceRequest->reviewed_by = Auth::id();
        $attendanceRequest->reviewed_at = Carbon::now();
        $attendanceRequest->save();

    });

    return redirect()
    ->route('attendance_requests.list', ['status' => 'pending'])
    ->with('success', '修正申請を承認しました。');

}

}
