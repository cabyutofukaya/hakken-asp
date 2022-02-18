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

  <form method="post" action="{{ route('staff.asp.estimates.reserve.update', [$agencyAccount, $reserve->control_number]) }}" id="editForm">
    @csrf
    @method('PUT')
    
    <div id="reserveEditArea"
      applicationStep='{{ $applicationStep }}'
      defaultValue='@json($defaultValue)'
      userAddModalDefaultValue='@json($userAddModalDefaultValue)'
      formSelects='@json($formSelects)'
      consts='@json($consts)'
      customFields='@json($customFields)'
      customCategoryCode='{{ $customCategoryCode }}'
      jsVars='@json($jsVars)'
    ></div>

    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button>
      </li>
      <li class="wd50">
        <button class="blueBtn">
          <span class="material-icons">save</span> この内容で更新する
        </button>
      </li>
    </ul>

  </form>

</main>
<script>
@include("staff.common._check_return_date_js")
</script>
<script src="{{ mix('/staff/js/reserve-edit.js') }}"></script>
@endsection