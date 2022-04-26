@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>見積基本情報{{$reserve->estimate_number}} 編集</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.web.estimates.normal.index', [$agencyAccount]) }}">WEB見積管理</a></li>
      <li><span>見積基本情報編集</span></li>
    </ol>
  </div>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <h2 class="subTit">
    <span class="material-icons"> subject </span>基本情報
  </h2>

    <div id="estimateEditArea"
      applicationStep='{{ $applicationStep }}'
      defaultValue='@json($defaultValue)'
      applicant='@json($applicant)'
      formSelects='@json($formSelects)'
      consts='@json($consts)'
      customFields='@json($customFields)'
      customCategoryCode='{{ $customCategoryCode }}'
      jsVars='@json($jsVars)'
    ></div>

</main>
<script src="{{ mix('/staff/js/web-estimate-edit.js') }}"></script>
@endsection