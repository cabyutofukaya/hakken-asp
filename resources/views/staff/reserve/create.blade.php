@extends('layouts.staff.app')

@section('content')

<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>新規予約作成</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route("staff.asp.estimates.reserve.index", $agencyAccount) }}">予約管理</a></li>
      <li><span>新規予約作成</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <h2 class="subTit"><span class="material-icons"> subject </span>基本情報</h2>

    <div id="reserveInputArea"
      defaultValue='@json($defaultValue)'
      userAddModalDefaultValue='@json($userAddModalDefaultValue)'
      formSelects='@json($formSelects)'
      consts='@json($consts)'
      customFields='@json($customFields)'
      customCategoryCode='{{ $customCategoryCode }}'
      flashMessage='@json($flashMessage)'
      jsVars='@json($jsVars)'
    ></div>

</main>

<script src="{{ mix('/staff/js/reserve-create.js') }}"></script>
@endsection
