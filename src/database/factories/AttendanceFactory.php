<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $clockIn = $this->faker->dateTimeBetween('09:00:00', '09:30:00');
        $clockOut = (clone $clockIn)->modify('+8 hours');

        return [
            'user_id' => User::factory(),
            'date' => $this->faker->dateTimeBetween('2025-04-01', '2025-09-30')->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ];
    }
}
