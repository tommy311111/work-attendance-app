<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use App\Models\AttendanceRequestBreak;


class AttendanceRequestBreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         $requests = AttendanceRequest::all();

        foreach ($requests as $request) {
            // 該当の Attendance に紐づく BreakTime を取得
            $breaks = BreakTime::where('attendance_id', $request->attendance_id)->get();

            if ($breaks->count() > 0) {
                // 1〜2件ランダムに紐づける（重複回避）
                $count = rand(1, min(2, $breaks->count()));
                $selected = $breaks->random($count);

                foreach ($selected as $break) {
                    AttendanceRequestBreak::factory()->create([
                        'attendance_request_id' => $request->id,
                        'break_id' => $break->id,
                    ]);
                }
            }
        }

        $this->command->info('AttendanceRequestBreaks seeded successfully.');
    }
}
