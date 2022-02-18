@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">location_on</span>都市・空港マスタ</h1>
    @can('create', App\Models\City::class)
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.master.city.create', $agencyAccount) }}'" class="addBtn"><span class="material-icons">location_on</span>新規追加</button>
      </div>
    @endcan
    <form method="GET" action="{{ route('staff.master.city.index', [$agencyAccount]) }}">
    <div id="searchBox">
      <div id="inputList">
        <ul class="sideList">
          <li class="wd30"><span class="inputLabel">都市・空港コード</span>
            <input type="text" name="code" value="{{ $searchParam['code'] ?? null }}">
          </li>
          <li class="wd40"><span class="inputLabel">都市・空港名称</span>
            <input type="text" name="name" value="{{ $searchParam['name'] ?? null }}">
          </li>
          <li class="wd30 mr00"><span class="inputLabel">国・地域</span>
            <div class="selectBox">
              <select name="v_area_uuid">
                @foreach($formSelects['vAreas'] as $k => $v)
                    <option value="{{ $k }}" @if($k == Arr::get($searchParam, 'v_area_uuid', '')) selected @endif>{{ $v }}</option>
                  @endforeach
              </select>
            </div>
          </li>
        </ul>
      </div>
      <div id="controlList">
        <ul>
          <li>
            <button class="orangeBtn icon-left"><span class="material-icons">search</span>検索</button>
          </li>
          <li>
            <button class="grayBtn slimBtn" type="reset">条件クリア</button>
          </li>
        </ul>
      </div>
    </div>
    </form>
  </div>
  
  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <div id="cityList" class="tableWrap dragTable" agencyAccount='{{$agencyAccount}}' searchParam='@json($searchParam)'></div>

</main>

<script src="{{ mix('/staff/js/city-index.js') }}"></script>
@endsection
