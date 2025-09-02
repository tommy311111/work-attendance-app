<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AdminAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function その日になされた全ユーザーの勤怠情報が確認できる()
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $user1  = User::factory()->create();
        $user2  = User::factory()->create();
        $date   = Carbon::today()->format('Y-m-d');

        $attendance1 = Attendance::factory()->create([
            'user_id'   => $user1->id,
            'date'      => $date,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'status'    => '退勤済',
        ]);
        BreakTime::factory()->create([
            'attendance_id'  => $attendance1->id,
            'break_start_at' => '12:00',
            'break_end_at'   => '12:30',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id'   => $user2->id,
            'date'      => $date,
            'clock_in'  => '10:00',
            'clock_out' => '19:00',
            'status'    => '退勤済',
        ]);
        BreakTime::factory()->create([
            'attendance_id'  => $attendance2->id,
            'break_start_at' => '13:00',
            'break_end_at'   => '13:45',
        ]);

        $response = $this->actingAs($admin)
                         ->get(route('admin.attendance.index', ['date' => $date]));

        $response->assertStatus(200)
                 ->assertSee($user1->name)
                 ->assertSee('09:00')
                 ->assertSee('18:00')
                 ->assertSee('0:30')
                 ->assertSee('8:30')
                 ->assertSee($user2->name)
                 ->assertSee('10:00')
                 ->assertSee('19:00')
                 ->assertSee('0:45')
                 ->assertSee('8:15');
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $today = Carbon::today();

        $response = $this->actingAs($admin)->get(route('admin.attendance.index'));

        $response->assertStatus(200)
                 ->assertSee($today->format('Y年n月j日'))
                 ->assertSee($today->format('Y/m/d'));
    }

    /** @test */
    public function 前日ボタンを押すと前日の勤怠情報が表示される()
    {
        $admin     = User::factory()->create(['role' => 'admin']);
        $user      = User::factory()->create();
        $yesterday = Carbon::yesterday();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => $yesterday->format('Y-m-d'),
            'clock_in'  => '08:00',
            'clock_out' => '17:00',
            'status'    => '退勤済',
        ]);

        BreakTime::factory()->create([
            'attendance_id'  => $attendance->id,
            'break_start_at' => '12:00',
            'break_end_at'   => '12:30',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', [
            'date' => $yesterday->format('Y-m-d')
        ]));

        $response->assertStatus(200)
                 ->assertSee($yesterday->format('Y年n月j日'))
                 ->assertSee('08:00')
                 ->assertSee('17:00')
                 ->assertSee('0:30')
                 ->assertSee('8:30');
    }

    /** @test */
    public function 翌日ボタンを押すと翌日の勤怠情報が表示される()
    {
        $admin    = User::factory()->create(['role' => 'admin']);
        $user     = User::factory()->create();
        $tomorrow = Carbon::tomorrow();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'date'      => $tomorrow->format('Y-m-d'),
            'clock_in'  => '11:00',
            'clock_out' => '20:00',
            'status'    => '退勤済',
        ]);

        BreakTime::factory()->create([
            'attendance_id'  => $attendance->id,
            'break_start_at' => '15:00',
            'break_end_at'   => '16:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', [
            'date' => $tomorrow->format('Y-m-d')
        ]));

        $response->assertStatus(200)
                 ->assertSee($tomorrow->format('Y年n月j日'))
                 ->assertSee('11:00')
                 ->assertSee('20:00')
                 ->assertSee('1:00')
                 ->assertSee('8:00');
    }
}
