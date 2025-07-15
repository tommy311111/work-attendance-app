<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;

class AttendanceRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $reasons = [
            '出勤時間を修正したいです。',
            '退勤時刻を誤って打刻しました。',
            '休憩が正確に入力されていません。',
            '早退のため修正をお願いします。',
            'システムトラブルで打刻できませんでした。',
        ];

        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'reason' => $this->faker->randomElement($reasons),
            'status' => $this->faker->randomElement(['pending', 'approved']),
            'request_type' => $this->faker->randomElement(['clock_in', 'clock_out', 'break']),
        ];
    }
}
