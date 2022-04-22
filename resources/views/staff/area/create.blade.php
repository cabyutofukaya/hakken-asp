@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">public</span>国・地域追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.area.index', $agencyAccount) }}">国・地方マスタ</a></li>
      <li><span>国・地域追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.area.store', $agencyAccount) }}">
    @csrf

    <div id="inputArea">
      <ul class="baseList">		
        <li class="wd40"><span class="inputLabel req">国・地域コード</span>
          <input type="text" name="code" value="{{ $defaultValue['code'] ?? null }}">
        </li>
        <li class="wd40">
          <div id="directionArea"
            jsVars='@json($jsVars)'
            defaultValue='@json($defaultValue)'
          ></div>
          {{-- <span class="inputLabel">方面</span>
          <div class="selectBox">
            <select name="v_direction_uuid">
              @foreach($formSelects['vDirections'] as $k => $v)
                <option value="{{ $k }}"@if($k == Arr::get($defaultValue, 'v_direction_uuid', '')) selected @endif>{{ $v }}</option>
              @endforeach
            </select>
          </div> --}}
        </li>
        <li class="wd100"><span class="inputLabel">国・地域名称</span>          
          <input type="text" name="name" value="{{ $defaultValue['name'] ?? null }}">
        </li>
        <li class="wd100"><span class="inputLabel">国・地域名称(英)</span>          
          <input type="text" name="name_en" value="{{ $defaultValue['name_en'] ?? null }}">
        </li>
      </ul>
    </div>

    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn doubleBan"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>
  </form>

</main>

<script src="{{ mix('/staff/js/area-create-edit.js') }}"></script>
@endsection
