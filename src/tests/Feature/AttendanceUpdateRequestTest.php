<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 修正申請が実行され管理者画面に表示される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'date' => Carbon::parse('2025-08-29'),
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $requestedClockIn = Carbon::parse($attendance->date->format('Y-m-d') . ' 09:30');
        $requestedClockOut = Carbon::parse($attendance->date->format('Y-m-d') . ' 18:30');

        $response = $this->actingAs($user)
            ->post(route('attendance-requests.store', $attendance->id), [
                'clock_in' => $requestedClockIn->format('H:i'),
                'clock_out' => $requestedClockOut->format('H:i'),
                'breaks' => [],
                'reason' => '打刻修正テスト',
            ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => $requestedClockIn->toDateTimeString(),
            'reason' => '打刻修正テスト',
            'status' => 'pending',
        ]);

        $attendanceRequest = AttendanceRequest::first();

        $this->actingAs($admin)
            ->get(route('stamp_correction_request.approve_form', $attendanceRequest->id))
            ->assertStatus(200)
            ->assertSee(str_replace(' ', '　', $user->name))
            ->assertSee($requestedClockIn->format('H:i'))
            ->assertSee($requestedClockOut->format('H:i'))
            ->assertSee('打刻修正テスト');

        $this->actingAs($admin)
            ->get(route('attendance_requests.list', ['status' => 'pending']))
            ->assertStatus(200)
            ->assertSee($user->name)
            ->assertSee('承認待ち')
            ->assertSee($attendance->date->format('Y/m/d'))
            ->assertSee('打刻修正テスト');
    }
}
