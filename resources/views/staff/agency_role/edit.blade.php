@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">manage_accounts</span>ユーザー権限編集
    </h1>
    @if(!$agencyRole->master) {{-- マスター権限は削除不可 --}}
      @can('delete', $agencyRole)
        <div class="deleteControl">
          <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
        </div>
      @endcan
    @endif

    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.role.index', $agencyAccount) }}">ユーザー権限</a></li>
      <li><span>ユーザー権限編集</span></li>
    </ol>
  </div>

  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.system.role.update', [$agencyAccount,$agencyRole->id]) }}">
    @method('PUT')
    @csrf

    <div id="inputArea">
      @include('staff.agency_role.form.edit')
    </div>

    <div id="staffIndex" agencyAccount='{{$agencyAccount}}' title='該当ユーザー 一覧' formSelects='@json($formSelects)' searchParam='@json($searchParam)'></div>{{-- 当該ユーザー一覧コンポーネント --}}

    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button>
      </li>
      @if(!$agencyRole->master) {{-- マスター権限は変更不可 --}}
        @can('update', $agencyRole)
          <li class="wd50">
            <button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で更新する</button>
          </li>
        @endcan
      @endif
    </ul>
  </form>
</main>

<!-- 削除モーダル// -->
<div id="mdDelete" class="modal js-modal">
  <div class="modal__bg js-modal-close"></div>
  <div class="modal__content">
    <p class="mdTit mb20">この権限を削除しますか？</p>
    <ul class="sideList">
      <li class="wd50"><button class="grayBtn js-modal-close">キャンセル</button></li>
      <li class="wd50 mr00">
        <form method="post" action="{{ route('staff.system.role.destroy', [$agencyAccount, $agencyRole->id]) }}">
          @method('DELETE')
          @csrf
          <button class="redBtn" type="submit">削除する</button>
        </form>
      </li>
    </ul>
  </div>
</div>
<!-- //削除モーダル -->

<script src="{{ mix('/staff/js/agency_role-edit.js') }}"></script>
@endsection
