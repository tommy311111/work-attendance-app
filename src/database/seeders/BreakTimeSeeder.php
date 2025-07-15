<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // すべての勤怠データを取得
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            // ランダムで 1〜3 件の休憩を作成
            $breakCount = rand(1, 3);

            for ($i = 0; $i < $breakCount; $i++) {
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                ]);
            }
        }

        $this->command->info('BreakTimes seeded successfully.');
    }
}
