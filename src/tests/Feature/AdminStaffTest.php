<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        $this->employee = User::factory()->create([
            'role' => 'employee',
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);
    }

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $employee2 = User::factory()->create([
            'role' => 'employee',
            'name' => '佐藤花子',
            'email' => 'hanako@example.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('taro@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('hanako@example.com');
    }

    /** @test */
    public function ユーザーの勤怠情報が複数回の休憩も正しく表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id'   => $this->employee->id,
            'date'      => '2025-09-01',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
            'status'    => '退勤済',
        ]);

        BreakTime::factory()->create([
            'attendance_id'  => $attendance->id,
            'break_start_at' => '12:00',
            'break_end_at'   => '12:30',
        ]);

        BreakTime::factory()->create([
            'attendance_id'  => $attendance->id,
            'break_start_at' => '15:00',
            'break_end_at'   => '15:15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.staff', $this->employee->id));

        $response->assertStatus(200);
        $response->assertSee('09:00')
                 ->assertSee('18:00')
                 ->assertSee('0:45')
                 ->assertSee('8:15')
                 ->assertSee($this->employee->name);
    }

    /** @test */
    public function 前月の勤怠データが表示される()
    {
        $prevMonth = Carbon::today()->subMonth();

        $attendances = collect(range(0, 2))->map(function ($i) use ($prevMonth) {
            return Attendance::factory()->create([
                'user_id' => $this->employee->id,
                'status'  => Attendance::STATUS['FINISHED'],
                'date'    => $prevMonth->copy()->startOfMonth()->addDays($i),
            ]);
        });

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.staff', [
                'id'    => $this->employee->id,
                'month' => $prevMonth->format('Y-m')
            ]));

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->date->format('m/d'));
        }
    }

    /** @test */
    public function 翌月の勤怠データが表示される()
    {
        $nextMonth = Carbon::today()->addMonth();

        $attendances = collect(range(0, 2))->map(function ($i) use ($nextMonth) {
            return Attendance::factory()->create([
                'user_id' => $this->employee->id,
                'status'  => Attendance::STATUS['FINISHED'],
                'date'    => $nextMonth->copy()->startOfMonth()->addDays($i),
            ]);
        });

        foreach ($attendances as $i => $attendance) {
            $attendance->date = $nextMonth->copy()->startOfMonth()->addDays($i);
            $attendance->save();
        }

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.staff', [
                'id' => $this->employee->id,
                'month' => $nextMonth->format('Y-m'),
            ]));

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->date->format('m/d'));
        }
    }

    /** @test */
    public function 詳細ボタンを押下すると通常の勤怠詳細画面に遷移する()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->employee->id,
            'date' => '2025-09-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 修正申請が保留中の場合は承認フォームに遷移する()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->employee->id,
            'date' => '2025-09-02',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $attendanceRequest = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->employee->id,
            'status' => 'pending',
            'request_type' => 'edit',
            'requested_clock_in_time' => '2025-09-02 10:30:00',
            'requested_clock_out_time' => '2025-09-02 19:30:00',
            'reason' => '出勤時刻を間違えたため修正依頼',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('stamp_correction_request.approve_form', $attendanceRequest->id));

        $response->assertStatus(200);
        $response->assertSee('10:30');
    }
}
