<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
{
    $users = User::where('role', 'employee')
                 ->with('attendanceRequests') // ← リクエストも一緒に取得
                 ->get();

    return view('admin.staff.index', compact('users'));
}

}
