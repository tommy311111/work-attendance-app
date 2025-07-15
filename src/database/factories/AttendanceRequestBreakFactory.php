<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;

class AttendanceRequestBreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'break_id' => BreakTime::factory(),
        ];
    }
}
