@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">person</span>個人顧客追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.client.person.index', $agencyAccount) }}">顧客管理</a></li>
      <li><span>個人顧客編集</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <h2 class="subTit"><span class="material-icons">person</span>基本情報</h2>

  <form method="post" action="{{ route('staff.client.person.update', [$agencyAccount, $user->user_number]) }}">
    @csrf
    @method('PUT')

    <input type="hidden" name="updated_at" value="{{ $defaultValue['updated_at'] ?? null }}"/>
    @include('staff.user.common._form',  ['editMode' => 'edit'])

    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();history.back()">
          <span class="material-icons">arrow_back_ios</span>更新せずに戻る
        </button>
      </li>
      @can('update', $user)
        <li class="wd50">
          <button class="blueBtn doubleBan" id="submit">
            <span class="material-icons">save</span> この内容で更新する
          </button>
        </li>
      @endcan
  </ul>

  </form>
</main>

@include('staff.user.common._js')
<script src="{{ mix('/staff/js/user-edit.js') }}"></script>
@endsection
