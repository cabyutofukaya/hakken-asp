@extends('layouts.admin.app')

@section('content')
<div id="login">
    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <div id="loginBox">
            <img src="{{ asset('/admin/img/shared/logo.svg') }}" alt="HAKKEN" width="168" height="36" />

            @include('admin.common.error_message')

            <ul>
                <li>
                    <span>ログインID</span>
                    <input type="text" name="email" value="{{ old('email') }}" />
                </li>
                <li>
                    <span>パスワード</span>
                    <input type="password" name="password" />
                </li>
            </ul>
            <button class="blueBtn">ログイン</button>
        </div>	
    </form>
</div>
@endsection
