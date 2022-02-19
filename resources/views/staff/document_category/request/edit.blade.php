@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">description</span>請求書 設定</h1>

    @can('delete', $documentRequest)
      <div class="deleteControl">
        <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
      </div>
    @endcan

    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')]) }}">帳票設定</a></li>
      <li><span>請求書 設定</span></li>
    </ol>
  </div>

  @include('staff.common.error_message')

  <form method="POST" action="{{ route('staff.system.document.request.update',[ $agencyAccount, $documentRequest->getRouteKey()]) }}">
    @csrf
    @method("PUT")

    @include("staff.document_category.request.common._form")
  
    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')]) }}'"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button>
      </li>
      @can('update', $documentRequest)
        <li class="wd50"><button class="blueBtn doubleBan"><span class="material-icons">save</span>この内容で更新する</button>
        </li>
      @endcan
    </ul>
  
  </form>
  
</main>

@include('staff.common.modal_delete', [
  'actionUrl' => route('staff.system.document.request.destroy', [
    'agencyAccount' => $agencyAccount,
    'documentRequest' => $documentRequest->getRouteKey(), 
    'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')
  ])
])

<script src="{{ mix('/staff/js/document_request-edit.js') }}"></script>
@endsection
