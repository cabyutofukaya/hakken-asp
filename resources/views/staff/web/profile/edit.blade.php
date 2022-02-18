@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">badge</span>プロフィール管理
    </h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.front.profile.update', [$agencyAccount]) }}">HAKKEN WEBページ管理</a></li>
      <li><span>プロフィール管理</span></li>
    </ol>
    <div class="deleteControl wd15">
      <button class="blueBtn" onclick="window.open().location.href='{{ get_webprofile_previewurl(\Hashids::encode($webProfile->staff->id)) }}'">プレビュー</button>
    </div>

  </div>
  
  <h2 class="subTit">
    <span class="material-icons">person</span>プロフィール基本情報
  </h2>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.front.profile.update', [$agencyAccount]) }}">
    @csrf
    <div id="inputArea"
    consts='@json($consts)'
    jsVars='@json($jsVars)' 
    defaultValue='@json($defaultValue)' 
    formSelects='@json($formSelects)'
    ></div>
    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>
  </form>
</main>

<script src="{{ mix('/staff/js/web_profile-create-edit.js') }}"></script>
@endsection
