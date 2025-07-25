<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimeSeeder extends Seeder
{
    public function run(): void
{
    $attendances = Attendance::all();

    foreach ($attendances as $attendance) {
        if ($attendance->status !== '勤務外') {
            // 昼休憩（1回）
            BreakTime::factory()
                ->lunch()
                ->create([
                    'attendance_id' => $attendance->id,
                ]);

            // 小休憩（ランダムで0〜2回）
            $shortBreakCount = rand(0, 2);
            for ($i = 0; $i < $shortBreakCount; $i++) {
                BreakTime::factory()
                    ->short()
                    ->create([
                        'attendance_id' => $attendance->id,
                    ]);
            }
        }
    }
}
}
