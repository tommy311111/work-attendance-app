<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceRequestListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちに自分の申請が表示される()
    {
        // ユーザーと勤怠を作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 勤怠詳細を修正して修正申請を保存する（postリクエスト）
        $request1 = $this->post(route('attendance-requests.store', $attendance->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'breaks' => [],
            'reason' => '修正理由1',
        ]);

        $request2 = $this->post(route('attendance-requests.store', $attendance->id), [
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'breaks' => [],
            'reason' => '修正理由2',
        ]);

        // 申請一覧画面にアクセス（承認待ちタブ）
        $response = $this->get(route('attendance_requests.list', ['status' => 'pending']));

        $response->assertStatus(200)
                 ->assertSee('承認待ち')
                 ->assertSee('修正理由1')
                 ->assertSee('修正理由2');
    }

        /** @test */
public function 承認済みに管理者が承認した申請が表示される()
{
    // 1. 勤怠情報が登録されたユーザーを作成
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    // 2. 勤怠詳細を修正し申請を保存（ユーザー側）
    $request = AttendanceRequest::factory()->create([
        'attendance_id' => $attendance->id,
        'user_id' => $user->id,
        'status' => 'pending',
        'reason' => '承認理由',
    ]);

    // 3. 管理者ユーザーで承認
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)
         ->put(route('stamp_correction_request.approve', $request->id), [
             '_method' => 'PUT',
         ]);

    // 4. ユーザーで申請一覧画面を開く
    $response = $this->actingAs($user)
                     ->get(route('attendance_requests.list', ['status' => 'approved']));

    // 期待挙動：承認済みに管理者が承認した申請が全て表示されている
    $response->assertStatus(200)
             ->assertSee('承認済み')
             ->assertSee('承認理由');
}


/** @test */
public function 承認待ちタブで自分の申請が表示され詳細画面に遷移できる()
{
    // ユーザーと勤怠情報作成
    $user = User::factory()->create();
    $attendance = Attendance::factory()->create(['user_id' => $user->id]);

    // 勤怠詳細を修正して修正申請を保存する（POSTリクエスト）
    $responsePost = $this->actingAs($user)
                         ->post(route('attendance-requests.store', $attendance->id), [
                             'clock_in' => '09:30',
                             'clock_out' => '18:30',
                             'breaks' => [],
                             'reason' => '詳細確認用',
                         ]);

    $responsePost->assertStatus(302); // リダイレクトで保存成功

    $request = AttendanceRequest::where('attendance_id', $attendance->id)
                                ->where('user_id', $user->id)
                                ->first();

    $this->assertNotNull($request);

    // 申請一覧画面にアクセス（承認待ちタブ）
    $responseList = $this->actingAs($user)
                         ->get(route('attendance_requests.list', ['status' => 'pending']));

    $responseList->assertStatus(200)
                 ->assertSee('承認待ち')
                 ->assertSee('詳細確認用');

    // 詳細ボタンを押して編集画面に遷移
    $responseDetail = $this->actingAs($user)
                           ->get(route('attendance-requests.edit', $request->id));

    $responseDetail->assertStatus(200)
                   ->assertSee('詳細確認用');
}

}
