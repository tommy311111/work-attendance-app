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
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $this->post(route('attendance.action'), ['action' => 'start_break']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->post(route('attendance.action'), ['action' => 'end_break']);
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->post(route('attendance.action'), ['action' => 'end_break']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $this->post(route('attendance.action'), ['action' => 'start_break']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post(route('attendance.action'), ['action' => 'end_break']);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->post(route('attendance.action'), ['action' => 'end_break']);
        $this->post(route('attendance.action'), ['action' => 'start_break']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $this->post(route('attendance.action'), ['action' => 'start_break']);
        $this->travel(30)->minutes();
        $this->post(route('attendance.action'), ['action' => 'end_break']);

        $response = $this->get(route('attendance.index'));
        $response->assertSee('0:30');
    }
}
