@extends('layouts.staff.app')

@section('content')

<main>
  <div id="pageHead">
    <h1><span class="material-icons">person_add</span>ユーザー追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.user.index', [$agencyAccount]) }}">ユーザー管理</a></li>
      <li><span>ユーザー追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.system.user.store', [$agencyAccount]) }}">
    @csrf
    <div id="inputArea" agencyAccount='{{$agencyAccount}}' defaultValue='@json($defaultValue)' formSelects='@json($formSelects)' customCategoryCode='{{$customCategoryCode}}' userCustomItemTypes='@json($userCustomItemTypes)'></div>

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>
  </form>
</main>

<script src="{{ mix('/staff/js/staff-create.js') }}"></script>
@endsection
