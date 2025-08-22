<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $currentDate = Carbon::createFromFormat('Y-m-d', $date);

        $attendances = Attendance::with(['user', 'breaks', 'attendanceRequests'])
            ->whereDate('date', $date)
            ->orderBy('user_id')
            ->get();

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

    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);
        $breaks = $attendance->breaks;
        $user = $attendance->user;

        $isPendingApproval = $attendance->attendanceRequests()->where('status', 'pending')->exists();

        if ($isPendingApproval) {
            return view('user.attendance.request_pending', compact('attendance', 'user', 'breaks'));
        } else {
            return view('admin.attendance.show', compact('attendance', 'user', 'breaks'));
        }
    }

    public function staffAttendance(Request $request, $id)
    {
        $user = User::where('role', 'employee')->findOrFail($id);
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

        return view('admin.attendance.staff', [
            'user' => $user,
            'attendances' => $attendances,
            'currentMonth' => $parsedMonth,
        ]);
    }

    public function exportCsv(Request $request, $id): StreamedResponse
    {
        $month = $request->input('month', now()->format('Y-m'));
        $user = User::findOrFail($id);

        $attendances = Attendance::with('breaks', 'attendanceRequests')
            ->where('user_id', $user->id)
            ->where('date', 'like', "$month%")
            ->orderBy('date', 'asc')
            ->get();

        $monthFormatted = Carbon::parse($month . '-01')->format('Ym');
        $filename = "{$user->name}_{$monthFormatted}_kintai.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return Response::stream(function () use ($attendances, $user, $month) {
            $handle = fopen('php://output', 'w');

            $monthText = Carbon::parse($month . '-01')->format('Y年n月');
            $title = "{$user->name}さんの勤怠 ({$monthText})";

            fputcsv($handle, [mb_convert_encoding($title, 'SJIS-win', 'UTF-8')]);
            fputcsv($handle, []);

            $headersRow = ['日付', '出勤', '退勤', '休憩合計', '勤務時間', '申請ステータス'];
            fputcsv($handle, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $headersRow));

            foreach ($attendances as $attendance) {
                $date = Carbon::parse($attendance->date)->format('Y/m/d');
                $clockIn = $attendance->status === '勤務外' ? '' : Carbon::parse($attendance->clock_in)->format('H:i');
                $clockOut = $attendance->status === '勤務外' ? '' : Carbon::parse($attendance->clock_out)->format('H:i');
                $break = $attendance->total_break_time_formatted;
                $duration = $attendance->work_duration_formatted;
                $status = optional($attendance->attendanceRequests->sortByDesc('created_at')->first())->status ?? '-';

                $row = [$date, $clockIn, $clockOut, $break, $duration, $status];
                fputcsv($handle, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row));
            }

            fclose($handle);
        }, 200, $headers);
    }
}
