<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        // 出勤中の勤怠データを作成
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'status' => Attendance::STATUS['WORKING'],
            'clock_in' => now()->subHours(2),
        ]);

        // 出勤中画面にアクセスして「退勤」ボタンが表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        // 退勤処理を実行
        $this->actingAs($user)->post(route('attendance.action'), ['action' => 'end_work']);

        // 再度画面にアクセスして「退勤済」が表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        $today = Carbon::today();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today,
            'status' => Attendance::STATUS['OFF_DUTY'],
            'clock_in' => null,
            'clock_out' => null,
        ]);

        $this->actingAs($user);

        // 出勤→退勤処理を実行
        $this->post(route('attendance.action'), ['action' => 'start_work']);
        $this->post(route('attendance.action'), ['action' => 'end_work']);

        // 勤怠一覧画面を取得
        $listResponse = $this->get(route('attendance.index'));

        // DBから退勤時刻を取得して表示確認
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $expectedClockOut = $attendance->clock_out->format('H:i');
        $listResponse->assertSee($expectedClockOut);

        // ステータスも退勤済みになっていることを確認
        $this->assertEquals(Attendance::STATUS['FINISHED'], $attendance->status);
    }
}
