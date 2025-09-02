<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 会員登録後に認証メールが送信される()
    {
        // メール送信をフェイク
        Notification::fake();

        // 会員登録処理（実際のフォーム入力を模擬）
        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録後にユーザーが作成されていること
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        // 認証メールが送信されていること
        Notification::assertSentTo(
            [$user],
            CustomVerifyEmail::class
        );

        // 登録後のリダイレクト確認（必要に応じて）
        $response->assertRedirect('/email/verify');
    
    }

    /** @test */
    public function 認証はこちらからボタンを押すと_mailtrapに遷移するリンクが表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
        // Mailtrap のリンクが表示されているか確認
        $response->assertSee('https://mailtrap.io/inboxes');
    }

    /** @test */
    public function メール認証を完了すると勤怠登録画面に遷移する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/attendance');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
