<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $employee1;
    protected $employee2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->employee1 = User::factory()->create(['name' => '山田太郎']);
        $this->employee2 = User::factory()->create(['name' => '鈴木花子']);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        $attendance1 = Attendance::factory()->create(['user_id' => $this->employee1->id, 'date' => '2025-09-01']);
        $attendance2 = Attendance::factory()->create(['user_id' => $this->employee2->id, 'date' => '2025-09-02']);

        AttendanceRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $this->employee1->id,
            'status' => 'pending',
            'request_type' => 'edit',
            'reason' => '出勤時刻修正',
        ]);
        AttendanceRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $this->employee2->id,
            'status' => 'pending',
            'request_type' => 'edit',
            'reason' => '退勤時刻修正',
        ]);

        $response = $this->actingAs($this->admin)
                         ->get(route('attendance_requests.list', ['status' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('鈴木花子');
        $response->assertSee('承認待ち');
        $response->assertSee('2025/09/01');
        $response->assertSee('2025/09/02');
        $response->assertSee('出勤時刻修正');
        $response->assertSee('退勤時刻修正');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        $attendance1 = Attendance::factory()->create(['user_id' => $this->employee1->id, 'date' => '2025-09-01']);
        $attendance2 = Attendance::factory()->create(['user_id' => $this->employee2->id, 'date' => '2025-09-02']);

        AttendanceRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $this->employee1->id,
            'status' => 'approved',
            'request_type' => 'edit',
            'reason' => '出勤時刻修正',
        ]);
        AttendanceRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'user_id' => $this->employee2->id,
            'status' => 'approved',
            'request_type' => 'edit',
            'reason' => '退勤時刻修正',
        ]);

        $response = $this->actingAs($this->admin)
                         ->get(route('attendance_requests.list', ['status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('鈴木花子');
        $response->assertSee('承認済み');
        $response->assertSee('2025/09/01');
        $response->assertSee('2025/09/02');
        $response->assertSee('出勤時刻修正');
        $response->assertSee('退勤時刻修正');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示される()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => '2025-09-01',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->employee1->id,
            'status' => 'pending',
            'request_type' => 'edit',
            'requested_clock_in_time' => '2025-09-01 10:00:00',
            'requested_clock_out_time' => '2025-09-01 19:00:00',
            'reason' => '出勤時刻修正',
        ]);

        $response = $this->actingAs($this->admin)
                         ->get(route('stamp_correction_request.approve_form', $request->id));

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('出勤時刻修正');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->employee1->id,
            'date' => '2025-09-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $this->employee1->id,
            'status' => 'pending',
            'request_type' => 'edit',
            'requested_clock_in_time' => '2025-09-01 10:00:00',
            'requested_clock_out_time' => '2025-09-01 19:00:00',
            'reason' => '出勤時刻修正',
        ]);

        $response = $this->actingAs($this->admin)
                         ->put(route('stamp_correction_request.approve', $request->id));

        $response->assertRedirect(route('attendance_requests.list', ['status' => 'pending']));

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '2025-09-01 10:00:00',
            'clock_out' => '2025-09-01 19:00:00',
        ]);
    }
}
