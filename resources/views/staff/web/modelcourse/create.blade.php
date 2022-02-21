@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">description</span>新規モデルコース作成</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.front.modelcourse.index', [$agencyAccount]) }}">モデルコース管理</a></li>
      <li><span>新規モデルコース作成</span></li>
    </ol>
  </div>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <h2 class="subTit"><span class="material-icons"> subject </span>基本情報</h2>

  <form method="post" action="{{ route('staff.front.modelcourse.store', [$agencyAccount]) }}">
    @csrf
    <div id="modelCourseInputArea"
      jsVars='@json($jsVars)' 
      defaultValue='@json($defaultValue)'
      consts='@json($consts)'
      formSelects='@json($formSelects)'
    ></div>
    
    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn doubleBan"><span class="material-icons">save</span>この内容で登録する</button></li>
    </ul>
  </form>
</main>

<script src="{{ mix('/staff/js/web_modelcourse-create-edit.js') }}"></script>
@endsection
