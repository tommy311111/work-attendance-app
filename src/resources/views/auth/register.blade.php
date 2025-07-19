@extends('layouts.user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<div class="register__content">
    <header class="register-form__heading">
        <h1 class="register-form__heading-title">会員登録</h1>
    </header>

    <form class="register-form" action="{{ route('register') }}" method="post" novalidate>
        @csrf

        <section class="form__group">
            <h2 class="form__group-title">
                <span class="form__label--item">名前</span>
            </h2>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="name" value="{{ old('name') }}">
                </div>
                <div class="form__error">
                    @error('name')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </section>

        <section class="form__group">
            <h2 class="form__group-title">
                <span class="form__label--item">メールアドレス</span>
            </h2>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
                <div class="form__error">
                    @error('email')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </section>

        <section class="form__group">
            <h2 class="form__group-title">
                <span class="form__label--item">パスワード</span>
            </h2>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="password" name="password">
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </section>

        <section class="form__group">
            <h2 class="form__group-title">
                <span class="form__label--item">パスワード確認</span>
            </h2>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="password" name="password_confirmation">
                </div>
                <div class="form__error">
                    @error('password')
                    {{ $message }}
                    @enderror
                </div>
            </div>
        </section>

        <div class="form__button">
            <button class="form__button-submit" type="submit">登録する</button>
        </div>
    </form>

    <div class="login__link">
        <a class="login__button-submit" href="/login">ログインはこちら</a>
    </div>
</div>
@endsection
