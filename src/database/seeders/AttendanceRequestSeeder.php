<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceRequestSeeder extends Seeder
{
    public function run()
    {
        $adminUser = User::where('role', 'admin')->first();
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            if (rand(1, 100) <= 8) { // 10%の確率で申請を作成

                $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in)->addMinutes(rand(-15, 15)) : null;
                $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out)->addMinutes(rand(-15, 15)) : null;

                $options = [];

                if ($clockIn) {
                    $options['requested_clock_in_time'] = $clockIn;
                }

                if ($clockOut) {
                    $options['requested_clock_out_time'] = $clockOut;
                }

                // 必ず1つは含める（両方ある場合はランダムに1つ or 両方）
                $keys = array_keys($options);
                shuffle($keys);
                $selectedKeys = array_slice($keys, 1); // 最低1つは選ばれる（max 2）

                $data = [
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'reviewed_by' => isset($adminUser) ? $adminUser->id : null,

                ];

                foreach ($selectedKeys as $key) {
                    $data[$key] = $options[$key];
                }

                AttendanceRequest::factory()->create($data);
            }
        }

        $this->command->info('AttendanceRequests seeded successfully.');
    }
}
