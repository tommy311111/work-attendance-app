<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者（承認者）を取得
        $admin = User::where('role', 'admin')->first();

        // 従業員のみ取得
        $employees = User::where('role', 'employee')->get();

        // 期間：2025年4月1日〜2025年9月30日
        $startDate = Carbon::create(2025, 4, 1);
        $endDate = Carbon::create(2025, 9, 30);

        foreach ($employees as $employee) {
            $date = $startDate->copy();

            while ($date->lte($endDate)) {
                // 土日を除外（平日のみ出勤）
                if (!$date->isWeekend()) {
                    // 勤怠データを1日分作成
                    Attendance::factory()->create([
                        'user_id' => $employee->id,
                        'date' => $date->format('Y-m-d'),
                    ]);
                }

                $date->addDay();
            }
        }

        $this->command->info('Attendances seeded successfully.');
    }
}
