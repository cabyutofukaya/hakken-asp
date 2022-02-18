@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">notifications</span>新規通知追加</h1>
  </div>

  {!! Form::open(['route'=>'admin.web.system_news.store']) !!}

  @include("admin.common.error_message")
  @include("admin.common.success_message")
  
  <div id="inputArea">
    <ul class="baseList">
      <li class="wd30">
        <span class="inputLabel">登録日</span>
        <div class="calendar">
          <input type="text" name="regist_date" value="{{ old('regist_date') }}">
        </div>
      </li>
      <li>
        <span class="inputLabel">通知内容※表示されません</span>
        <input type="text" name="title" value="{{ old('title') }}">
      </li>
      <li>
        <span class="inputLabel">本文※HTMLタグも使えます</span>
        <textarea rows="5" name="content">{{ old('content') }}</textarea>
      </li>
    </ul>
  </div>
  <ul id="formControl">
    <li class="wd50">
      <button class="grayBtn" onclick="event.preventDefault();location.href='{{ route('admin.web.system_news.index') }}'" >
        <span class="material-icons">arrow_back_ios</span>登録せずに戻る
      </button>
    </li>
    <li class="wd50">
      <button class="blueBtn">
        <span class="material-icons">save</span> この内容で登録する
      </button>
    </li>
  </ul>
  {!! Form::close() !!}
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
@endsection
