@extends('layouts.user')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">

<div class="verify__content">

    <p class="verify__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="verify__alert">認証メールを送信しました。</p>
    @endif

    {{-- 認証はこちらから → Mailtrap に遷移 --}}
<a href="https://mailtrap.io/inboxes" target="_blank" class="verify__button">
    認証はこちらから
</a>

    <form method="POST" action="{{ route('verification.send') }}" class="verify__form">
        @csrf
        <button type="submit" class="verify__resend">認証メールを再送する</button>
    </form>

</div>
@endsection
