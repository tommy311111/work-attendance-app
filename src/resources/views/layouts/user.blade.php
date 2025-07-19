<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ユーザー画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/user.css') }}">
    @yield('css')
</head>
<body>

<header class="header">
        <div class="header__inner">
            <div class="header__top">
                <h1 class="header__logo">
                     //勤怠登録へ
                        <img src="{{ asset('storage/images/logo.svg') }}" alt="COACHTECH">
                    </a>
                </h1>

            </div>
            <div class="header__bottom">
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
