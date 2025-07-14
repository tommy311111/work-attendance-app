<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceRequestBreaksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_request_breaks', function (Blueprint $table) {
            $table->id();
            // 外部キー：attendance_requests テーブル
            $table->foreignId('attendance_request_id')
                ->constrained('attendance_requests')
                ->onDelete('cascade');

            // 外部キー：breaks テーブル
            $table->foreignId('break_id')
                ->constrained('breaks')
                ->onDelete('cascade');

            // 修正希望の休憩時間
            $table->timestamp('requested_start_time')->nullable();
            $table->timestamp('requested_end_time')->nullable();
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
        Schema::dropIfExists('attendance_request_breaks');
    }
}
