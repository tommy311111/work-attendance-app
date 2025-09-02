<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分の勤怠情報が全て表示される()
    {
        $user = User::factory()->create();

        // 勤怠情報を複数作成
        $attendances = Attendance::factory()->count(3)->sequence(
    fn ($sequence) => [
        'user_id' => $user->id,
        'status' => Attendance::STATUS['FINISHED'],
        'clock_in' => now()->startOfMonth()->addDays($sequence->index)->setTime(9, 0),
        'clock_out' => now()->startOfMonth()->addDays($sequence->index)->setTime(18, 0),
        'date' => now()->startOfMonth()->addDays($sequence->index),
    ]
)->create();


        $this->actingAs($user)
             ->get(route('attendance.index'))
             ->assertStatus(200)
             ->assertSee($attendances[0]->date->format('m/d'))
             ->assertSee($attendances[1]->date->format('m/d'))
             ->assertSee($attendances[2]->date->format('m/d'));
    }

    /** @test */
    public function 勤怠一覧画面に現在の月が表示される()
    {
        $user = User::factory()->create();
        $today = Carbon::today();

        $this->actingAs($user)
             ->get(route('attendance.index'))
             ->assertStatus(200)
             ->assertSee($today->format('Y/m'));
    }

    /** @test */
    public function 前月の勤怠データが表示される()
    {
        $user = User::factory()->create();
        $prevMonth = Carbon::today()->subMonth();

        // 前月の勤怠を3件作成
        $attendances = Attendance::factory()->count(3)->make([
            'user_id' => $user->id,
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        foreach ($attendances as $i => $attendance) {
            $attendance->date = $prevMonth->copy()->startOfMonth()->addDays($i);
            $attendance->save();
        }

        // 前月ページにアクセス
        $response = $this->actingAs($user)
                         ->get(route('attendance.index', ['month' => $prevMonth->format('Y-m')]))
                         ->assertStatus(200);

        // それぞれの日付が表示されているか確認
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->date->format('m/d'));
        }
    }

    /** @test */
    public function 翌月の勤怠データが表示される()
    {
        $user = User::factory()->create();
        $nextMonth = Carbon::today()->addMonth();

        // 翌月の勤怠を3件作成
        $attendances = Attendance::factory()->count(3)->make([
            'user_id' => $user->id,
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        foreach ($attendances as $i => $attendance) {
            $attendance->date = $nextMonth->copy()->startOfMonth()->addDays($i);
            $attendance->save();
        }

        // 翌月ページにアクセス
        $response = $this->actingAs($user)
                         ->get(route('attendance.index', ['month' => $nextMonth->format('Y-m')]))
                         ->assertStatus(200);

        // それぞれの日付が表示されているか確認
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->date->format('m/d'));
        }
    }

    /** @test */
    public function 詳細ボタンを押すと勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        $this->actingAs($user)
             ->get(route('attendance.show', $attendance->id))
             ->assertStatus(200)
             ->assertSee('勤怠詳細')
             ->assertSee($attendance->date->format('Y年'));
    }
}
