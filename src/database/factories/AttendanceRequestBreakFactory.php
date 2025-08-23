<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;

class AttendanceRequestBreakFactory extends Factory
{
    public function definition()
    {
        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'break_id' => BreakTime::factory(),
        ];
    }
}
