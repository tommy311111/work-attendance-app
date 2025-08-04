<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\User\UserAttendanceController;
use App\Http\Controllers\User\UserRequestController;

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance'); //勤怠登録
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/action', [UserAttendanceController::class, 'updateStatus'])->name('attendance.action');
});

Route::middleware(['auth'])->group(function () {
    // 勤怠一覧・詳細
    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{id}', [UserAttendanceController::class, 'show'])->name('attendance.show');

    // 勤怠修正申請
    Route::get('/attendance-requests/{id}/edit', [UserRequestController::class, 'editRequest'])->name('attendance-requests.edit');
    Route::post('/attendance-requests/{id}', [UserRequestController::class, 'storeRequest'])->name('attendance-requests.store');

    //修正申請一覧
    Route::get('/stamp_correction_request/list', [UserRequestController::class, 'index'])->name('attendance_requests.index');
});


use App\Http\Controllers\Admin\AdminAttendanceController;

//管理者
Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.index');

    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance-requests/{id}/edit', [AdminAttendanceRequestController::class, 'edit'])->name('attendance-requests.edit');
});