<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        $dateInput = $this->faker->dateTimeBetween('2025-07-01', '2025-09-30');
        $carbonDate = Carbon::parse($dateInput);

        $clockIn = Carbon::createFromTime(9, rand(0, 30));
        $clockOut = (clone $clockIn)->addHours(8);

        return [
            'date' => $carbonDate->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => '出勤中',
        ];
    }

    public function offWork()
    {
        return $this->state(function (array $attributes) {
            return [
                'clock_in' => null,
                'clock_out' => null,
                'status' => '勤務外',
            ];
        });
    }
}
