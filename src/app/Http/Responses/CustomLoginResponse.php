<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        if ($user->role === 'admin') {
            return redirect('/admin/attendance/list');
        }

        return redirect('/attendance');
    }
}
