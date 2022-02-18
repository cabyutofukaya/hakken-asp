@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">mark_email_read</span>メール定型文 設定</h1>
  </div>
  
  @include('staff.common.success_message')
  @include('staff.common.error_message')
  
  <div id="declineMessageArea"><!-- React用declineメッセージ出力エリア --></div>

  <div class="customList show pt10">
  <h2><span class="material-icons">mark_email_read</span>メール定型文 一覧
    @can('create', App\Models\MailTemplate::class)
      <a href="{{ route('staff.system.mail.create', $agencyAccount) }}"><span class="material-icons">add_circle</span>新規テンプレート追加</a>
    @endcan
  </h2>

  @can('viewAny', App\Models\MailTemplate::class)
    <div id="tableWrap" agencyAccount='{{$agencyAccount}}' ></div>
  @endcan
    
  </div>
</main>

<script src="{{ mix('/staff/js/mail_template-index.js') }}"></script>
@endsection
