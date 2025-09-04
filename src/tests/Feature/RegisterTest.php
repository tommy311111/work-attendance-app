<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 共通の登録データ（必要に応じて変更して使う）
        $this->defaultData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    /** @test */
    public function 名前が未入力の場合_バリデーションメッセージが表示される()
    {
        $data = $this->defaultData;
        $data['name'] = '';

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** @test */
    public function メールアドレスが未入力の場合_バリデーションメッセージが表示される()
    {
        $data = $this->defaultData;
        $data['email'] = '';

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    public function パスワードが8文字未満の場合_バリデーションメッセージが表示される()
    {
        $data = $this->defaultData;
        $data['password'] = $data['password_confirmation'] = 'pass';

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** @test */
    public function パスワードが一致しない場合_バリデーションメッセージが表示される()
    {
        $data = $this->defaultData;
        $data['password_confirmation'] = 'different123';

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    /** @test */
    public function パスワードが未入力の場合_バリデーションメッセージが表示される()
    {
        $data = $this->defaultData;
        $data['password'] = $data['password_confirmation'] = '';

        $response = $this->post(route('register'), $data);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function フォームに内容が入力されていた場合_データが正常に保存される()
    {
        $response = $this->post(route('register'), $this->defaultData);

        $response->assertRedirect(route('verification.notice'));

        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);
    }
}
