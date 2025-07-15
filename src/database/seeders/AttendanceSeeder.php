<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // 管理者取得（今回は未使用）
        $admin = User::where('role', 'admin')->first();

        // 従業員だけ取得
        $employees = User::where('role', 'employee')->get();

        // 勤務期間開始・終了日
        $startDate = Carbon::create(2025, 6, 1);
        $endDate = Carbon::create(2025, 9, 30);

        foreach ($employees as $employee) {
            $currentDate = $startDate->copy();

            while ($currentDate->lte($endDate)) {
                // 1週間の範囲の開始日と終了日を決める
                $weekStart = $currentDate->copy();
                $weekEnd = $weekStart->copy()->addDays(6);
                if ($weekEnd->gt($endDate)) {
                    $weekEnd = $endDate->copy();
                }

                // その週の日付リストを作成
                $weekDates = [];
                $tempDate = $weekStart->copy();
                while ($tempDate->lte($weekEnd)) {
                    $weekDates[] = $tempDate->toDateString();
                    $tempDate->addDay();
                }

                // その中からランダムに4日（または週の残り日数）選ぶ
                $workDaysCount = min(4, count($weekDates));
                $workDays = (array)array_rand(array_flip($weekDates), $workDaysCount);

                // 選んだ日付で勤怠を作成
                foreach ($workDays as $date) {
                    Attendance::factory()->create([
                        'user_id' => $employee->id,
                        'date' => $date,
                    ]);
                }

                // 次の週へ
                $currentDate->addWeek();
            }
        }

        $this->command->info('Attendances seeded successfully.');
    }
}
