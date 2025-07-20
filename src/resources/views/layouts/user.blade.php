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
        
                <h1 class="header__logo">
                    <a href="{{ route('attendance.show') }}">
                        <img src="{{ asset('storage/images/logo.svg') }}" alt="COACHTECH">
                    </a>
                </h1>

            <nav class="header-nav">
    <ul class="header-nav-list">
      <li class="header-nav-item"><a href="">勤怠</a></li>
      <li class="header-nav-item"><a href="">勤怠一覧</a></li>
      <li class="header-nav-item"><a href="">申請</a></li>
      <li class="header-nav-item">
    <form action="/logout" method="POST" class="logout-form">
        @csrf
        <button type="submit" class="logout-button">ログアウト</button>
    </form>
</li>

    </ul>
  </nav>


       
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
