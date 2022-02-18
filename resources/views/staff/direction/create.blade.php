@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">explore</span>方面追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.direction.index', $agencyAccount) }}">方面マスタ</a></li>
      <li><span>方面追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.direction.store', $agencyAccount) }}">
    @csrf
    
    <div id="inputArea">
      <ul class="baseList">		
        <li class="wd40"><span class="inputLabel">方面コード</span>
          <input type="text" name="code" value="{{ $defaultValue['code'] ?? null }}">
        </li>
        <li class="wd100"><span class="inputLabel">方面名称</span>          
          <input type="text" name="name" value="{{ $defaultValue['name'] ?? null }}">
        </li>
      </ul>
    </div>

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>
  </form>

</main>
@endsection
