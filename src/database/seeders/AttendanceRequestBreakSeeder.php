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

            $breaks = BreakTime::where('attendance_id', $attendance->id)->get();

            if ($breaks->isEmpty()) {
                continue;
            }

            $selectedBreaks = $breaks->random(rand(1, min(2, $breaks->count())));

            foreach ($selectedBreaks as $break) {
                $originalStart = Carbon::parse($break->break_start_at);
                $originalEnd = Carbon::parse($break->break_end_at);

                $requestedStart = $originalStart->copy()->addMinutes(rand(-15, 15));
                $requestedEnd = $originalEnd->copy()->addMinutes(rand(-15, 15));

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
