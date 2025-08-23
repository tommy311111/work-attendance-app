<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\User\UserAttendanceController;
use App\Http\Controllers\User\UserRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\Admin\StaffController;

// ユーザー登録
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// ログイン・ログアウト
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// メール認証
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/attendance'); // 勤怠登録画面へ
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 勤怠登録（認証済ユーザーのみ）
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [UserAttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/action', [UserAttendanceController::class, 'updateStatus'])->name('attendance.action');
});

// 勤怠一覧・詳細・修正申請（認証ユーザー）
Route::middleware(['auth'])->group(function () {
    Route::get('/attendance/list', [UserAttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/{id}', [UserAttendanceController::class, 'show'])->name('attendance.show');
    Route::get('/attendance-requests/{id}/edit', [UserRequestController::class, 'editRequest'])->name('attendance-requests.edit');
    Route::post('/attendance-requests/{id}', [UserRequestController::class, 'storeRequest'])->name('attendance-requests.store');

    // 修正申請一覧（ユーザー・管理者兼用）
    Route::get('/stamp_correction_request/list', [UserRequestController::class, 'index'])->name('attendance_requests.list');
});

// 管理者ログイン
Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);

// 管理者用ルート（認証ユーザー）
Route::middleware(['auth'])->group(function () {
    // 勤怠一覧・詳細
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.index');
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');

    // 勤怠修正申請承認
    Route::patch('/admin/attendance-requests/{id}', [AdminRequestController::class, 'update'])->name('admin.attendance-requests.update');

    // スタッフ一覧
    Route::get('/admin/staff/list', [StaffController::class, 'index'])->name('admin.staff.list');

    // スタッフ個別勤怠・CSV
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffAttendance'])->name('admin.attendance.staff');
    Route::get('/admin/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'exportCsv'])->name('admin.attendance.staff.csv');

    // 承認画面表示
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminRequestController::class, 'approveForm'])->name('stamp_correction_request.approve_form');

    // 承認処理
    Route::put('/stamp_correction_request/approve/{attendance_correct_request}', [AdminRequestController::class, 'approve'])->name('stamp_correction_request.approve');
});

// 修正申請一覧（ユーザー・管理者兼用）
Route::middleware(['auth'])->group(function () {
    Route::get('/stamp_correction_request/list', function () {
        if (auth()->user()->role === 'admin') {
            return app(\App\Http\Controllers\Admin\AdminRequestController::class)->index();
        } else {
            return app(\App\Http\Controllers\User\UserRequestController::class)->index();
        }
    })->name('attendance_requests.list');
});
