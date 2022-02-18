@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">explore</span>方面マスタ管理</h1>
  </div>

  @include("admin.common.success_message")
  @include("admin.common.error_message")
  
  <div id="inputArea">
    <h2 class="bankTit">方面マスタのCSVをアップロードしてください。</h2>
    <ul>
      <li>CSVファイルの文字コードはShift-JISにしてください。</li>
      <li>一行に「コード,方面名称」という形式</li>
    </ul>

    <form method="POST" action="{{ route('admin.areas.master_directions.import.store') }}" enctype="multipart/form-data">
      @csrf
      <ul class="baseList">
        @for($i=0; $i<config('consts.const.DIRECTION_CSV_UPLOAD_FIELDS'); $i++)
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
