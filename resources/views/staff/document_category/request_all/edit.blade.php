@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">description</span>一括請求書 設定</h1>

    @can('delete', $documentRequestAll)
      <div class="deleteControl">
        <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
      </div>
    @endcan

    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')]) }}">帳票設定</a></li>
      <li><span>一括請求書 設定</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <form method="POST" action="{{ route('staff.system.document.request_all.update',[$agencyAccount, $documentRequestAll->getRouteKey()]) }}">
    @csrf
    @method("PUT")

    @include("staff.document_category.request_all.common._form")

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')]) }}'"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button></li>
      @can('update', $documentRequestAll)
        <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> この内容で更新する</button>
      </li>
      @endcan
    </ul>

  </form>

</main>

@include('staff.common.modal_delete', [
  'actionUrl' => route('staff.system.document.request_all.destroy', [
    'agencyAccount' => $agencyAccount,
    'documentRequestAll' => $documentRequestAll->getRouteKey(), 
    'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')
  ])
])

<script src="{{ mix('/staff/js/document_request_all-edit.js') }}"></script>
@endsection
