@extends('layouts.staff.app')

@section('content')
<div id="login">
    <form method="POST" action="{{ route('staff.login', $agencyAccount) }}">
        @csrf
        <input type="hidden" name="agency_id" value="{{$agencyId}}"/>
        <div id="loginBox">
            <img src="{{ asset('/staff/img/shared/logo.svg') }}" alt="HAKKEN" width="168" height="36" />

            @include('staff.common.error_message')

            <ul>
                <li>
                    <span>ログインID</span>
                    <input type="text" name="account" value="{{ old('account') }}" />
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
