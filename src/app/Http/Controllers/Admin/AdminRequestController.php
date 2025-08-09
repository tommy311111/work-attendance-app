<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
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
        return view('admin.request.approve');
    }
}
