<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;


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

    public function staffAttendance(Request $request, $id)
{
    // 1人のユーザーを取得（または404）
    $user = User::where('role', 'employee')->findOrFail($id);

    // 表示対象の年月を取得（なければ今月）
    $month = $request->input('month', Carbon::now()->format('Y-m'));
    $parsedMonth = Carbon::createFromFormat('Y-m', $month);

    // 勤怠情報＋その休憩情報もまとめて取得
    $attendances = Attendance::with('breaks', 'attendanceRequests') // ← ここ
        ->where('user_id', $user->id)
        ->where('date', 'like', "$month%")
        ->orderBy('date', 'asc')
        ->get();

// 各勤怠に status を追加（pending/approved/none）
    $attendances = $attendances->map(function ($attendance) {
        $latestRequest = $attendance->attendanceRequests->sortByDesc('created_at')->first();
        if ($latestRequest) {
            $attendance->request_status = $latestRequest->status; // 例: 'pending', 'approved'
        } else {
            $attendance->request_status = null;
        }
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

    $monthFormatted = \Carbon\Carbon::parse($month . '-01')->format('Ym');
$filename = "{$user->name}_{$monthFormatted}_kintai.csv";

// Shift_JISに変換してContent-Dispositionにセット
$headers = [
    'Content-Type' => 'text/csv; charset=Shift_JIS',
    'Content-Disposition' => 'attachment; filename="' . mb_convert_encoding($filename, 'SJIS-win', 'UTF-8') . '"',
];


    $headers = [
        'Content-Type' => 'text/csv; charset=Shift_JIS',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ];

    return Response::stream(function () use ($attendances, $user, $month) {
        $handle = fopen('php://output', 'w');

        // Excelで文字化けしないようUTF-8→SJIS変換
        $monthText = \Carbon\Carbon::parse($month . '-01')->format('Y年n月');
        $title = "{$user->name}さんの勤怠 ({$monthText})";

        fputcsv($handle, [mb_convert_encoding($title, 'SJIS-win', 'UTF-8')]);
        fputcsv($handle, []); // 空行

        // 項目名
        $headersRow = ['日付', '出勤', '退勤', '休憩合計', '勤務時間', '申請ステータス'];
        fputcsv($handle, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $headersRow));

        // データ
        foreach ($attendances as $a) {
            $date = \Carbon\Carbon::parse($a->date)->format('Y/m/d');
            $clockIn = $a->status === '勤務外' ? '' : \Carbon\Carbon::parse($a->clock_in)->format('H:i');
            $clockOut = $a->status === '勤務外' ? '' : \Carbon\Carbon::parse($a->clock_out)->format('H:i');
            $break = $a->total_break_time_formatted;
            $duration = $a->work_duration_formatted;
            $status = optional($a->attendanceRequests->sortByDesc('created_at')->first())->status ?? '-';

            $row = [$date, $clockIn, $clockOut, $break, $duration, $status];
            fputcsv($handle, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $row));
        }

        fclose($handle);
    }, 200, $headers);
}

}
