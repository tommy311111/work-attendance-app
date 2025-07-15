<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AttendanceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUser = User::where('role', 'admin')->first();  // 管理者取得
         // すべてのAttendanceを取得
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            // 10～20% の確率で申請を作成
            if (rand(1, 100) <= 15) {
                AttendanceRequest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'reviewed_by' => $adminUser ? $adminUser->id : null,
                ]);
            }
        }

        $this->command->info('AttendanceRequests seeded successfully.');
    }
}
