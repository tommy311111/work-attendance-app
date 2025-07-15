<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;


class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = $this->faker->dateTimeBetween('10:00:00', '16:00:00');
        $duration = rand(5, 60); // 5分〜60分のランダム
        $end = (clone $start)->modify("+{$duration} minutes");

        return [
            'attendance_id' => Attendance::factory(),
            'break_start_at' => $start,
            'break_end_at' => $end,
        ];
    }
}
