<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
public function 出勤ボタンが表示され押すとステータスが出勤中になる()
{
    // 時刻固定
    Carbon::setTestNow(Carbon::create(2025, 8, 27, 9, 0));

    // ユーザー作成
    $user = User::factory()->create();

    $today = Carbon::today();

    // 勤怠画面にアクセス（この時点でOFF_DUTYのレコードが自動作成される）
    $response = $this->actingAs($user)->get('/attendance');
    $response->assertStatus(200);
    $response->assertSee('出勤');

    // OFF_DUTYでレコードがあることを確認
    $this->assertDatabaseHas('attendances', [
        'user_id' => $user->id,
        'date'    => $today->toDateString(),
        'status'  => Attendance::STATUS['OFF_DUTY'],
    ]);

    // 出勤処理
    $this->actingAs($user)->post('/attendance/action', [
        'action' => 'start_work',
    ]);

    // 出勤後はON_DUTYに更新されていることを確認
    $this->assertDatabaseHas('attendances', [
        'user_id' => $user->id,
        'date'    => $today->toDateString(),
        'status'  => Attendance::STATUS['WORKING'],
    ]);

    // 出勤後の画面に「出勤中」と表示されていることを確認
    $response = $this->actingAs($user)->get('/attendance');
    $response->assertStatus(200);
    $response->assertSee('出勤中');
}



    /** @test */
    public function 退勤済の場合は出勤ボタンが表示されない()
    {
        Carbon::setTestNow(Carbon::create(2025, 8, 27, 9, 0));

        $user = User::factory()->create();

        // 勤怠レコード作成（退勤済）
        $today = Carbon::today();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date'    => $today,
            'status'  => Attendance::STATUS['FINISHED'],
            'clock_in' => now()->subHours(8),
            'clock_out'=> now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    /** @test */

    public function 出勤時刻が勤怠一覧で確認できる()
    {
        // 1. テストユーザー作成
        $user = User::factory()->create();

        // 2. 当日の勤務外勤怠レコードを作成（clock_in は null）
        $today = Carbon::today();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $today,
            'status' => Attendance::STATUS['OFF_DUTY'],
            'clock_in' => null,
            'clock_out' => null,
        ]);

        // 3. ユーザーでログイン
        $this->actingAs($user);

        // 4. 出勤処理を POST で実行
        $response = $this->post(route('attendance.action'), [
            'action' => 'start_work',
        ]);

        $response->assertRedirect(route('attendance.create'));

        // 5. 勤怠一覧画面を取得
        $listResponse = $this->get(route('attendance.index'));

        // 6. 出勤時刻が表示されていることを確認
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $expectedClockIn = $attendance->clock_in->format('H:i');

        $listResponse->assertSee($expectedClockIn);

        // 7. ステータスも「出勤中」に更新されていることを確認
        $this->assertEquals(Attendance::STATUS['WORKING'], $attendance->status);
    }

}
