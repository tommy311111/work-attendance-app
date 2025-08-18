<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use App\Http\Controllers\Controller;

class UserRequestController extends Controller
{
    public function storeRequest(AttendanceUpdateRequest $request, $id)
{
    $attendance = Attendance::with('breaks')->findOrFail($id);

    // 他ユーザーの勤怠は編集不可
    if ($attendance->user_id !== Auth::id()) {
        abort(403);
    }

    // 日付を取得
    $date = $attendance->date->format('Y-m-d');

    // 出勤・退勤を空文字なら null に変換
    $clockIn  = $request->clock_in === '' ? null : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->clock_in);
    $clockOut = $request->clock_out === '' ? null : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->clock_out);

    // 修正申請を作成
    $attendanceRequest = AttendanceRequest::create([
        'user_id' => Auth::id(),
        'attendance_id' => $attendance->id,
        'request_type' => 'edit',
        'requested_clock_in_time' => $clockIn,
        'requested_clock_out_time' => $clockOut,
        'reason' => $request->reason,
        'status' => 'pending',
        'reviewed_by' => null,
        'reviewed_at' => null,
    ]);

    // 休憩時間を処理（空文字 → null 変換）
    foreach ($request->breaks ?? [] as $breakInput) {
        $start = ($breakInput['break_start'] ?? '') === ''
            ? null
            : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $breakInput['break_start']);

        $end = ($breakInput['break_end'] ?? '') === ''
            ? null
            : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $breakInput['break_end']);

        // 通常の追加・修正申請
        if ($start !== null || $end !== null) {
            AttendanceRequestBreak::create([
                'attendance_request_id' => $attendanceRequest->id,
                'break_id' => $breakInput['id'] ?? null,
                'requested_start_time' => $start,
                'requested_end_time' => $end,
            ]);
        }
        // 削除申請（既存の break_id がある & 両方 null）
        elseif (!empty($breakInput['id']) && $start === null && $end === null) {
            AttendanceRequestBreak::create([
                'attendance_request_id' => $attendanceRequest->id,
                'break_id' => $breakInput['id'],
                'requested_start_time' => null,
                'requested_end_time' => null,
            ]);
        }
    }

    return redirect()->route('attendance-requests.edit', $attendanceRequest->id)
        ->with('success', '修正申請を送信しました。');
}


public function editRequest($id)
{
    $attendance = AttendanceRequest::with('attendanceRequestBreaks', 'user')->findOrFail($id);

    $user = $attendance->user;  // AttendanceRequest に紐づく User モデル（例: 申請者）
    $breaks = $attendance->attendanceRequestBreaks;  // 関連する休憩時間の修正データ

    return view('user.attendance.request_pending', compact('attendance', 'user', 'breaks'));
}

    public function index()
{
    $status = request('status','pending'); // 'pending' or 'approved'

    $query = AttendanceRequest::where('user_id', Auth::id());

    if ($status === 'pending') {
        $query->where('status', 'pending');
    } elseif ($status === 'approved') {
        $query->where('status', 'approved');
    }

    $requests = $query->latest()->get();

    return view('user.request.index', compact('requests'));
}

}
