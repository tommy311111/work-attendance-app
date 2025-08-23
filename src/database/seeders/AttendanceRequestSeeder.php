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
            if (rand(1, 100) <= 8) {
                $originalClockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
                $originalClockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

                if (!$originalClockIn || !$originalClockOut) {
                    continue;
                }

                $clockIn = $originalClockIn->copy()->addMinutes(rand(-15, 15));
                $clockOut = $originalClockOut->copy()->addMinutes(rand(-15, 15));

                if ($clockIn->gt($clockOut)) {
                    [$clockIn, $clockOut] = [$clockOut, $clockIn];
                }

                $requestType = collect(['clock_in', 'clock_out', 'break'])->random();

                AttendanceRequest::factory()->create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'reviewed_by' => $adminUser ? $adminUser->id : null,
                    'requested_clock_in_time' => $clockIn,
                    'requested_clock_out_time' => $clockOut,
                ]);
            }
        }

        $this->command->info('AttendanceRequests seeded successfully.');
    }
}
