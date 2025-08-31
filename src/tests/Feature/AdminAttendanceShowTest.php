<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // 管理者ユーザー作成
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->adminUser->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);
    }

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        // 動作：管理者ユーザーで詳細ページを開く
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.attendance.show', $this->attendance->id));

        $response->assertStatus(200);

        // 期待挙動：出勤・退勤が表示される
        $response->assertSee('09:00');
        $response->assertSee('18:00');

    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーになる()
    {
        $response = $this->actingAs($this->adminUser)
            ->patch(route('admin.attendance-requests.update', $this->attendance->id), [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'reason' => '修正テスト',
            ]);

        // 期待挙動：出勤時間・退勤時間のバリデーションメッセージが表示される
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーになる()
    {
        $response = $this->actingAs($this->adminUser)
            ->patch(route('admin.attendance-requests.update', $this->attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '19:00', 'break_end' => '19:30'],
                ],
                'reason' => '修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーになる()
    {
        $response = $this->actingAs($this->adminUser)
            ->patch(route('admin.attendance-requests.update', $this->attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['break_start' => '17:00', 'break_end' => '19:00'],
                ],
                'reason' => '修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーになる()
    {
        $response = $this->actingAs($this->adminUser)
            ->patch(route('admin.attendance-requests.update', $this->attendance->id), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [],
                'reason' => '',
            ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください。',
        ]);
    }
}
