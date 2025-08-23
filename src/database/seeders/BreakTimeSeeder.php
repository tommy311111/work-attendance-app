<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTimeSeeder extends Seeder
{
    public function run(): void
    {
        $attendances = Attendance::where('status', '出勤')->get();

        foreach ($attendances as $attendance) {
            if ($attendance->status !== '勤務外') {
                BreakTime::factory()
                    ->lunch()
                    ->create([
                        'attendance_id' => $attendance->id,
                    ]);

                if (rand(0, 1) === 1) {
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
