<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // 出勤中の状態を作成
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'status' => Attendance::STATUS['WORKING'],
            'clock_in' => now()->subHours(2),
        ]);
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        // 出勤中画面にアクセスして「休憩入」ボタンを確認
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        // 休憩開始
        $this->post(route('attendance.action'), ['action' => 'start_break']);

        // 最新画面を取得してステータス「休憩中」を確認
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        // 1回目休憩入 & 戻り
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->post(route('attendance.action'), ['action' => 'end_break']);

        // 2回目休憩入 & 戻り
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->post(route('attendance.action'), ['action' => 'end_break']);

        // ステータスが「出勤中」に戻った状態で画面を取得
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        // 「休憩入ボタン」が表示されていることを確認
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        // 休憩開始
        $this->post(route('attendance.action'), ['action' => 'start_break']);

        // 「休憩戻ボタン」が表示されているか確認
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        // 休憩終了
        $this->post(route('attendance.action'), ['action' => 'end_break']);

        // ステータス「出勤中」が表示されているか確認
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        // 1回目休憩入 & 戻り
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->post(route('attendance.action'), ['action' => 'end_break']);

        // 2回目休憩入
        $this->post(route('attendance.action'), ['action' => 'start_break']);

        // ステータスが「休憩中」に戻った状態で画面を取得
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        // 「休憩戻ボタン」が表示されていることを確認
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        // 休憩入 & 戻り
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->travel(30)->minutes();
        $this->post(route('attendance.action'), ['action' => 'end_break']);

        // 勤怠一覧画面を取得
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);

        // Bladeのフォーマットに合わせて「0:30」で確認
        $response->assertSee('0:30');
    }
}
