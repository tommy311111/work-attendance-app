<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2025, 8, 27, 9, 0));

        $user = User::factory()->create();
        $today = Carbon::today();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => $today->toDateString(),
            'status'  => Attendance::STATUS['OFF_DUTY'],
        ]);

        $this->actingAs($user)->post('/attendance/action', [
            'action' => 'start_work',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'date'    => $today->toDateString(),
            'status'  => Attendance::STATUS['WORKING'],
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        Carbon::setTestNow(Carbon::create(2025, 8, 27, 9, 0));

        $user = User::factory()->create();
        $today = Carbon::today();

        Attendance::factory()->create([
            'user_id'  => $user->id,
            'date'     => $today,
            'status'   => Attendance::STATUS['FINISHED'],
            'clock_in' => now()->subHours(8),
            'clock_out'=> now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧で確認できる()
    {
        $user = User::factory()->create();
        $today = Carbon::today();

        Attendance::factory()->create([
            'user_id'  => $user->id,
            'date'     => $today,
            'status'   => Attendance::STATUS['OFF_DUTY'],
            'clock_in' => null,
            'clock_out'=> null,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.action'), [
            'action' => 'start_work',
        ]);
        $response->assertRedirect(route('attendance.create'));

        $listResponse = $this->get(route('attendance.index'));

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $expectedClockIn = $attendance->clock_in->format('H:i');

        $listResponse->assertSee($expectedClockIn);
        $this->assertEquals(Attendance::STATUS['WORKING'], $attendance->status);
    }
}
