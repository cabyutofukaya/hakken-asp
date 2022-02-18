@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">location_on</span>都市・空港追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.city.index', $agencyAccount) }}">都市・空港マスタ</a></li>
      <li><span>都市・空港追加</span></li>
    </ol>
  </div>
  
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.city.store', $agencyAccount) }}">
    @csrf

    <div id="inputArea">
      <ul class="baseList">		
        <li class="wd40"><span class="inputLabel">都市・空港コード</span>
          <input type="text" name="code" value="{{ $defaultValue['code'] ?? null }}">
        </li>
        <li class="wd40">
          <div id="areaArea"
            jsVars='@json($jsVars)'
            defaultValue='@json($defaultValue)'
            formSelects='@json($formSelects)'
          ></div>
          {{-- <span class="inputLabel">国・地域</span>
          <div class="selectBox">
            <select name="v_area_uuid">
              @foreach($formSelects['vAreas'] as $k => $v)
                <option value="{{ $k }}"@if($k == Arr::get($defaultValue, 'v_area_uuid', '')) selected @endif>{{ $v }}</option>
              @endforeach
            </select>
          </div> --}}
        </li>
        <li class="wd100"><span class="inputLabel">都市・空港名称</span>
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

<script src="{{ mix('/staff/js/city-create-edit.js') }}"></script>
@endsection
