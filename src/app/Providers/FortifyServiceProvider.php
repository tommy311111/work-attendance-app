<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Responses\CustomLoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
{
    $this->app->singleton(LogoutResponse::class, function () {
        return new class implements LogoutResponse {
            public function toResponse($request)
            {
                // ログアウト後にリダイレクトしたいURL
                return redirect('/login');
            }
        };
    });
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register'); // register.blade.php を表示
        });

        // ログイン画面の切り替え
    Fortify::loginView(function () {
        if (request()->is('admin/*')) {
            return view('admin.login'); // 管理者用Blade
        }
        return view('auth.login'); // ユーザー用Blade
    });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

    }
}
