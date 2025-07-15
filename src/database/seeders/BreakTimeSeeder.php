<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimeSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $breakCount = rand(1, 2);
            $breaks = [];

            $workStart = Carbon::parse($attendance->clock_in);
            $workEnd = Carbon::parse($attendance->clock_out);

            for ($i = 0; $i < $breakCount; $i++) {
                $tries = 0;

                do {
                    $start = $workStart->copy()->addMinutes(rand(60, 240));
                    $duration = rand(5, 60);
                    $end = $start->copy()->addMinutes($duration);

                    if ($end->gt($workEnd)) {
                        $overlaps = true;
                        $tries++;
                        continue;
                    }

                    $overlaps = false;
                    foreach ($breaks as $existing) {
                        if (
                            $start->lt($existing['end']) &&
                            $end->gt($existing['start'])
                        ) {
                            $overlaps = true;
                            break;
                        }
                    }

                    $tries++;
                } while ($overlaps && $tries < 10);

                if (!$overlaps) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'user_id' => $attendance->user_id,
                        'break_start_at' => $start,
                        'break_end_at' => $end,
                    ]);

                    $breaks[] = ['start' => $start, 'end' => $end];
                } elseif ($tries >= 10) {
                    $this->command->warn("Couldn't create non-overlapping break for Attendance ID: {$attendance->id}");
                }
            }
        }

        $this->command->info('BreakTimes seeded successfully.');
    }
}
