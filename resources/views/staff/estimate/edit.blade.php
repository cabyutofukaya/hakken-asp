@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>見積基本情報{{$reserve->estimate_number}} 編集</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route("staff.asp.estimates.normal.index", $agencyAccount) }}">見積管理</a></li>
      <li><span>見積基本情報編集</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <h2 class="subTit">
    <span class="material-icons"> subject </span>基本情報
  </h2>

  <form method="post" action="{{ route('staff.asp.estimates.normal.update', [$agencyAccount, $reserve->estimate_number]) }}">
    @csrf
    @method('PUT')
    
    <div id="estimateEditArea"
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
        <button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で更新する</button>
      </li>
    </ul>

  </form>

</main>
<script src="{{ mix('/staff/js/estimate-edit.js') }}"></script>
@endsection
