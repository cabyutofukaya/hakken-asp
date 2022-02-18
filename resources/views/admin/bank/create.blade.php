@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">storage</span>銀行マスタ管理</h1>
  </div>

  @include("admin.common.success_message")
  @include("admin.common.error_message")

  <div id="inputArea">
  <h2 class="bankTit">銀行マスタのCSVをアップロードしてください。</h2>
  <ul>
  <li>CSVファイルの文字コードはShift-JISにしてください。</li>
  <li>アップロードすると既存の銀行リストは全て初期化されます。</li></ul>

  <form method="POST" action="{{ route('admin.banks.import.store') }}" enctype="multipart/form-data">
    @csrf
    <ul class="baseList">
      @for($i=0; $i<config('consts.const.BANK_CSV_UPLOAD_FIELDS'); $i++)
        <li><span class="inputLabel">{{ sprintf("CSV%d", $i + 1) }}</span>
          <input type="file" name="csv[{{$i}}]">
        </li>
      @endfor
    </ul>
  </div>
  <ul id="formControl">
    <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('admin.home.index') }}'"><span class="material-icons">arrow_back_ios</span>更新せずに戻る</button></li>
  <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> アップロードする</button></li>
  </ul>
</form>
</main>
@endsection
