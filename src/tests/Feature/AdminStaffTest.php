<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
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

        // 管理者ユーザー
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com',
        ]);

        // 一般ユーザー
        $this->employee = User::factory()->create([
            'role' => 'employee',
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);
    }

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('taro@example.com');
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        // 勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $this->employee->id,
            'date' => '2025-09-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.staff', $this->employee->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
public function 前月の勤怠データが表示される()
{
    $prevMonth = Carbon::today()->subMonth();

    $attendances = Attendance::factory()->count(3)->create([
        'user_id' => $this->employee->id,
        'status' => Attendance::STATUS['FINISHED'],
    ]);

    foreach ($attendances as $i => $attendance) {
        $attendance->date = $prevMonth->copy()->startOfMonth()->addDays($i);
        $attendance->save();
    }

    $response = $this->actingAs($this->admin)
                     ->get(route('admin.attendance.staff', [
                         'id' => $this->employee->id,
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

    // 翌月の勤怠を3件作成
    $attendances = Attendance::factory()->count(3)->create([
        'user_id' => $this->employee->id,
        'status' => Attendance::STATUS['FINISHED'],
    ]);

    // 日付を翌月の1日、2日、3日に設定
    foreach ($attendances as $i => $attendance) {
        $attendance->date = $nextMonth->copy()->startOfMonth()->addDays($i);
        $attendance->save();
    }

    // 翌月ページにアクセス
    $response = $this->actingAs($this->admin)
                     ->get(route('admin.attendance.staff', [
                         'id' => $this->employee->id,
                         'month' => $nextMonth->format('Y-m'),
                     ]));

    // それぞれの日付が表示されているか確認
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

    // 修正申請を pending 状態で追加（factoryを利用）
    $attendanceRequest = AttendanceRequest::factory()->create([
    'attendance_id' => $attendance->id,
    'user_id' => $this->employee->id,
    'status' => 'pending',
    'request_type' => 'edit',
    'requested_clock_in_time' => '2025-09-02 10:30:00', // 日付付きに変更
    'requested_clock_out_time' => '2025-09-02 19:30:00', // 日付付きに変更
    'reason' => '出勤時刻を間違えたため修正依頼',
]);


    $response = $this->actingAs($this->admin)
        ->get(route('stamp_correction_request.approve_form', $attendanceRequest->id));

    $response->assertStatus(200);
    $response->assertSee('10:30'); // 申請内容が画面に表示されるはず
}


}
