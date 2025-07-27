<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;


class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
{
    $dateInput = $this->faker->dateTimeBetween('2025-07-01', '2025-09-30');
    $carbonDate = Carbon::parse($dateInput);

    // デフォルトの状態は「出勤」でセット（clock_in, clock_out付き）
    $clockIn = Carbon::createFromTime(9, rand(0, 30));
    $clockOut = (clone $clockIn)->addHours(8);

    return [
        'date' => $carbonDate->format('Y-m-d'),
        'clock_in' => $clockIn,
        'clock_out' => $clockOut,
        'status' => '出勤',
    ];
}

// 勤務外状態のstateメソッドを用意
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
