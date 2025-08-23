<?php

namespace App\Http\Controllers\User;

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

        $date = $attendance->date->format('Y-m-d');

        $clockIn  = $request->clock_in === '' ? null : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->clock_in);
        $clockOut = $request->clock_out === '' ? null : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $request->clock_out);

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

        foreach ($request->breaks ?? [] as $breakInput) {
            $start = ($breakInput['break_start'] ?? '') === ''
                ? null
                : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $breakInput['break_start']);

            $end = ($breakInput['break_end'] ?? '') === ''
                ? null
                : Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $breakInput['break_end']);

            if ($start !== null || $end !== null) {
                AttendanceRequestBreak::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'break_id' => $breakInput['id'] ?? null,
                    'requested_start_time' => $start,
                    'requested_end_time' => $end,
                ]);
            } elseif (!empty($breakInput['id']) && $start === null && $end === null) {
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

        $user = $attendance->user;
        $breaks = $attendance->attendanceRequestBreaks;

        return view('user.attendance.request_pending', compact('attendance', 'user', 'breaks'));
    }

    public function index()
    {
        $status = request('status', 'pending');

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
