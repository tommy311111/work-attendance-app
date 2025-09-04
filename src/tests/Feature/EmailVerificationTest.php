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
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            [$user],
            CustomVerifyEmail::class
        );

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function 認証はこちらからボタンを押すと_mailtrapに遷移するリンクが表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);
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
