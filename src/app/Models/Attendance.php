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
        return '-';
    }

    $hours = floor($totalMinutes / 60);
    $minutes = $totalMinutes % 60;

    return $hours . ':' . sprintf('%02d', $minutes);
}
}
