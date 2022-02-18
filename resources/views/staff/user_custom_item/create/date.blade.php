@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">calendar_today</span>日時項目追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.custom.index', ['agencyAccount' => $agencyAccount, 'tab' => $defaultUserCustomCategoryCode]) }}">カスタム項目</a></li>
      <li><span>日時項目追加</span></li>
    </ol>
  </div>

  <form method="post" action="{{ route('staff.system.custom.date.store', [$agencyAccount]) }}">
    @csrf

    <div id="inputArea" defaultUserCustomCategoryId='{{$defaultUserCustomCategoryId}}' errors='@json($errors->toArray())' defaultValue='@json(session()->getOldInput())' formSelects='@json($formSelects)'></div>

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();location.href='{{route('staff.system.custom.index', ['agencyAccount' => $agencyAccount, 'tab' => $defaultUserCustomCategoryCode])}}'"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>
  </form>
</main>
<script src="{{ mix('/staff/js/user_custom_item-create_date.js') }}"></script>
@endsection
