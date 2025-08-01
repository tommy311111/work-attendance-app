<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function user()
    {
    return $this->belongsTo(User::class);
    }

    public function attendance()
    {
    return $this->belongsTo(Attendance::class);
    }

    public function attendanceRequestBreaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }
}
