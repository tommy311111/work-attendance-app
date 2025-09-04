<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceUpdateValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertStatus(200)
            ->assertSee('勤怠詳細');

        $response = $this->actingAs($user)
            ->post(route('attendance-requests.store', $attendance->id), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'reason' => '備考',
            ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤より後の場合エラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertStatus(200);

        $response = $this->actingAs($user)
            ->post(route('attendance-requests.store', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['id' => null, 'break_start' => '19:00', 'break_end' => '19:30'],
                ],
                'reason' => '備考',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤より後の場合エラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertStatus(200);

        $response = $this->actingAs($user)
            ->post(route('attendance-requests.store', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['id' => null, 'break_start' => '12:00', 'break_end' => '19:00'],
                ],
                'reason' => '備考',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーになる()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id))
            ->assertStatus(200);

        $response = $this->actingAs($user)
            ->post(route('attendance-requests.store', $attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'reason' => '',
            ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください。',
        ]);
    }
}
