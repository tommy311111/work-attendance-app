<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
