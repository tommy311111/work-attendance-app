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
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance-requests.store', $attendance->id), [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'breaks' => [],
            'reason' => '修正理由1',
        ]);

        $this->post(route('attendance-requests.store', $attendance->id), [
            'clock_in' => '09:15',
            'clock_out' => '18:15',
            'breaks' => [],
            'reason' => '修正理由2',
        ]);

        $response = $this->get(route('attendance_requests.list', ['status' => 'pending']));

        $response->assertStatus(200)
                 ->assertSee('承認待ち')
                 ->assertSee('修正理由1')
                 ->assertSee('修正理由2');
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $request1 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => '承認理由1',
        ]);

        $request2 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => '承認理由2',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
             ->put(route('stamp_correction_request.approve', $request1->id), ['_method' => 'PUT']);

        $this->actingAs($admin)
             ->put(route('stamp_correction_request.approve', $request2->id), ['_method' => 'PUT']);

        $response = $this->actingAs($user)
                         ->get(route('attendance_requests.list', ['status' => 'approved']));

        $response->assertStatus(200)
                 ->assertSee('承認済み')
                 ->assertSee('承認理由1')
                 ->assertSee('承認理由2');
    }

    /** @test */
    public function 各申請の「詳細」を押下すると申請詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
             ->post(route('attendance-requests.store', $attendance->id), [
                 'clock_in' => '09:30',
                 'clock_out' => '18:30',
                 'breaks' => [],
                 'reason' => '詳細確認用',
             ])->assertStatus(302);

        $request = AttendanceRequest::where('attendance_id', $attendance->id)
                                    ->where('user_id', $user->id)
                                    ->first();

        $this->assertNotNull($request);

        $responseList = $this->actingAs($user)
                             ->get(route('attendance_requests.list', ['status' => 'pending']));

        $responseList->assertStatus(200)
                     ->assertSee('承認待ち')
                     ->assertSee('詳細確認用');

        $responseDetail = $this->actingAs($user)
                               ->get(route('attendance-requests.edit', $request->id));

        $responseDetail->assertStatus(200)
                       ->assertSee('詳細確認用');
    }
}
