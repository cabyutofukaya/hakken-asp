@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">person</span>ユーザー編集
    </h1>

    @if(!$staff->master) {{-- マスター権限はステータスの変更、および削除不可 --}}
      @can('delete', $staff)
        <div class="acountControl" id="acountControl" 
          agencyAccount='{{$agencyAccount}}' 
          staffAccount='{{$staffAccount}}' 
          defaultValue='@json($defaultValue)' 
          formSelects='@json($formSelects)'
        ></div>
      @endcan
    @endif

    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.user.index', [$agencyAccount]) }}">ユーザー管理</a></li>
      <li><span>ユーザー編集</span></li>
    </ol>
  </div>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.system.user.update', [$agencyAccount, $staff->account]) }}" id="staffForm">
    @method('PUT')
    @csrf

    {{-- ↓同時編集チェックする場合はコメントアウト解除する --}}
    {{-- <input type="hidden" name="updated_at" value="{{ $staff->updated_at }}"/> --}}
    <div id="inputArea" 
      agencyAccount='{{$agencyAccount}}' 
      defaultValue='@json($defaultValue)' 
      formSelects='@json($formSelects)' 
      customCategoryCode='{{$customCategoryCode}}' 
      userCustomItemTypes='@json($userCustomItemTypes)'
      isMaster='{{$staff->master}}'
      ></div>

    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('staff.system.user.index', [$agencyAccount]) }}'">
          <span class="material-icons">arrow_back_ios</span>保存せずに戻る
        </button>
      </li>
      @can('update', $staff)
        <li class="wd50">
          <button class="blueBtn doubleBan">
            <span class="material-icons">save</span> この内容で更新する
          </button>
        </li>
      @endcan
    </ul>
  </form>
</main>

<script>
$(function(){
  // 自身のパスワードを変更すると一度ログアウトするので注意を促す
  if('{{ $staff->account }}' == '{{ auth('staff')->user()->account }}'){
    $('#staffForm').submit(function(){
    if($('[name=password]').val() && $('[name=password_confirmation]').val()){
      if(confirm("ご自身のパスワードを変更すると一旦ログアウトします。\nよろしいですか?")){
          return true;
        }else{
          $("form .doubleBan").css("pointer-events", "auto"); // 二重送信防止class除去
          return false;
        }
      }
      return true;
    });
  }
});
</script>
<script src="{{ mix('/staff/js/staff-edit.js') }}"></script>
@endsection
