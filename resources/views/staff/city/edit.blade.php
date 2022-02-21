@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">location_on</span>都市・空港追加</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.master.city.index', $agencyAccount) }}">都市・空港マスタ</a></li>
      <li><span>都市・空港追加</span></li>
    </ol>
    @can('delete', $city)
      <div class="deleteControl">
        <button class="redBtn js-modal-open" data-target="mdDelete">削除</button>
      </div>
    @endcan
  </div>

  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.master.city.update', [$agencyAccount, $city]) }}">
    @csrf
    @method('PUT')

    <div id="inputArea">
      <ul class="baseList">		
        <li class="wd40"><span class="inputLabel">都市・空港コード</span>
          <input type="text" name="code" value="{{ $defaultValue['code'] ?? null }}" disabled>
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
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();history.back()">
          <span class="material-icons">arrow_back_ios</span>更新せずに戻る
        </button>
      </li>
      @can('update', $city)
        <li class="wd50">
          <button class="blueBtn doubleBan">
            <span class="material-icons">save</span> この内容で更新する
          </button>
        </li>
      @endcan
    </ul>
  </form>

<script src="{{ mix('/staff/js/city-create-edit.js') }}"></script>
</main>

@include('staff.common.modal_delete', [
  'title' => 'この項目を削除しますか？',
  'actionUrl' => route('staff.master.city.destroy', [
    'agencyAccount' => $agencyAccount,
    'city' => $city, 
  ])
])
@endsection
