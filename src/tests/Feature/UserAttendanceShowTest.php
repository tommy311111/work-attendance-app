<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime; // もし休憩モデルがある場合
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UserAttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に名前が表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $this->actingAs($user)
             ->get(route('attendance.show', $attendance->id))
             ->assertStatus(200)
             ->assertSee(str_replace(' ', '　', $user->name));
    }

    /** @test */
    public function 勤怠詳細画面に日付が表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::parse('2025-08-29'),
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $this->actingAs($user)
             ->get(route('attendance.show', $attendance->id))
             ->assertStatus(200)
             ->assertSee($attendance->date->format('Y年'))
             ->assertSee($attendance->date->format('n月 j日'));
    }

    /** @test */
    public function 勤怠詳細画面に出勤退勤時間が表示される()
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
             ->assertSee($attendance->clock_in->format('H:i'))
             ->assertSee($attendance->clock_out->format('H:i'));
    }

    /** @test */
    public function 勤怠詳細画面に休憩時間が表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        // 休憩がある場合
        $break1 = $attendance->breaks()->create([
            'break_start_at' => Carbon::parse('12:00'),
            'break_end_at' => Carbon::parse('12:45'),
        ]);

        $break2 = $attendance->breaks()->create([
            'break_start_at' => Carbon::parse('15:00'),
            'break_end_at' => Carbon::parse('15:15'),
        ]);

        $this->actingAs($user)
             ->get(route('attendance.show', $attendance->id))
             ->assertStatus(200)
             ->assertSee($break1->break_start_at->format('H:i'))
             ->assertSee($break1->break_end_at->format('H:i'))
             ->assertSee($break2->break_start_at->format('H:i'))
             ->assertSee($break2->break_end_at->format('H:i'));
    }
}
