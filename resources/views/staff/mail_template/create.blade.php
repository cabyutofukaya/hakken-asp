@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">mark_email_read</span>メール定型文 設定</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.system.mail.index', $agencyAccount) }}">メール定型文設定</a></li>
      <li><span>メール定型文テンプレート追加</span></li>
    </ol>
  </div>

  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.system.mail.store', $agencyAccount) }}">
    @csrf

    @include('staff.mail_template.common._form')
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

<script src="{{ mix('/staff/js/mail_template-create.js') }}"></script>
@endsection
