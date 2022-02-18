@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">description</span>モデルコース管理</h1>

    @can('create', [new App\Models\WebModelcourse, $agencyAccount])
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.front.modelcourse.create', $agencyAccount) }}'" class="addBtn"><span class="material-icons">description</span>新規作成</button>
      </div>
    @endcan

    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.front.modelcourse.index', $agencyAccount) }}">HAKKEN WEBページ管理</a></li>
      <li><span>モデルコース管理</span></li>
    </ol>
  </div>
  
  @include('staff.web.common._check_web_valid')
  
  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <div class="tableWrap dragTable" id="modelcourseList"
    myId='{{ $myId }}'
    jsVars='@json($jsVars)' 
  ></div>
</main>

<script src="{{ mix('/staff/js/web_modelcourse-index.js') }}"></script>
@endsection
