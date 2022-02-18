@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>新規見積作成</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route("staff.asp.estimates.normal.index", $agencyAccount) }}">見積管理</a></li>
      <li><span>新規見積作成</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <h2 class="subTit"><span class="material-icons"> subject </span>基本情報</h2>

  <form method="post" action="{{ route('staff.asp.estimates.normal.store', $agencyAccount) }}">
    @csrf
    <div id="estimateInputArea"
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
      <button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button>
    </li>
    <li class="wd50">
      <button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button>
    </li>
  </ul>
</main>

<script src="{{ mix('/staff/js/estimate-create.js') }}"></script>
@endsection
