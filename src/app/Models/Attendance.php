<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
    return $this->hasMany(BreakTime::class);
    }

    public function attendanceRequests()
    {
    return $this->hasMany(AttendanceRequest::class);
    }

    public const STATUS = [
        'OFF_DUTY' => '勤務外',
        'WORKING' => '出勤中',
        'ON_BREAK' => '休憩中',
        'FINISHED' => '退勤済',
    ];

    public function getTotalBreakTimeFormattedAttribute()
{
    $totalMinutes = 0;

    foreach ($this->breaks as $break) {
        if ($break->break_start_at && $break->break_end_at) {
            $start = \Carbon\Carbon::parse($break->break_start_at);
            $end = \Carbon\Carbon::parse($break->break_end_at);
            $totalMinutes += $start->diffInMinutes($end);
        }
    }

    if ($totalMinutes <= 0) {
        return '';
    }

    $hours = floor($totalMinutes / 60);
    $minutes = $totalMinutes % 60;

    return $hours . ':' . sprintf('%02d', $minutes);
}

    public function getWorkDurationFormattedAttribute()
{
    if (! $this->clock_in || ! $this->clock_out) {
        return null;
    }

    // 出勤・退勤の差（分）
    $start = Carbon::parse($this->clock_in);
    $end = Carbon::parse($this->clock_out);
    $totalWorkMinutes = $end->diffInMinutes($start);

    // 休憩合計（分）
    $totalBreakMinutes = $this->breaks->sum(function ($break) {
        if ($break->break_start_at && $break->break_end_at) {
            $start = Carbon::parse($break->break_start_at);
            $end = Carbon::parse($break->break_end_at);
            return $end->diffInMinutes($start);
        }
        return 0;
    });

    // 実働時間（分）
    $actualMinutes = $totalWorkMinutes - $totalBreakMinutes;

    if ($actualMinutes <= 0) {
        return null;
    }

    // 分 → 時:分 形式に整形（例: 1:05）
    $hours = floor($actualMinutes / 60);
    $minutes = $actualMinutes % 60;

    return sprintf('%d:%02d', $hours, $minutes);
}

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
    'clock_out' => 'datetime',
    ];

}
