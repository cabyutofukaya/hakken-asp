@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">business</span>法人顧客追加
    </h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.client.business.index', $agencyAccount) }}">顧客管理</a></li>
      <li><span>法人顧客追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <h2 class="subTit"><span class="material-icons">business</span>基本情報</h2>

  <form method="post" action="{{ route('staff.client.business.store', $agencyAccount) }}">
    @csrf

    @include('staff.business_user.common._form',  ['editMode' => 'create'])
      
    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button>
      </li>
      <li class="wd50">
        <button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button>
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
<script src="{{ mix('/staff/js/business_user-create.js') }}"></script>
@endsection
