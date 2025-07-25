<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // 従業員のみ取得
        $employees = User::where('role', 'employee')->get();

        $startDate = Carbon::create(2025, 6, 1);
        $endDate = Carbon::create(2025, 9, 30);
        $allDays = CarbonPeriod::create($startDate, $endDate);

        foreach ($employees as $employee) {
            $workDays = [];

            // 各週ごとに4日を勤務日に設定
            $weekStart = $startDate->copy();
            while ($weekStart->lte($endDate)) {
                $weekEnd = $weekStart->copy()->addDays(6);
                if ($weekEnd->gt($endDate)) {
                    $weekEnd = $endDate->copy();
                }

                // 1週間の日付を配列に
                $weekDates = [];
                $temp = $weekStart->copy();
                while ($temp->lte($weekEnd)) {
                    $weekDates[] = $temp->toDateString();
                    $temp->addDay();
                }

                $workDaysCount = min(4, count($weekDates));
                $randomWorkDays = collect($weekDates)->random($workDaysCount)->all();
                $workDays = array_merge($workDays, $randomWorkDays);

                $weekStart->addWeek();
            }

            foreach ($allDays as $date) {
                $dateStr = $date->format('Y-m-d');

                $status = in_array($dateStr, $workDays)
                    ? collect(['出勤中', '休憩中', '退勤済'])->random()
                    : '勤務外';

                $clockIn = $status !== '勤務外' ? Carbon::parse($dateStr)->setTime(rand(8, 10), rand(0, 59)) : null;
                $clockOut = $status !== '勤務外' ? Carbon::parse($dateStr)->setTime(rand(17, 19), rand(0, 59)) : null;

                $attendance = Attendance::factory()->create([
                    'user_id' => $employee->id,
                    'date' => $dateStr,
                    'status' => $status,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                ]);

                if ($status !== '勤務外') {
                    // 昼休憩（12:00～13:30の範囲でランダム）
                    $breakStart = Carbon::parse($dateStr)->setTime(rand(12, 12), rand(0, 30));
                    $breakEnd = (clone $breakStart)->addMinutes(rand(30, 60));

                    BreakTime::factory()->create([
                        'attendance_id' => $attendance->id,
                        'break_start_at' => $breakStart,
                        'break_end_at' => $breakEnd,
                    ]);

                    // 小休憩（50%の確率で追加）
                    if (rand(0, 1)) {
                        $smallBreakStart = Carbon::parse($dateStr)->setTime(rand(10, 11), rand(0, 59));
                        $smallBreakEnd = (clone $smallBreakStart)->addMinutes(rand(10, 20));

                        BreakTime::factory()->create([
                            'attendance_id' => $attendance->id,
                            'break_start_at' => $smallBreakStart,
                            'break_end_at' => $smallBreakEnd,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Attendances and BreakTimes seeded successfully.');
    }
}
