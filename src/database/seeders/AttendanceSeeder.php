<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonPeriod;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'employee')->take(3)->get();
        $period = CarbonPeriod::create('2025-07-01', '2025-09-30');

        foreach ($users as $user) {
            $workDayCounter = 0;

            foreach ($period as $date) {
                $isWorkDay = ($workDayCounter < 5);

                if ($isWorkDay) {
                    Attendance::factory()->create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                    ]);
                } else {
                    Attendance::factory()->offWork()->create([
                        'user_id' => $user->id,
                        'date' => $date->format('Y-m-d'),
                    ]);
                }

                $workDayCounter++;
                if ($workDayCounter >= 7) {
                    $workDayCounter = 0;
                }
            }
        }
    }
}
