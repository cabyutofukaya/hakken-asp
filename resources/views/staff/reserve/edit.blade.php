@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>予約基本情報{{$reserve->control_number}} 編集</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route("staff.asp.estimates.reserve.index", $agencyAccount) }}">予約管理</a></li>
      <li><span>予約基本情報編集</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <h2 class="subTit">
    <span class="material-icons"> subject </span>基本情報
  </h2>

    <div id="reserveEditArea"
      isCanceled='{{ $isCanceled }}'
      applicationStep='{{ $applicationStep }}'
      defaultValue='@json($defaultValue)'
      userAddModalDefaultValue='@json($userAddModalDefaultValue)'
      formSelects='@json($formSelects)'
      consts='@json($consts)'
      customFields='@json($customFields)'
      customCategoryCode='{{ $customCategoryCode }}'
      jsVars='@json($jsVars)'
    ></div>

</main>
<script src="{{ mix('/staff/js/reserve-edit.js') }}"></script>
@endsection