@extends('layouts.user')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth/verify.css') }}">

<div class="verify__content">
    <h1 class="verify__heading">メール認証</h1>

    <p class="verify__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    @if (session('status') == 'verification-link-sent')
        <p class="verify__alert">認証メールを送信しました。</p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}" class="verify__form">
        @csrf
        <button type="submit" class="verify__button">認証はこちらから</button>
    </form>

    <form method="POST" action="{{ route('verification.send') }}" class="verify__form">
        @csrf
        <button type="submit" class="verify__resend">認証メールを再送する</button>
    </form>
</div>
@endsection
