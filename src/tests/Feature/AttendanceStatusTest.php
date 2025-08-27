<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務外の場合_ステータスが表示される()
    {
        $user  = User::factory()->create();
        $today = Carbon::create(2025, 8, 27);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => $today,
            'status'  => Attendance::STATUS['OFF_DUTY'],
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合_ステータスが表示される()
    {
        $user  = User::factory()->create();
        $today = Carbon::create(2025, 8, 27);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => $today,
            'status'  => Attendance::STATUS['WORKING'],
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合_ステータスが表示される()
    {
        $user  = User::factory()->create();
        $today = Carbon::create(2025, 8, 27);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => $today,
            'status'  => Attendance::STATUS['ON_BREAK'],
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合_ステータスが表示される()
    {
        $user  = User::factory()->create();
        $today = Carbon::create(2025, 8, 27);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => $today,
            'status'  => Attendance::STATUS['FINISHED'],
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
