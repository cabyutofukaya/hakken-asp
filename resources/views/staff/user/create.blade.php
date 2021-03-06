@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">person</span>個人顧客追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.client.person.index', $agencyAccount) }}">顧客管理</a></li>
      <li><span>個人顧客追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <h2 class="subTit"><span class="material-icons">person</span>基本情報</h2>

  <form method="post" action="{{ route('staff.client.person.store', $agencyAccount) }}">
    @csrf

    @include('staff.user.common._form',  ['editMode' => 'create'])

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
    <li class="wd50"><button class="blueBtn doubleBan" id="submit"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>

  </form>
</main>

@include('staff.user.common._js')
<script src="{{ mix('/staff/js/user-create.js') }}"></script>
@endsection