@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">description</span>領収書 設定</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')]) }}">帳票設定</a></li>
      <li><span>領収書 設定</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <form method="POST" action="{{ route('staff.system.document.receipt.store',$agencyAccount) }}">
    @csrf
    
    @include('staff.document_category.receipt.common._form')

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')]) }}'"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
    <li class="wd50"><button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>
  </form>
</main>
@endsection
