<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 申請者
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade'); // 対象の勤怠記録

            $table->string('request_type', 20); // 出勤 or 退勤 or 備考など
            $table->timestamp('requested_clock_in_time')->nullable();  // 修正後の出勤時間（nullableにして柔軟に）
            $table->timestamp('requested_clock_out_time')->nullable(); // 修正後の退勤時間（nullableにして柔軟に）
            $table->text('reason'); // 修正理由

            $table->enum('status', ['pending', 'approved'])->default('pending'); // ステータス（enum風）
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // 管理者による承認者（nullable）
            $table->timestamp('reviewed_at')->nullable(); // 承認日時
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attendance_requests');
    }
}
