<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UserAttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
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
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
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
    public function 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
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
    public function 「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS['FINISHED'],
        ]);

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
