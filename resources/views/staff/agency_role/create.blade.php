@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">manage_accounts</span>ユーザー権限追加
    </h1>
    <ol class="breadCrumbs">
      <li>
        <a href="{{ route('staff.system.role.index', $agencyAccount) }}">ユーザー権限</a>
      </li>
      <li>
        <span>ユーザー権限追加</span>
      </li>
    </ol>
  </div>

  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.system.role.store', $agencyAccount) }}">
    @csrf

    <div id="inputArea">
      @include('staff.agency_role.form.edit')
    </div>
    
    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button>
      </li>
      <li class="wd50">
        <button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button>
      </li>
    </ul>
  </form>
</main>

<script src="{{ mix('/staff/js/agency_role-create.js') }}"></script>
@endsection
