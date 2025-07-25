<?php

namespace Database\Factories;

use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        return []; // 状態クラス（state）で休憩時間を決めるためここは空
    }

    // 昼休憩（例：12:00〜13:00の間）
    public function lunch()
    {
        $start = $this->faker->dateTimeBetween('12:00:00', '12:30:00');
        $end = (clone $start)->modify('+60 minutes');

        return $this->state([
            'break_start_at' => $start,
            'break_end_at' => $end,
        ]);
    }

    // 小休憩（例：10:00〜16:00の間で15分間）
    public function short()
    {
        $start = $this->faker->dateTimeBetween('10:00:00', '16:00:00');
        $end = (clone $start)->modify('+15 minutes');

        return $this->state([
            'break_start_at' => $start,
            'break_end_at' => $end,
        ]);
    }
}
