<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠打刻画面に現在の日付・曜日・時計要素が表示される()
    {
        // 1. ユーザー作成
        $user = User::factory()->create();

        // 2. テスト用に現在日時を固定
        Carbon::setTestNow(Carbon::create(2025, 8, 27, 14, 30));

        // 3. ユーザーでログインして勤怠打刻画面にアクセス
        $response = $this->actingAs($user)
                         ->get('/attendance'); // 勤怠画面のルート

        // 4. HTTPステータス確認
        $response->assertStatus(200);

        // 5. Bladeで生成される日付と曜日が正しく表示されているか確認
        $response->assertSee('2025年8月27日');
        $response->assertSee('水'); // $weekdayJapanese の想定

        // 6. JSで更新される時計領域が存在することを確認
        $response->assertSee('id="clock"', false);

    }
}
