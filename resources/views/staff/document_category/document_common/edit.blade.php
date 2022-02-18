@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">description</span>共通設定</h1>

    @can('delete', $documentCommon)
      <div class="deleteControl">
        <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
      </div>
    @endcan
    
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')]) }}">帳票設定</a></li>
      <li><span>共通設定</span></li>
    </ol>
  </div>

  @include('staff.common.error_message')

  <form method="POST" action="{{ route('staff.system.document.common.update',[ $agencyAccount, $documentCommon->getRouteKey()]) }}">
    @csrf
    @method('PUT')
  
    @include('staff.document_category.document_common.common._form')

    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')]) }}'"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button>
      </li>
      @can('update', $documentCommon)
      <li class="wd50">
        <button class="blueBtn"><span class="material-icons">save</span> この内容で更新する</button>
      </li>
      @endcan
    </ul>
  </form>
</main>

<div id="mdDelete" class="modal js-modal">
  <div class="modal__bg js-modal-close"></div>
  <div class="modal__content">
    <p class="mdTit mb20">この設定を削除しますか？</p>
      <ul class="sideList">
        <li class="wd50"><button class="grayBtn js-modal-close">キャンセル</button></li>
        <li class="wd50 mr00">
          <form method="post" action="{{ route('staff.system.document.common.destroy', [$agencyAccount, $documentCommon->getRouteKey()]) }}">
            @method('DELETE')
            @csrf
            <button class="redBtn" type="submit">削除する</button>
          </form>
    </ul>
  </div>
</div>
@endsection
