<?php

namespace App\Http\Controllers\User;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class UserAttendanceController extends Controller
{
    // 勤怠画面の表示
    public function show()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 本日の勤怠データ取得 or 新規作成
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'date' => $today],
            ['status' => '勤務外']  // 初期状態
        );

        return view('user.attendance.create', compact('attendance'));

    }
}
