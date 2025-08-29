<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 修正申請が実行され管理者画面に表示される()
    {
        // 一般ユーザーと管理者ユーザーを作成
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);

        // 勤怠情報を作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'date' => Carbon::parse('2025-08-29'),
            'status' => Attendance::STATUS['FINISHED'],
        ]);

        // 修正申請用の時間（timestamp対応）
        $requestedClockIn = Carbon::parse($attendance->date->format('Y-m-d') . ' 09:30');
        $requestedClockOut = Carbon::parse($attendance->date->format('Y-m-d') . ' 18:30');

        // 一般ユーザーとして修正申請を送信
        $response = $this->actingAs($user)
            ->post(route('attendance-requests.store', $attendance->id), [
                'clock_in' => $requestedClockIn->format('H:i'),
                'clock_out' => $requestedClockOut->format('H:i'),
                'breaks' => [],
                'reason' => '打刻修正テスト',
            ]);

        $response->assertStatus(302); // リダイレクト確認

        // 修正申請がDBに保存されているか確認
        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_time' => $requestedClockIn->toDateTimeString(),
            'reason' => '打刻修正テスト',
            'status' => 'pending',
        ]);

        $attendanceRequest = AttendanceRequest::first();

        // 管理者として申請承認画面にアクセスできるか確認
        $this->actingAs($admin)
     ->get(route('stamp_correction_request.approve_form', $attendanceRequest->id))
     ->assertStatus(200)
     ->assertSee(str_replace(' ', '　', $user->name)) // 半角を全角に置換してチェック
     ->assertSee($requestedClockIn->format('H:i'))
     ->assertSee($requestedClockOut->format('H:i'));


        // 管理者の申請一覧画面に「承認待ち」として表示されるか確認
        $this->actingAs($admin)
             ->get(route('attendance_requests.list', ['status' => 'pending']))
             ->assertStatus(200)
             ->assertSee($user->name)
             ->assertSee('承認待ち')
             ->assertSee($attendance->date->format('Y/m/d'));
    }
}
