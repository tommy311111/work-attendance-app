<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;
    protected $table = 'breaks';

    protected $guarded = [
        'id',
    ];

    public function attendance()
    {
    return $this->belongsTo(Attendance::class);
    }

    public function attendanceRequestBreaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }

}
