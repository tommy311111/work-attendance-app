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
        'reason' => $request->reason,
        'status' => 'pending',
        'reviewed_by' => null,
        'reviewed_at' => null,
    ]);

    if (!empty($request->breaks)) {
    foreach ($request->breaks as $breakInput) {
        if (!empty($breakInput['break_start']) || !empty($breakInput['break_end'])) {
            AttendanceRequestBreak::create([
                'attendance_request_id' => $attendanceRequest->id,
                'break_id' => !empty($breakInput['id']) ? $breakInput['id'] : null, // 新規ならnull
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
