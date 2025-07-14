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
    return $this->belongsTo(Attendance::class);
    }

    public function BreakTimeRequest()
    {
    return $this->belongsTo(BreakTime::class);
    }
}
