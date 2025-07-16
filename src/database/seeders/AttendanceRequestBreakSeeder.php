<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

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
                $selectedBreaks = $breaks->random($count);

                foreach ($selectedBreaks as $break) {
                    // 元の時刻から±15分の範囲でランダム修正
                    $requestedStart = $break->break_start_at
                        ? Carbon::parse($break->break_start_at)->addMinutes(rand(-15, 15))
                        : null;

                    $requestedEnd = $break->break_end_at
                        ? Carbon::parse($break->break_end_at)->addMinutes(rand(-15, 15))
                        : null;

                    // ランダムにどちらかまたは両方を選ぶ（最低1つ）
                    $options = [
                        'requested_start_time' => $requestedStart,
                        'requested_end_time' => $requestedEnd,
                    ];

                    $keys = array_keys($options);
                    shuffle($keys);
                    $selectedKeys = array_slice($keys, rand(1, count($keys)));

                    // 作成用データに含める
                    $data = [
                        'attendance_request_id' => $request->id,
                        'break_id' => $break->id,
                    ];

                    foreach ($selectedKeys as $key) {
                        $data[$key] = $options[$key];
                    }

                    AttendanceRequestBreak::factory()->create($data);
                }
            }
        }

        $this->command->info('AttendanceRequestBreaks seeded successfully.');
    }
}
