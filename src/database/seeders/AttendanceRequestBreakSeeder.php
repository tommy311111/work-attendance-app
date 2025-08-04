<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AttendanceRequestBreakSeeder extends Seeder
{
    public function run()
    {
        $requests = AttendanceRequest::where('request_type', 'break')->get();

        foreach ($requests as $request) {
            $attendance = $request->attendance;

            if (!$attendance || !$attendance->clock_in || !$attendance->clock_out) {
                continue;
            }

            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            // 該当の Attendance に紐づく BreakTime を取得
            $breaks = BreakTime::where('attendance_id', $attendance->id)->get();

            if ($breaks->isEmpty()) {
                continue;
            }

            // 1〜2件ランダムに紐づける（重複回避）
            $selectedBreaks = $breaks->random(rand(1, min(2, $breaks->count())));

            foreach ($selectedBreaks as $break) {
                $originalStart = Carbon::parse($break->break_start_at);
                $originalEnd = Carbon::parse($break->break_end_at);

                // ランダムに±15分でずらす
                $requestedStart = $originalStart->copy()->addMinutes(rand(-15, 15));
                $requestedEnd = $originalEnd->copy()->addMinutes(rand(-15, 15));

                // 出勤～退勤の範囲に収める
                $requestedStart = $requestedStart->lt($clockIn) ? $clockIn->copy() : $requestedStart;
                $requestedStart = $requestedStart->gt($clockOut) ? $clockOut->copy()->subMinutes(5) : $requestedStart;

                $requestedEnd = $requestedEnd->gt($clockOut) ? $clockOut->copy() : $requestedEnd;
                $requestedEnd = $requestedEnd->lt($requestedStart) ? $requestedStart->copy()->addMinutes(5) : $requestedEnd;

                AttendanceRequestBreak::create([
                    'attendance_request_id' => $request->id,
                    'break_id' => $break->id,
                    'requested_start_time' => $requestedStart,
                    'requested_end_time' => $requestedEnd,
                ]);
            }
        }

        $this->command->info('AttendanceRequestBreaks seeded successfully.');
    }
}
