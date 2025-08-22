<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdminRequestController extends Controller
{
    public function update(AttendanceUpdateRequest $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $attendance = Attendance::with('breaks')->findOrFail($id);
        $date = $attendance->date->format('Y-m-d');
        $clockIn = $request->clock_in ? Carbon::createFromFormat('Y-m-d H:i', "$date {$request->clock_in}") : null;
        $clockOut = $request->clock_out ? Carbon::createFromFormat('Y-m-d H:i', "$date {$request->clock_out}") : null;

        $attendance->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);

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
                } else {
                    if (!empty($breakInput['break_start']) && !empty($breakInput['break_end'])) {
                        $attendance->breaks()->create([
                            'break_start_at' => Carbon::createFromFormat('Y-m-d H:i', "$date {$breakInput['break_start']}"),
                            'break_end_at' => Carbon::createFromFormat('Y-m-d H:i', "$date {$breakInput['break_end']}"),
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
        $status = request('status','pending');
        $query = AttendanceRequest::query();

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
                'attendanceRequestBreaks',
            ])->findOrFail($id);

            $attendance = $attendanceRequest->attendance;
            $attendance->clock_in  = $attendanceRequest->requested_clock_in_time ?: null;
            $attendance->clock_out = $attendanceRequest->requested_clock_out_time ?: null;
            $attendance->save();

            if ($attendanceRequest->attendanceRequestBreaks->count()) {
                foreach ($attendanceRequest->attendanceRequestBreaks as $breakRequest) {
                    $start = $breakRequest->requested_start_time ?: null;
                    $end   = $breakRequest->requested_end_time   ?: null;

                    if ($breakRequest->break_id) {
                        $break = $attendance->breaks->firstWhere('id', $breakRequest->break_id);
                        if ($break) {
                            if ($start === null && $end === null) {
                                $break->delete();
                            } else {
                                $break->update([
                                    'break_start_at' => $start,
                                    'break_end_at'   => $end,
                                ]);
                            }
                        }
                    } else {
                        if ($start !== null || $end !== null) {
                            $attendance->breaks()->create([
                                'break_start_at' => $start,
                                'break_end_at'   => $end,
                            ]);
                        }
                    }
                }
            }

            $attendanceRequest->update([
                'status'      => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => Carbon::now(),
            ]);
        });

        return redirect()
            ->route('attendance_requests.list', ['status' => 'pending'])
            ->with('success', '修正申請を承認しました。');
    }
}
