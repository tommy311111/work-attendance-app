<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // 従業員だけ取得
        $employees = User::where('role', 'employee')->get();

        // 勤務期間（全日）を設定
        $startDate = Carbon::create(2025, 6, 1);
        $endDate = Carbon::create(2025, 9, 30);
        $allDays = CarbonPeriod::create($startDate, $endDate); // 6月〜9月の全日

        foreach ($employees as $employee) {
            $workDays = [];

            // 各週ごとに4日を勤務日として選出（6月〜9月）
            $weekStart = $startDate->copy();
            while ($weekStart->lte($endDate)) {
                $weekEnd = $weekStart->copy()->addDays(6);
                if ($weekEnd->gt($endDate)) {
                    $weekEnd = $endDate->copy();
                }

                // 今週の全日を取得
                $weekDates = [];
                $temp = $weekStart->copy();
                while ($temp->lte($weekEnd)) {
                    $weekDates[] = $temp->toDateString();
                    $temp->addDay();
                }

                // 最大4日を勤務日に設定
                $workDaysCount = min(4, count($weekDates));
                $randomWorkDays = (array)array_rand(array_flip($weekDates), $workDaysCount);
                $workDays = array_merge($workDays, $randomWorkDays);

                // 次の週へ
                $weekStart->addWeek();
            }

            // すべての日について、勤務日ならランダムステータス、その他は勤務外
            foreach ($allDays as $date) {
                $dateStr = $date->format('Y-m-d');

                $status = in_array($dateStr, $workDays)
                    ? collect(['出勤中', '休憩中', '退勤済'])->random()
                    : '勤務外';

                Attendance::factory()->create([
                    'user_id' => $employee->id,
                    'date' => $dateStr,
                    'status' => $status,
                ]);
            }
        }

        $this->command->info('Attendances seeded successfully.');
    }
}
