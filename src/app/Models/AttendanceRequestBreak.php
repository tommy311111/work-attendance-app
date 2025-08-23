<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequestBreak extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }

    protected $casts = [
        'requested_start_time' => 'datetime',
        'requested_end_time' => 'datetime',
    ];
}
