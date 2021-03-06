@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">move_to_inbox</span>仕入れ先追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.supplier.index', $agencyAccount) }}">仕入れ先マスタ</a></li>
      <li><span>仕入れ先追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.supplier.store', $agencyAccount) }}">
    @csrf

    @include("staff.supplier.common.form", ['editMode' => 'create'])

    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button>
      </li>
      <li class="wd50">
        <button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で登録する</button>
      </li>
    </ul>
  </form>
</main>

<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> 
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script> 
<script>
    flatpickr.localize(flatpickr.l10ns.ja);
    flatpickr('.calendar input', {
        allowInput: true,
		dateFormat: "Y/m/d"
    });
</script>
@include('staff.common._codeinput_js')
<script src="{{ mix('/staff/js/supplier-create.js') }}"></script>
@endsection
